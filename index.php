<?php
include("header.php");

if(isset($engine->cleanGet['MYSQL']['ajax'])){
    $result = array();
    switch($engine->cleanPost['MYSQL']['action']){
        case 'updateUserProjects':
            $currentProjectsIDs   = array_keys(sessionGet('currentProject'));
            $submittedProjectsIDs = isset($engine->cleanPost['MYSQL']['selectedProjects'])
                ? $engine->cleanPost['MYSQL']['selectedProjects']
                : array();

            try{
                // Delete project IDs that disappeared
                $deletedIDs = array_diff($currentProjectsIDs,$submittedProjectsIDs);
                if(sizeof($deletedIDs)){
                    $deleteSQL = sprintf("DELETE FROM users_projects WHERE userID='%s' AND projectID IN (%s)",
                        mfcs::user('ID'),
                        implode(',', $deletedIDs));
                    $deleteSQLResult = $engine->openDB->query($deleteSQL);
                    if(!$deleteSQLResult['result']){
                        throw new Exception("MySQL Error - ".$deleteSQLResult['error']);
                    }
                }

                // Add project IDs that appeared
                $addedIDs = array_diff($submittedProjectsIDs,$currentProjectsIDs);
                if(sizeof($addedIDs)){
                    $keyPairs=array();
                    foreach($addedIDs as $addedID){
                        $keyPairs[] = sprintf("('%s','%s')", mfcs::user('ID'), $addedID);
                    }
                    $insertSQL = sprintf("INSERT INTO  users_projects (userID,projectID) VALUES %s", implode(',', $keyPairs));
                    $insertSQLResult = $engine->openDB->query($insertSQL);
                    if(!$insertSQLResult['result']){
                        throw new Exception("MySQL Error - ".$insertSQLResult['error']);
                    }
                }

                // If we get here either nothing happened, or everything worked (no errors happened)
                $result = array(
                    'success'    => TRUE,
                    'deletedIDs' => $deletedIDs,
                    'addedIDs'   => $addedIDs
                );

            }catch(Exception $e){
                $result = array(
                    'success'  => FALSE,
                    'errorMsg' => $e->getMessage()
                );
            }
            break;
    }
    header('Content-type: application/json');
    die(json_encode($result));
}

try {
	$sql       = sprintf("SELECT * FROM `projects`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
		errorHandle::errorMsg("Error getting Projects");
		throw new Exception('Error');
	}

	$projectList = "";
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		if (checkProjectPermissions($row['ID']) === TRUE) {
			$projectList .= sprintf('<li><a href="dataEntry/index.php?id=%s">%s</a></li>',
				$engine->openDB->escape($row['ID']),
				$engine->openDB->escape($row['projectName'])
				);
		}

	}

	localvars::add("projectList",$projectList);

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Project</h1>
	</header>

	{local var="results"}

	<ul>
		{local var="projectList"}
	</ul>


	<ul>
		<li>
			<a href="dataEntry/selectForm.php">Create new Object</a>
		</li>
		<li>
			<a href="">List Objects</a>
		</li>
		<li>
			<a href="">Metadata Forms</a>
		</li>
		<li>
			<a href="">Export</a>
		</li>
	</ul>

</section>


<?php
$engine->eTemplate("include","footer");
?>
