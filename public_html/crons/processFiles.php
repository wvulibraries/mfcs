<?php

session_save_path('/tmp');
ini_set('memory_limit', -1);
set_time_limit(0);

require("../header.php");

if (!isCLI()) {
    print "Must be run from the command line.";
    exit;
}

# Turn off EngineAPI template engine
$engine->obCallback = FALSE;

# Function to check if the required tables exist
function checkDatabaseSetup() {
    $requiredTables = ['users', 'objectProcessing']; // Add other required tables here
    $missingTables = [];

    foreach ($requiredTables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $result = mfcs::$engine->openDB->query($query);

        if (!$result['result'] || mysqli_num_rows($result['result']) === 0) {
            $missingTables[] = $table;
        }
    }

    return $missingTables;
}

# Check if the necessary tables exist before proceeding
$missingTables = checkDatabaseSetup();
if (!empty($missingTables)) {
    errorHandle::newError("Missing required tables: " . implode(", ", $missingTables), errorHandle::DEBUG);
    exit;
}

# Count how many items we need to iterate through.  
$sqlCount = "SELECT COUNT(*) AS `processing` FROM `objectProcessing` WHERE `state` = 1";
$countQuery = mfcs::$engine->openDB->query($sqlCount); 
if (!$countQuery || !$countQuery['result']) {
    errorHandle::newError("Failed to count processing items: " . $countQuery['error'], errorHandle::DEBUG);
    exit;
}

$result = mysqli_fetch_array($countQuery['result'], MYSQLI_ASSOC);
$count = (int) $result['processing'];

while ($count > 0) {
    # Grab one item at a time that is in a valid state 
    $sql = "SELECT * FROM `objectProcessing` WHERE `state` = 1 LIMIT 1";
    $sqlResult = mfcs::$engine->openDB->query($sql);

    # Break if there's nothing to process
    if (!$sqlResult['result']) {
        errorHandle::newError("Error during query: " . $sqlResult['error'], errorHandle::DEBUG);
        break;
    }

    if ((int) $sqlResult['numrows'] === 0) break;

    $row = mysqli_fetch_array($sqlResult['result'], MYSQLI_ASSOC);
    files::process($row['objectID'], $row['fieldName']);

    # Update the count of remaining items after processing
    $count--;
}

# Clean up old jobs
files::deleteOldProcessingJobs();
files::errorOldProcessingJobs();

?>