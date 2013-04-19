<?php
include("../header.php");

function generateFieldDisplay($field,$data,$side,$revisionID=NULL){
	$engine = mfcs::$engine;
	$output='';
	switch($field['type']){
		case 'idno':
			/*
			$output .= sprintf('<li><div>%s</div>%s</li>',
				$field['label'],
				$object['data'][ $field['name'] ]
			);
			*/
			break;

		case 'text':
			$output .= sprintf('<li><div>%s</div>%s</li>',
				$field['label'],
				$data[ $field['name'] ]
			);
			break;

		case 'file':
			$output .= sprintf('<li><div>%s</div>', $field['label']);

			$fileViewerBaseParams = array(
				'objectID' => $engine->cleanGet['MYSQL']['objectID'],
				'field' => $field['name']
			);
			if(!is_null($revisionID)) $fileViewerBaseParams['revisionID'] = $revisionID;

			if(str2bool($field['multipleFiles'])){
				$files = $data[ $field['name'] ];
				for($i=1;$i<=sizeof($files); $i++){
					$fileViewerBaseParams['fileNum'] = $i;
					$output .= sprintf('<iframe class="%sFileViewer" src="fileViewer.php?%s#%sFileViewer" data-field_name="%s" onload="scrollSync(this)" seamless></iframe>',
						trim(strtolower($side)),
						http_build_query($fileViewerBaseParams),
						trim(strtolower($side)),
						$field['name']
					);
				}
			}else{
				$output .= '<div class="filePreview"><a href="#">Click to view current file</a>';
				$output .= sprintf('<iframe class="%sFileViewer" src="fileViewer.php?%s#%sFileViewer" data-field_name="%s" onload="scrollSync(this)" seamless></iframe>',
					trim(strtolower($side)),
					http_build_query($fileViewerBaseParams),
					trim(strtolower($side)),
					$field['name']
				);
			}

			$output .= '</li>';
			break;
	}
	return $output;
}

