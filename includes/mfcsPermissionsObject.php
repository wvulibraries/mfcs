<?php
class mfcsPermissions extends permissionObject {
	
	private $engine    = NULL;
	private $origTable = NULL;
	private $ident     = NULL;
	private $table     = NULL;

	function __construct($origTable,$engine,$ident,$table) {
		
		$this->engine    = $engine;
		$this->origTable = $origTable;
		$this->ident     = $ident;
		$this->table     = $table;

		// Create and populate a temporary table to pass to permissionObject class
		$create = $this->createTempTable();
		if ($create !== TRUE) {
			return $create;
		}

		parent::__construct($table, $engine);

	}

	function __destruct() {
		
		$sql = sprintf("DROP TEMPORARY TABLE IF EXISTS %s",
			$this->engine->openDB->escape($this->engine->dbTables($this->table))
			);
		$sqlResult = $this->engine->openDB->query($sql);

		if ($sqlResult['result']) {
			return(TRUE);
		}

		return(FALSE);

	}

	private function createTempTable() {
		
		$sql = sprintf("CREATE TEMPORARY TABLE IF NOT EXISTS %s (ID int(10) unsigned NOT NULL auto_increment, name varchar(75) default NULL, value varchar(255) default NULL, PRIMARY KEY (ID)) ENGINE=MyISAM",
			$this->engine->openDB->escape($this->engine->dbTables($this->table))
			);
		$this->engine->openDB->sanitize = FALSE;
		$sqlResult                      = $this->engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			return webHelper_errorMsg("Error creating temporary table");
		}


		$sql = sprintf("INSERT INTO %s (name,value) SELECT name,value FROM %s WHERE ident='%s' ORDER BY value + 0",
			$this->engine->openDB->escape($this->engine->dbTables($this->table)),
			$this->engine->openDB->escape($this->engine->dbTables($this->origTable)),
			$this->engine->openDB->escape($this->ident)
			);
		$this->engine->openDB->sanitize = FALSE;
		$sqlResult                      = $this->engine->openDB->query($sql);
				
		if (!$sqlResult['result']) {
			return webHelper_errorMsg("Error populating tempory table");
		}
		
