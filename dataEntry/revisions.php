<?php
include("../header.php");

// Setup revision control
$revisions = revisions::create();

###############################################################################################################

try{
	if(	!isset($engine->cleanGet['MYSQL']['objectID']) ||
		!validate::integer($engine->cleanGet['MYSQL']['objectID'])){
		throw new Exception('No Object ID Provided.');
	}

	$objectID = $engine->cleanGet['MYSQL']['objectID'];
	$object   = objects::get($objectID);
	$form     = forms::get($object['formID']);
	$fields   = $form['fields'];
	if(mfcsPerms::isEditor($form['ID']) === FALSE) throw new Exception("Permission Denied to view objects created with this form.");


	###############################################################################################################

	// Catch a form submition (which would be a revision being reverted to)
	if(isset($engine->cleanPost['MYSQL']['revisionID'])){
		if (($revision = $revisions->getRevision($engine->cleanGet['MYSQL']['objectID'], $engine->cleanPost['MYSQL']['revisionID'])) === FALSE) {
			throw new Exception('Could not load revision.');
		} 

		if (objects::update($engine->cleanGet['MYSQL']['objectID'],$revision['formID'],(decodeFields($revision['data'])),$revision['metadata'],$revision['parentID']) !== FALSE) {
			// Reload the object - To refresh the data
			$object = objects::get($objectID,TRUE); 
		}
		else {
			throw new Exception('Could not update object with revision.');
		}
	}

	###############################################################################################################

	// Is this just a revision AJAX request?
	if((isset($engine->cleanGet['MYSQL']['revisionID']))) {
		$revisionID = $revisions->getRevisionID($engine->cleanGet['MYSQL']['objectID'], $engine->cleanGet['MYSQL']['revisionID']);
		$revision   = $revisions->getMetadataForID($revisionID);

		if(!$revision){
			die('Error reading revision');
		}else{
			die(revisions::generateFieldDisplay($revision, $fields));
		}
	}

	###############################################################################################################

	localvars::add("formName", $form['title']);
	localvars::add("objectID", $objectID);
	localvars::add("currentVersion", revisions::generateFieldDisplay($object, $fields));

}catch(Exception $e){
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

<div class="row-fluid" id="results">
	{local var="results"}
</div>

<div id="objectComparator">
	<section class="revisionSection" id="current">
	<header>Current Version:</header>
	{local var="currentVersion"}
	</section>
	<section class="revisionSection" id="revisions">
		<header>
			Past Version:
			<div>
				<select id="revisionSelector">
					<option>Select a revision</option>
					<?php
					foreach($revisions->getSecondaryIDs($engine->cleanGet['MYSQL']['objectID'], 'DESC') as $revisionID){
						printf('<option value="%s">%s</option>', $revisionID, date('D, M d, Y - h:i a', $revisionID));
					}
					?>
				</select>
				<input id="revertBtn" type="button" value="Revert">
			</div>
		</header>
		<div id="revisionViewer"></div>
	</section>
</div>

<!-- @TODO : Style should be moved out of this file -->
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

	.objectField{
		margin: 5px 15px;
	}
	.objectField header{
		font-weight: bold;
		font-size: 14px;
		padding: 0;
		border-bottom: 1px solid #999;
		width: 50%;
	}
	.objectField ul{
		margin-left: 10px;
	}
	.objectField aside{
		border-top: 1px solid #ccc;
		width: 25%;
		margin-top: 10px;
		padding: 5px 0;
	}
</style>

<!-- @TODO : scripts should be moved out of this file -->
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
		$('#objectComparator').on('click','.toggleFileList',function(){
			$link = $(this);
			$ul   = $link.next('ul');
			if($ul.is(':visible')){
				$ul.slideUp('fast');
				$link.html('click to show list');
			}else{
				$ul.slideDown('fast');
				$link.html('click to hide list');
			}
		});
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>