try {
	// Make sure we have id, formID, and objectID provided
	if(!isset($engine->cleanGet['MYSQL']['objectID']) || is_empty($engine->cleanGet['MYSQL']['objectID']) || !validate::integer($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID Provided.');
	localvars::add("objectID", $engine->cleanGet['MYSQL']['objectID']);

	$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
	$form   = forms::get($object['formID']);
	localvars::add("formName",$form['title']);

	if (mfcsPerms::isEditor($form['ID']) === FALSE) {
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	// Setup revision control
	$revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');

	// Get the current object
	$object = objects::get($engine->cleanGet['MYSQL']['objectID']);

	// Catch a form submition (which would be a revision being reverted to)
	if(isset($engine->cleanPost['MYSQL']['revisionID'])){
		$return = $revisions->insertRevision($engine->cleanGet['MYSQL']['objectID']);
		if($return === TRUE){
			$revision = $revisions->getRevision($engine->cleanGet['MYSQL']['objectID'], $engine->cleanPost['MYSQL']['revisionID']);
			// insert new version
			$sql = sprintf("UPDATE `objects` SET `parentID`='%s', `formID`='%s', `defaultProject`='%s', `data`='%s', `metadata`='%s', `modifiedTime`='%s' WHERE `ID`='%s'",
				$engine->openDB->escape($revision['parentID']),
				$engine->openDB->escape($revision['formID']),
				$engine->openDB->escape($revision['defaultProject']),
				$engine->openDB->escape($revision['data']),
				$engine->openDB->escape($revision['metadata']),
				time(),
				$engine->openDB->escape($engine->cleanGet['MYSQL']['objectID'])
			);
			$sqlResult = $engine->openDB->query($sql);
			if($sqlResult['result'] === FALSE){
				errorHandle::newError("SQL Error: ".$sqlResult['error'], errorHandle::HIGH);
			}else{
				// Reload the object - To refresh the data
				$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
			}
		}
	}

	// Is this just a revision AJAX request?
	if((isset($engine->cleanGet['MYSQL']['revisionID']))){
		$revision = $revisions->getRevision($engine->cleanGet['MYSQL']['objectID'], $engine->cleanGet['MYSQL']['revisionID']);
		$revision['data'] = decodeFields($revision['data']);
		echo '<ul class="objectFields">';
		foreach($form['fields'] as $field){
			echo generateFieldDisplay($field,$revision['data'],'right',$engine->cleanGet['MYSQL']['revisionID']);
		}
		echo '</ul>';
		exit();
	}

	// Build current version
	$output='';
	foreach($form['fields'] as $field){
		$output .= generateFieldDisplay($field,$object['data'],'left');
	}
	localvars::add("currentVersion", $output);

}
catch(Exception $e) {
	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<form id="revisionForm" action="" method="post">
	{engine name="csrf"}
	<input type="hidden" name="revisionID" id="revisionID" value="">
</form>


<header class="page-header">
	<h1>{local var="formName"}</h1>
</header>

<div id="left">
	{local var="leftnav"}
</div>

<div id="objectComparator">
	<section class="revisionSection" id="current"">
	<header>Current Version:</header>
	<ul class="objectFields">{local var="currentVersion"}</ul>
	</section>
	<section class="revisionSection" id="revisions">
		<header>
			Past Version:
			<div>
				<select id="revisionSelector">
					<option>Select a revision</option>
					<?php
					foreach($revisions->getSecondaryIDs($engine->cleanGet['MYSQL']['objectID'], 'DESC') as $revisionID){
						echo sprintf('<option value="%s">%s</option>', $revisionID, date('D, M d, Y - h:i a', $revisionID));
					}
					?>
				</select>
				<input id="revertBtn" type="button" value="Revert">
			</div>
		</header>
		<div id="revisionViewer"></div>
	</section>
</div>


<style>
	.revisionSection{
		width: 45%;
		margin: 5px;
		display: inline-block;
		vertical-align: top;
	}
	.revisionSection header{
		font-size: 20px;
		font-weight: bold;
		border-bottom: 1px solid #000;
		padding: 5px;
	}
	.revisionSection header div{
		float: right;
		font-weight: normal;
		font-size: 15px;
	}
	.revisionSection header div select{
		padding: 0;
		margin: 0;
		height: inherit;
	}

	.objectFields{
		list-style: none;
		margin: 0;
	}
	.objectFields li{
		margin-bottom: 5px;
	}
	.objectFields li div{
		font-weight: bold;
		font-size: 18px;
	}

	.leftFileViewer, .rightFileViewer{
		width: 95%;
		height: 500px;
	}
</style>
<script>
	function scrollSync(iFrameObj){
		$($(iFrameObj).contents()).scroll(function(){
			if($('#revisionSelector').val()){
				var thisObj     = $(this);
				var url         = thisObj[0].URL;
				var fieldName   = url.match(/field=\w+/i)[0].split('=')[1];
				var iFrameClass = url.match(/#\w+/i)[0].substr(1);
				var targetClass = iFrameClass=='rightFileViewer' ? 'leftFileViewer' : 'rightFileViewer';
				var scrollTop   = thisObj.scrollTop();
				var scrollLeft  = thisObj.scrollLeft();
				$('.'+targetClass+'[data-field_name="'+fieldName+'"]').contents().scrollTop(scrollTop).scrollLeft(scrollLeft);
			}
		});
		// onLoad, trigger sync from the left fileViewer
		$('.leftFileViewer').contents().scroll();
	}

	$(function(){
		$('#revisionSelector').change(function(){
			var url = '?objectID={local var="objectID"}&revisionID='+$(this).val();
			$('#revisionViewer').load(url);
		});
		$('#revertBtn').click(function(){
			if(confirm('Are you sure you want to revert back to this version?')){
				$('#revisionID').val( $('#revisionSelector').val() );
				$('#revisionForm').submit();
				$('#revisions :input').attr('disabled','disabled');
			}else{
				alert('Revert canceled');
			}
		});
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>