		return(TRUE);

	}

	public function insert($function) {

		$insert = parent::insert($function);
		if ($insert === FALSE) {
			return(FALSE);
		}

		$sql = sprintf("DELETE FROM %s WHERE ident='%s'",
			$this->engine->openDB->escape($this->engine->dbTables($this->origTable)),
			$this->engine->openDB->escape($this->ident)
			);
		$this->engine->openDB->sanitize = FALSE;
		$sqlResult                      = $this->engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			return(FALSE);
		}
		

		$sql = sprintf("INSERT INTO %s (ident,name,value) SELECT '%s',name,value FROM %s ORDER BY value + 0",
			$this->engine->openDB->escape($this->engine->dbTables($this->origTable)),
			$this->engine->openDB->escape($this->ident),
			$this->engine->openDB->escape($this->engine->dbTables($this->table))
			);
		$this->engine->openDB->sanitize = FALSE;
		$sqlResult                      = $this->engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			return(FALSE);
		}

		return(TRUE);		

	}

	public function buildFormChecklist($userPerms) {

		$sql = sprintf("SELECT perms.*, projs.ID AS projectID FROM %s AS perms LEFT JOIN %s AS projs ON perms.name=projs.name ORDER BY projs.name",
			$this->engine->openDB->escape($this->engine->dbTables($this->table)),
			$this->engine->openDB->escape($this->engine->dbTables("projects"))
			);
		$this->engine->openDB->sanitize = FALSE;
		$sqlResult                      = $this->engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			return webHelper_errorMsg("Error pulling permissions.");
		}
		
		$data = array();
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_BOTH)) {
			$row['name']  = htmlSanitize($row['name']);
			$row['value'] = htmlSanitize($row['value']);
			$ident = 'projects';

			if (!isset($userPerms[$ident])) {
				$userPerms[$ident]['view']   = "0";
				$userPerms[$ident]['modify'] = "0";
			}
var_dump($this->checkPermissions($userPerms[$ident]['view'],$row['value']));

			$tmp   = array();
			$tmp[] = '<input type="checkbox" name="viewPerms['.$ident.'][]" value="'.$row['value'].'" '.($this->checkPermissions($userPerms[$ident]['view'],$row['value'])?'checked ':'').'/>';
			$tmp[] = '<input type="checkbox" name="modifyPerms['.$ident.'][]" value="'.$row['value'].'" '.($this->checkPermissions($userPerms[$ident]['modify'],$row['value'])?'checked ':'').'/>';
			$tmp[] = $row['name'];

			$data[] = $tmp;
			unset($tmp);


			$sql = sprintf("SELECT perms.*, forms.ID AS formID FROM %s AS perms LEFT JOIN %s AS forms ON perms.name=forms.formName WHERE perms.ident='proj%sforms' ORDER BY forms.formName",
				$this->engine->openDB->escape($this->engine->dbTables($this->origTable)),
				$this->engine->openDB->escape($this->engine->dbTables("forms")),
				$this->engine->openDB->escape($row['projectID'])
				);
			$this->engine->openDB->sanitize = FALSE;
			$sqlResult2                     = $this->engine->openDB->query($sql);
			
			if ($sqlResult2['result']) {
				while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
					$row2['name']  = htmlSanitize($row2['name']);
					$row2['value'] = htmlSanitize($row2['value']);
					$ident = 'proj'.$row['projectID'].'forms';

					if (!isset($userPerms[$ident])) {
						$userPerms[$ident]['view']   = "0";
						$userPerms[$ident]['modify'] = "0";
					}
var_dump($this->checkPermissions($userPerms[$ident]['view'],$row2['value']));

					$tmp   = array();
					$tmp[] = '<input type="checkbox" name="viewPerms['.$ident.'][]" value="'.$row2['value'].'" '.($this->checkPermissions($userPerms[$ident]['view'],$row2['value'])?'checked ':'').'/>';
					$tmp[] = '<input type="checkbox" name="modifyPerms['.$ident.'][]" value="'.$row2['value'].'" '.($this->checkPermissions($userPerms[$ident]['modify'],$row2['value'])?'checked ':'').'/>';
					$tmp[] = '<img src="../images/subtree.gif" />&nbsp;'.$row2['name'];

					$data[] = $tmp;
					unset($tmp);


					$sql = sprintf("SELECT * FROM %s WHERE ident='proj%sform%selements' ORDER BY name",
						$this->engine->openDB->escape($this->engine->dbTables($this->origTable)),
						$this->engine->openDB->escape($row['projectID']),
						$this->engine->openDB->escape($row2['formID'])
						);
					$this->engine->openDB->sanitize = FALSE;
					$sqlResult3                     = $this->engine->openDB->query($sql);
					
					if ($sqlResult3['result']) {
						while ($row3 = mysql_fetch_array($sqlResult3['result'], MYSQL_ASSOC)) {
							$row3['name']  = htmlSanitize($row3['name']);
							$row3['value'] = htmlSanitize($row3['value']);
							$ident = 'proj'.$row['projectID'].'form'.$row2['formID'].'elements';

							if (!isset($userPerms[$ident])) {
								$userPerms[$ident]['view']   = "0";
								$userPerms[$ident]['modify'] = "0";
							}
var_dump($this->checkPermissions($userPerms[$ident]['view'],$row3['value']));

							$tmp   = array();
							$tmp[] = '<input type="checkbox" name="viewPerms['.$ident.'][]" value="'.$row3['value'].'" '.($this->checkPermissions($userPerms[$ident]['view'],$row3['value'])?'checked ':'').'/>';
							$tmp[] = '<input type="checkbox" name="modifyPerms['.$ident.'][]" value="'.$row3['value'].'" '.($this->checkPermissions($userPerms[$ident]['modify'],$row3['value'])?'checked ':'').'/>';
							$tmp[] = '&nbsp; &nbsp; <img src="../images/subtree.gif" />&nbsp;'.$row3['name'];

							$data[] = $tmp;
							unset($tmp);
						}
					}
					

				}
			}
			
		}
		
		$t = new tableObject($this->engine);
		$t->headers(array("View","Modify"));
		$t->summary = "";

		$output = $t->display($data);
		
		return($output);
	}

}
?>
