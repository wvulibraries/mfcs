<?php
include("../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
    header('Location: /index.php?permissionFalse');
}

log::insert("Admin: Export Management: View");

try {
    if (isset($engine->cleanPost['MYSQL']['delete_submit']) && is_array($engine->cleanPost['MYSQL']['delete'])) {
        foreach ($engine->cleanPost['MYSQL']['delete'] as $rowID) {

            if (!validate::integer($rowID)) continue;

            $sql       = sprintf("DELETE FROM `exports` WHERE `ID`='%s' LIMIT 1",$engine->openDB->escape($rowID));
            $sqlResult = $engine->openDB->query($sql);

            if (!$sqlResult['result']) {
                errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
                throw new Exception($sqlResult['error']);
            }

            log::insert("Admin: Export Management: Delete",0,0,$rowID);

        }
        errorHandle::successMsg("Deleted Exports");
    }
}
catch(Exception $e) {
    log::insert("Admin: Export Management: Error",0,0,$e->getMessage());
    errorHandle::errorMsg($e->getMessage());
}

$sql       = sprintf("SELECT * from `exports`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
    errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
    return FALSE;
}

$table_data = array();
while($row  = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

    $table_data[] = array(
        "delete"  => sprintf('<input type="checkbox" name="delete[]" value="%s" />',$row['ID']),
        "form"    => (isnull($row["formID"]))?"":forms::title($row["formID"]),
        "project" => (isnull($row["projectID"]))?"":projects::title($row["projectID"]),
        "date"    => date("Y-m-d h:ia",$row["date"])
        );

}

$table           = new tableObject("array");
$table->sortable = TRUE;
$table->summary  = "Exports Management listings";
$table->class    = "styledTable";
$table->headers(array("Delete","Form","Project","Date"));

localvars::add("exports_table",$table->display($table_data));
localvars::add("php_self",$_SERVER['PHP_SELF']);

localVars::add("results",displayMessages());
log::insert("Admin: View Projects Page");

$engine->eTemplate("include","header");
?>

<section>
    <header class="page-header">
        <h1>Manage Export Dates</h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}/admin/">Admin</a></li>
        <li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Export-Date-Management" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
    </ul>

    {local var="results"}

    <div class="metadataTables responsive-table">
        <section>
            <form action="{local var="php_self"}" method="post"> 
                {engine name="insertCSRF"}
                {local var="exports_table"}
                <input type="submit" name="delete_submit" value="Delete Export Dates" />
            </form>
        </section>
    </div>
</section>

<?php
$engine->eTemplate("include","footer");
?>
