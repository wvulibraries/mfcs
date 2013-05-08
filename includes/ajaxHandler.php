<?php

if(isset($engine->cleanGet['MYSQL']['ajax'])){
    $result = array();
    if (isset($engine->cleanPost['MYSQL']['action'])) {
        switch($engine->cleanPost['MYSQL']['action']){
            case 'updateUserProjects':
            // @TODO this case statement should be broken off into another file or class
            $currentProjectsIDs   = array_keys(sessionGet('currentProject'));
            $submittedProjectsIDs = isset($engine->cleanPost['MYSQL']['selectedProjects'])
            ? $engine->cleanPost['MYSQL']['selectedProjects']
            : array();

            try{
                // Delete project IDs that disappeared
                $deletedIDs = array_diff($currentProjectsIDs,$submittedProjectsIDs);
                if(sizeof($deletedIDs)){
                    $deleteSQL = sprintf("DELETE FROM users_projects WHERE userID='%s' AND projectID IN (%s)",
                        users::user('ID'),
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
                        $keyPairs[] = sprintf("('%s','%s')", users::user('ID'), $addedID);
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
    }
    else if (isset($engine->cleanGet['MYSQL']['action'])) {
        switch($engine->cleanGet['MYSQL']['action']){
            case 'selectChoices':
                $field        = forms::getField($engine->cleanGet["MYSQL"]['formID'],$engine->cleanGet["MYSQL"]['fieldName']);
                $fieldChoices = forms::getFieldChoices($field);
                $result       = forms::drawFieldChoices($field,$fieldChoices);
                die($result);
                break;
            case 'searchFormFields':
                die(mfcsSearch::formFieldOptions($engine->cleanGet["MYSQL"]['formID']));
                break;
        }
    }
    header('Content-type: application/json');
    die(json_encode($result));
}

?>