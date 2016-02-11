<?php
    localvars::add('siteRoot', "/");
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
            <li><a href="{local var="siteRoot"}dataView/search.php"><i class="fa fa-angle-double-right"></i> Search</a></li>
            <li><a href="{local var="siteRoot"}exporting/"><i class="fa fa-angle-double-right"></i> Exporting</a></li>
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
            <li><a href="{local var="siteRoot"}admin/metadataSchema/"> <i class="fa fa-angle-double-right"></i> Metadata Schemas </a></li>
            <li><a href="{local var="siteRoot"}admin/obsoleteFileTypes/"> <i class="fa fa-angle-double-right"></i> Obsolete File Types </a></li>
            <li><a href="{local var="siteRoot"}admin/projects/"><i class="fa fa-angle-double-right"></i> Projects</a></li>
            <li><a href="{local var="siteRoot"}admin/users/"><i class="fa fa-angle-double-right"></i> Users</a></li>
            <li><a href="{local var="siteRoot"}admin/watermarks/"> <i class="fa fa-angle-double-right"></i> Watermarks</a></li>
            
        </div>

        <div>
            <li> <h3> System Information </h3> </li>
            <li><a href="{local var="siteRoot"}dashboard/"><i class="fa fa-angle-double-right"></i> Dashboard</a></li>
            <li><a href="{local var="siteRoot"}dashboard/permissions/"><i class="fa fa-angle-double-right"></i> Permissions Audit</a></li>
            <li><a href="{local var="siteRoot"}dashboard/fixity/"> <i class="fa fa-angle-double-right"></i> Fixity History</a></li>
            <li><a href="{local var="siteRoot"}dashboard/obsolete/"> <i class="fa fa-angle-double-right"></i> Obsolete Files</a></li>
            <li><a href="{local var="siteRoot"}dashboard/virus/"> <i class="fa fa-angle-double-right"></i> Virus History </a></li>
            <li><a href="{local var="siteRoot"}stats/"> <i class="fa fa-angle-double-right"></i> Statistics </a></li>
        </div>

        </div>

         <div>
            <li> <h3> Logout </h3> </li>
            <li><a href="https://mfcsdev.lib.wvu.edu/engineIncludes/logout.php?csrf=d648314d45bbce117f76bdcdb0810aa0"> <i class="fa fa-user-times"></i> Logout</a></li>
        </div>
    </ul>
</nav>



