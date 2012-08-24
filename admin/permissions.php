<?php
include("header.php");

$errorMsg = NULL;

function listFields() {
	global $engine;

	$listObj = new listManagement($engine,$engine->dbTables("permissions"));

	$listObj->whereClause = "WHERE projectID = '".$engine->localVars("projectID")."'";

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
	$options['validate'] = "alphaNumericNoSpaces";
	$options['dupes']    = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "projectID";
	$options['label']     = "Project ID";
	$options['dupes']     = TRUE;
	$options['type']      = "hidden";
	$options['value']     = $engine->localVars("projectID");
	$listObj->addField($options);
	unset($options);

	return $listObj;
}


$listObj = listFields();

// Form Submission
if(isset($engine->cleanPost['MYSQL'][$engine->dbTables("permissions").'_submit'])) {
	
	$errorMsg .= $listObj->insert();

}
else if (isset($engine->cleanPost['MYSQL'][$engine->dbTables("permissions").'_update'])) {
	
	$errorMsg .= $listObj->update();
	
}
// Form Submission

$listObj = listFields();


$engine->eTemplate("include","header");
?>

<script type="text/javascript" src="{local var="siteRoot"}includes/permissions_functions.js"></script>

<h2>Edit Permissions</h2>

<?php
if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}
?>

<p><a href="#" id="commonSecurityGroupsToggle">Common Security Groups</a></p>

<ul id="commonSecurityGroupsList">
<li><a href="#">libraryDept_dlc_systems</a></li>
<li><a href="#">libraryDept_dlc_systemsStudents</a></li>
<li><a href="#">libraryGroup_Proxy</a></li>
<li><a href="#">libraryGroup_ProxyAlumni</a></li>
<li><a href="#">libraryGroup_staff</a></li>
<li><a href="#">libraryGroup_staff_dlc</a></li>
<li><a href="#">libraryGroup_staff_evl</a></li>
<li><a href="#">libraryGroup_staff_hsl</a></li>
<li><a href="#">libraryGroup_students</a></li>
<li><a href="#">libraryGroup_StudentsWithHomeDirs</a></li>
<li><a href="#">libraryWeb_engineCMSAdmin</a></li>
<li><a href="#">libraryWebWVC_IAI</a></li>
<li><a href="#">libraryWebWVC_PEC</a></li>
<li><a href="#">libraryWebWVC_PTP</a></li>
<li><a href="#">libraryWebWVC_Workshop</a></li>
<li><a href="#">libraryWebWVC_wvcguide</a></li>
<li><a href="#">libraryWebWVC_WVCP</a></li>
</ul>

<h3>New User</h3>
<?= $listObj->displayInsertForm(); ?>

<hr />

<h3>Edit Permissions</h3>
<?= $listObj->displayEditTable(); ?>

<script type="text/javascript">
	$(document).ready(init);
</script>

<?php
$engine->eTemplate("include","footer");
?>
