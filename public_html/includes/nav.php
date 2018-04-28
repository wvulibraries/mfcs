<?php
    localvars::add('siteRoot', "/");
    localvars::add("csrf",$_SESSION['CSRF']);
?>

<nav class="main-nav">
    <h2> Menu </h2>
    <div class="close"> <a href="javascript:void(0);"> <span class="fa fa-times-circle"></span> </a> </div>
    <ul>
        <div>
            <li><a href="{local var="siteRoot"}"> <span class="fa fa-home"></span> Home</a></li>
        </div>

        <div>
            <li> <h3> Data Entry </h3> </li>
            <li><a href="{local var="siteRoot"}dataEntry/selectForm.php"><span class="fa fa-angle-double-right"></span> Create &amp; Edit</a></li>
            <li><a href="{local var="siteRoot"}data/search/"><span class="fa fa-angle-double-right"></span> Search</a></li>
            <li><a href="{local var="siteRoot"}exports/"><span class="fa fa-angle-double-right"></span> Exporting</a></li>
            <li><a href="{local var="siteRoot"}data/list/projects/"><span class="fa fa-angle-double-right"></span> Projects List</a></li>
            <li><a href="{local var="siteRoot"}stats/"> <span class="fa fa-angle-double-right"></span> Statistics </a></li>

            <li><a href="{local var="siteRoot"}data/object/batchUpload/"> <span class="fa fa-angle-double-right"></span> Batch Upload </a></li>
            <li><a href="{local var="siteRoot"}data/object/move/"> <span class="fa fa-angle-double-right"></span> Move Objects </a></li>
        </div>

        <div>
            <li> <h3> Form Management </h3> </li>
            <li><a href="{local var="siteRoot"}formCreator/"> <span class="fa fa-angle-double-right"></span> New Form</a></li>
            <li><a href="{local var="siteRoot"}formCreator/list.php"> <span class="fa fa-angle-double-right"></span> List Forms</a></li>
            <li><a href="{local var="siteRoot"}formCreator/copy.php"> <span class="fa fa-angle-double-right"></span> Copy Form</a></li>
        </div>

        <div>
            <li> <h3> Administration </h3> </li>
            <!-- <li><a href="/admin/fileReProcessing/">File Re-Processing</a></li> -->
<<<<<<< Updated upstream
            <li><a href="{local var="siteRoot"}admin/export_dates/"> <i class="fa fa-angle-double-right"></i> Export Dates </a></li>
            <li><a href="{local var="siteRoot"}admin/metadataSchema/"> <i class="fa fa-angle-double-right"></i> Metadata Schemas </a></li>
            <li><a href="{local var="siteRoot"}admin/obsoleteFileTypes/"> <i class="fa fa-angle-double-right"></i> Obsolete File Types </a></li>
            <li><a href="{local var="siteRoot"}admin/projects/"><i class="fa fa-angle-double-right"></i> Projects</a></li>
            <li><a href="{local var="siteRoot"}admin/readonly/"><i class="fa fa-angle-double-right"></i> Read Only</a></li>
            <li><a href="{local var="siteRoot"}admin/reprocess/"><i class="fa fa-angle-double-right"></i> Reprocess</a></li>
            <li><a href="{local var="siteRoot"}admin/scheduler/"><i class="fa fa-angle-double-right"></i> Scheduler</a></li>          
            <li><a href="{local var="siteRoot"}admin/users/"><i class="fa fa-angle-double-right"></i> Users</a></li>
            <li><a href="{local var="siteRoot"}admin/watermarks/"> <i class="fa fa-angle-double-right"></i> Watermarks</a></li>
=======
            <li><a href="{local var="siteRoot"}admin/export_dates/"> <span class="fa fa-angle-double-right"></span> Export Dates </a></li>
            <li><a href="{local var="siteRoot"}admin/metadataSchema/"> <span class="fa fa-angle-double-right"></span> Metadata Schemas </a></li>
            <li><a href="{local var="siteRoot"}admin/obsoleteFileTypes/"> <span class="fa fa-angle-double-right"></span> Obsolete File Types </a></li>
            <li><a href="{local var="siteRoot"}admin/projects/"><span class="fa fa-angle-double-right"></span> Projects</a></li>
            <li><a href="{local var="siteRoot"}admin/readonly/"><span class="fa fa-angle-double-right"></span> Read Only</a></li>
            <li><a href="{local var="siteRoot"}admin/reprocess/"><span class="fa fa-angle-double-right"></span> Reprocess</a></li>
            <li><a href="{local var="siteRoot"}admin/scheduler/"><span class="fa fa-angle-double-right"></span> Scheduler</a></li>
            <li><a href="{local var="siteRoot"}admin/users/"><span class="fa fa-angle-double-right"></span> Users</a></li>
            <li><a href="{local var="siteRoot"}admin/watermarks/"> <span class="fa fa-angle-double-right"></span> Watermarks</a></li>
>>>>>>> Stashed changes

        </div>

        <div>
            <li> <h3> Dashboard </h3> </li>
            <li><a href="{local var="siteRoot"}dashboard/"><span class="fa fa-angle-double-right"></span> Dashboard</a></li>
            <li><a href="{local var="siteRoot"}dashboard/duplicates/"><span class="fa fa-angle-double-right"></span> Duplicates</a></li>
            <li><a href="{local var="siteRoot"}dashboard/permissions/"><span class="fa fa-angle-double-right"></span> Permissions Audit</a></li>
            <li><a href="{local var="siteRoot"}dashboard/fixity/"> <span class="fa fa-angle-double-right"></span> Fixity History</a></li>
            <li><a href="{local var="siteRoot"}dashboard/obsolete/"> <span class="fa fa-angle-double-right"></span> Obsolete Files</a></li>
            <li><a href="{local var="siteRoot"}dashboard/virus/"> <span class="fa fa-angle-double-right"></span> Virus History </a></li>

        </div>

        </div>

         <div>
            <li> <h3> Logout </h3> </li>
            <li><a href="{local var="siteRoot"}logout/?csrf={local var="csrf"}"> <span class="fa fa-user-times"></span> Logout</a></li>
        </div>
    </ul>
</nav>
