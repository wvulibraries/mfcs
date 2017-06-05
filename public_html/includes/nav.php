<?php
    localvars::add('siteRoot', "/");
    localvars::add("csrf",$_SESSION['CSRF']);
?>

<nav class="main-nav">
    <h2> Menu </h2>
    <div class="close"> <a href="javascript:void(0);"> <i class="fa fa-times-circle"></i> </a> </div>
    <ul>
        <div>
            <li><a href="{local var="siteRoot"}"> <i class="fa fa-home"></i> Home</a></li>
        </div>

        <div>
            <li> <h3> Data Entry </h3> </li>
            <li><a href="{local var="siteRoot"}dataEntry/selectForm.php"><i class="fa fa-angle-double-right"></i> Create &amp; Edit</a></li>
            <li><a href="{local var="siteRoot"}data/search/"><i class="fa fa-angle-double-right"></i> Search</a></li>
            <li><a href="{local var="siteRoot"}exports/"><i class="fa fa-angle-double-right"></i> Exporting</a></li>
            <li><a href="{local var="siteRoot"}data/list/projects/"><i class="fa fa-angle-double-right"></i> Projects List</a></li>
            <li><a href="{local var="siteRoot"}stats/"> <i class="fa fa-angle-double-right"></i> Statistics </a></li>

            <li><a href="{local var="siteRoot"}data/object/batchUpload/"> <i class="fa fa-angle-double-right"></i> Batch Upload </a></li>
            <li><a href="{local var="siteRoot"}data/object/move/"> <i class="fa fa-angle-double-right"></i> Move Objects </a></li>
        </div>

        <div>
            <li> <h3> Form Management </h3> </li>
            <li><a href="{local var="siteRoot"}formCreator/"> <i class="fa fa-angle-double-right"></i> New Form</a></li>
            <li><a href="{local var="siteRoot"}formCreator/list.php"> <i class="fa fa-angle-double-right"></i> List Forms</a></li>
            <li><a href="{local var="siteRoot"}formCreator/copy.php"> <i class="fa fa-angle-double-right"></i> Copy Form</a></li>
        </div>

        <div>
            <li> <h3> Administration </h3> </li>
            <!-- <li><a href="/admin/fileReProcessing/">File Re-Processing</a></li> -->
            <li><a href="{local var="siteRoot"}admin/export_dates/"> <i class="fa fa-angle-double-right"></i> Export Dates </a></li>
            <li><a href="{local var="siteRoot"}admin/metadataSchema/"> <i class="fa fa-angle-double-right"></i> Metadata Schemas </a></li>
            <li><a href="{local var="siteRoot"}admin/obsoleteFileTypes/"> <i class="fa fa-angle-double-right"></i> Obsolete File Types </a></li>
            <li><a href="{local var="siteRoot"}admin/projects/"><i class="fa fa-angle-double-right"></i> Projects</a></li>
            <li><a href="{local var="siteRoot"}admin/readonly/"><i class="fa fa-angle-double-right"></i> Read Only</a></li>
            <li><a href="{local var="siteRoot"}admin/reprocess/"><i class="fa fa-angle-double-right"></i> Reprocess</a></li>
            <li><a href="{local var="siteRoot"}admin/scheduler/"><i class="fa fa-angle-double-right"></i> Scheduler</a></li>          
            <li><a href="{local var="siteRoot"}admin/users/"><i class="fa fa-angle-double-right"></i> Users</a></li>
            <li><a href="{local var="siteRoot"}admin/watermarks/"> <i class="fa fa-angle-double-right"></i> Watermarks</a></li>

        </div>

        <div>
            <li> <h3> Dashboard </h3> </li>
            <li><a href="{local var="siteRoot"}dashboard/"><i class="fa fa-angle-double-right"></i> Dashboard</a></li>
            <li><a href="{local var="siteRoot"}dashboard/duplicates/"><i class="fa fa-angle-double-right"></i> Duplicates</a></li>
            <li><a href="{local var="siteRoot"}dashboard/permissions/"><i class="fa fa-angle-double-right"></i> Permissions Audit</a></li>
            <li><a href="{local var="siteRoot"}dashboard/fixity/"> <i class="fa fa-angle-double-right"></i> Fixity History</a></li>
            <li><a href="{local var="siteRoot"}dashboard/obsolete/"> <i class="fa fa-angle-double-right"></i> Obsolete Files</a></li>
            <li><a href="{local var="siteRoot"}dashboard/virus/"> <i class="fa fa-angle-double-right"></i> Virus History </a></li>

        </div>

        </div>

         <div>
            <li> <h3> Logout </h3> </li>
            <li><a href="{local var="siteRoot"}logout/?csrf={local var="csrf"}"> <i class="fa fa-user-times"></i> Logout</a></li>
        </div>
    </ul>
</nav>
