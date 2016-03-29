<?php
// header
include("../header.php");
$engine->eTemplate("include","header");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

?>

<section>

    <header class="page-header">
        <h1> Move Files </h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}/moveObjects/"> Move Objects </a></li>

        <li class="pull-right noDivider">
            <a href="#batchUploadDocumentation" target="_blank">
                <i class="fa fa-book"></i> Documentation
            </a>
        </li>
    </ul>

    <div class="row-fluid">

    </div>
</section>

<?php
$engine->eTemplate("include","footer");
?>
