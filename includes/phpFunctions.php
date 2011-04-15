<?php
function displayProjectInfo() {
	global $engine;

	$out  = '<span id="projectNameLabel">Project Name:</span> <span id="projectName">'.$engine->localVars("projectName").'</span>';
	$out .= '<br />';
	$out .= '<span id="formNameLabel">Form Name:</span> <span id="formName">'.$engine->localVars("formName").'</span>';

	return $out;
}

function editFormItem($id=NULL,$type=NULL,$fieldID=NULL) {

	global $engine;

	$return  = '<span class="itemOptions">';
	$return .= '<img class="trashIcon" src="'.$engine->localVars("siteRoot").'images/trash.png" />';
	$return .= ' <span class="editLink">Edit</span> ';
	$return .= '<img class="dragHandle" src="'.$engine->localVars("siteRoot").'images/hand.png" />';
	$return .= '</span>';

	$return .= '<span class="item">';
	$return .= showField($id,$type,$fieldID);
	$return .= '</span>';

	$return .= '<div class="addlInfoForm" style="display:'.(isnull($fieldID)?'block':'none').'">';
		$return .= '<span class="ui-icon ui-icon-circle-close"></span>';

		$tObj = new tableObject($engine,"array");
		$tObj->rowStriping = FALSE;
		$tObj->summary     = "";

		$data   = array();
		$showFields = fieldList($type);
		foreach ($showFields as $key => $val) {
			foreach ($val as $k => $v) {
				if ($key == 'hidden') {
					$return .= fieldProperties($id,$type,$k,$v,$fieldID,TRUE);
					continue;
				}

				$data[] = fieldProperties($id,$type,$k,$v,$fieldID);
			}
		}

		$return .= $tObj->display($data);
		$return .= '<input type="button" name="closeAddlInfoForm" value="Done" />';
	$return .= '</div>';

	return $return;

}

function leftPad($str,$length,$padChars='0',$padDir=STR_PAD_LEFT) {
	
	return str_pad($str,$length,$padChars,$padDir);
	
}

// returns an array of projectIDs that the user can access
function allowedProjects() {

	global $engine;

	$grps       = array();
	$projectIDs = array();

	foreach (sessionGet("groups") as $key => $value) {
		$grps[] = "name='".$value."'";
	}

	if (!is_empty($grps)) {
		$groupStr = "OR (type='group' AND (".implode(" OR ",$grps)."))";
	}

	$sql = sprintf("SELECT * FROM %s WHERE (type='user' AND name='%s') %s",
		$engine->openDB->escape($engine->dbTables("permissions")),
		$engine->openDB->escape(sessionGet("username")),
		$groupStr
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			
			$projectIDs[] = $row['projectID'];

		}
	}

	return $projectIDs;

}

?>
