<?php
include("../header.php");

try {
    // Make sure we have id, formID, and objectID provided
    if(!isset($engine->cleanGet['MYSQL']['id'])       || is_empty($engine->cleanGet['MYSQL']['id'])       || !validate::integer($engine->cleanGet['MYSQL']['id']))       throw new Exception('No Project ID Provided.');
    if(!isset($engine->cleanGet['MYSQL']['formID'])   || is_empty($engine->cleanGet['MYSQL']['formID'])   || !validate::integer($engine->cleanGet['MYSQL']['formID']))   throw new Exception('No Form ID Provided.');
    if(!isset($engine->cleanGet['MYSQL']['objectID']) || is_empty($engine->cleanGet['MYSQL']['objectID']) || !validate::integer($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID Provided.');

    // check for edit permissions on the project
    if(checkProjectPermissions($engine->cleanGet['MYSQL']['id']) === FALSE) throw new Exception('Permissions denied for working on this project');

    // check that this form is part of the project
    if(!checkFormInProject($engine->cleanGet['MYSQL']['id'],$engine->cleanGet['MYSQL']['formID'])) throw new Exception('Form is not part of project.');

    // Make sure the objectID is from this form
    if(isset($engine->cleanGet['MYSQL']['objectID']) && !checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) throw new Exception('Object not from this form');

    // Setup revision control
    $revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');

    // Get the project
    $project = getProject($engine->cleanGet['MYSQL']['id']);
    if($project === FALSE) throw new Exception('Error retrieving project.');
    localvars::add("projectName",$project['projectName']);

    // Get the current object
    $object = getObject($engine->cleanGet['MYSQL']['objectID']);
    $object['data'] = decodeFields($object['data']);

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
                $object = getObject($engine->cleanGet['MYSQL']['objectID']);
                $object['data'] = decodeFields($object['data']);
            }
        }
    }

    // Setup some local vars
    localvars::add("id", $engine->cleanGet['MYSQL']['id']);
    localvars::add("formID", $engine->cleanGet['MYSQL']['formID']);
    localvars::add("objectID", $engine->cleanGet['MYSQL']['objectID']);
    localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['id']));

    // Is this just a revision AJAX request?
    if((isset($engine->cleanGet['MYSQL']['revisionTable']))){
        echo $revisions->generateRevisionTable($engine->cleanGet['MYSQL']['objectID'], array(
            array('field' => 'parentID', 'label' => 'Parent ID'),
            array('field' => 'formID', 'label' => 'Form ID'),
            array('field' => 'modifiedTime', 'label' => 'Last Updated'),
        ));
    }

    if((isset($engine->cleanGet['MYSQL']['revisionID']))){
        $revision = $revisions->getRevision($engine->cleanGet['MYSQL']['objectID'], $engine->cleanGet['MYSQL']['revisionID']);
        $revision['data'] = decodeFields($revision['data']);
        echo '<ul class="objectFields">';
        foreach($revision['data'] as $name => $value){
            echo !is_array($value)
                ? sprintf('<li><div>%s</div>%s<br>%s</li>', $name, $value, $revisions->manualDiff($object['data'][$name], $revision['data'][$name]))
                : sprintf('<li><div>%s</div><iframe class="rightFileViewer" src="fileViewer.php?objectID=%s&field=%s&revisionID=%s#rightFileViewer" data-field_name="%s" onload="scrollSync(this)" seamless></iframe></li>', $name, $engine->cleanGet['MYSQL']['objectID'], $name, $engine->cleanGet['MYSQL']['revisionID'], $name);
        }
        echo '</ul>';
        exit();
    }
}
catch(Exception $e) {
    errorHandle::newError(__METHOD__."() - ".$e->getMessage(), errorHandle::DEBUG);
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
    <h1>{local var="projectName"}</h1>
</header>

<div id="left">
    {local var="leftnav"}
</div>

<div id="objectComparator">
    <section class="revisionSection" id="current"">
    <header>Current Version:</header>
    <ul class="objectFields">
        <?php
        foreach($object['data'] as $name => $value){
            echo !is_array($value)
                ? sprintf('<li><div>%s</div>%s</li>', $name, $value)
                : sprintf('<li><div>%s</div><iframe class="leftFileViewer" src="fileViewer.php?objectID=%s&field=%s#leftFileViewer" data-field_name="%s" onload="scrollSync(this)" seamless></iframe></li>', $name, $engine->cleanGet['MYSQL']['objectID'], $name, $name);
        }
        ?>
    </ul>
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
           var url = '?id={local var="id"}&formID={local var="formID"}&objectID={local var="objectID"}&revisionID='+$(this).val();
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
