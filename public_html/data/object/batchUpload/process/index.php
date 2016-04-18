<?php
// header
include("../../../../header.php");
$engine->eTemplate("include","header");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

// Batch Uploading Logs
log::insert("BatchUpload",0,0, "Processing upload");

print "<pre>";
var_dump($engine->cleanPost);
print "</pre>";

?>


<?php
$engine->eTemplate("include","footer");
?>
