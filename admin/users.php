<?php
include("header.php");

$errorMsg = NULL;

function listFields() {
	global $engine;

	$listObj = new listManagement($engine,$engine->dbTables("users"));

	$options = array();
	$options['field']    = "ID";
	$options['label']    = "ID";
	$options['type']     = "hidden";
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = "type";
	$options['label']    = "Type";
	$options['type']     = "select";
	$options['dupes']    = TRUE;
	$options['options'][] = array("value"=>"user","label"=>"User");
	$options['options'][] = array("value"=>"group","label"=>"Group");
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = "name";
	$options['label']    = "Username/Group Name";
	$options['size']     = "20";
	$options['validate'] = "alphaNumeric";
	$options['dupes']    = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "projectID";
	$options['label']     = "Project Name";
	$options['dupes']     = TRUE;
	$options['type']      = "select";

	$sql = sprintf("SELECT * FROM %s",
		$engine->openDB->escape($engine->dbTables("projects"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['result']) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$options['options'][] = array("value" => $row['ID'], "label" => $row['name']);
		}
	}

	$listObj->addField($options);
	unset($options);

	return $listObj;
}


$listObj = listFields();

// Form Submission
if(isset($engine->cleanPost['MYSQL'][$engine->dbTables("users").'_submit'])) {
	
	$errorMsg .= $listObj->insert();

}
else if (isset($engine->cleanPost['MYSQL'][$engine->dbTables("users").'_update'])) {
	
	$errorMsg .= $listObj->update();
	
}
// Form Submission

$listObj = listFields();


print "<h2>Edit Users</h2>";

if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}

print "<h3>New User</h3>";
print $listObj->displayInsertForm();

print "<hr />";

print "<h3>Edit Users</h3>";
print $listObj->displayEditTable();


$engine->eTemplate("include","footer");
?>
