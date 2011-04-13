<?php
include("../newEngine.php");

recurseInsert("vars.php","php");


$output  = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";

$output .= '<project>';
$output .= '<name>'.$engine->localVars("projectName").'</name>';
$output .= '<forms>';

	$sql = sprintf("SELECT * FROM %s WHERE projectID='%s'",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['result']) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			
			$output .= '<form>';
				$output .= '<name>'.htmlSanitize($row['formName']).'</name>';
				$output .= '<label>'.htmlSanitize($row['label']).'</label>';
				$output .= '<type>'.htmlSanitize($row['formType']).'</type>';
				$output .= '<fields>';

					$sql = sprintf("SELECT * FROM %s WHERE formID='%s' ORDER BY position",
						$engine->openDB->escape($engine->dbTables("formFields")),
						$engine->openDB->escape($row['ID'])
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult2               = $engine->openDB->query($sql);
					
					if ($sqlResult2['result']) {
						while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
							
							$output .= '<field>';
							$output .= '<name>'.$row2['fieldName'].'</name>';
							$output .= '<type>'.$row2['type'].'</type>';
							$output .= '<position>'.$row2['position'].'</position>';
							$output .= '</field>';

						}
					}
					

				$output .= '</fields>';
				$output .= '<records>';

					//Switch to project database
					$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

					$sql = sprintf("SELECT * FROM %s",
						$engine->openDB->escape($engine->dbTables($row['formName']))
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult2               = $engine->openDB->query($sql);
					
					if ($sqlResult2['result']) {
						while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
							$output .= '<record>';
							foreach ($row2 as $key => $value) {
								$output .= '<'.$key.'>'.$value.'</'.$key.'>';
							}
							$output .= '</record>';
						}
					}
					

					// Switch to system database
					$engine->openDB->select_db($engine->localVars("dbName"));

				$output .= '</records>';
			$output .= '</form>';

		}
	}
	

$output .= '</forms>';
$output .= '</project>';



header("Expires: 0");
header("Cache-Control: private");
header("Pragma: cache");
header("Content-Length: ".strlen($output));
header("Content-type: application/force-download");
header("Content-Transfer-Encoding: binary");
header('Content-Disposition: attachment; filename="export_'.time().'.xml"');

print $output;
?>
