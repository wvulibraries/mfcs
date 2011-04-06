<?php
include("header.php");

$vals = array();
$ident = "projects";
$permissions = new mfcsPermissions("permissions",$engine,$ident,"tempPermissions");

if (isset($engine->cleanPost['MYSQL']['submitPermissions'])) {

	$security = array();
	
	if (isset($engine->cleanPost['MYSQL']['viewPerms'])) {
		foreach ($engine->cleanPost['MYSQL']['viewPerms'] as $ident => $values) {
			$security[$ident]['view'] = isset($security[$ident]['view'])?$security[$ident]['view']:"0";
			$security[$ident]['modify'] = isset($security[$ident]['modify'])?$security[$ident]['view']:"0";

			foreach ($values as $value) {
				$security[$ident]['view'] = bcadd($security[$ident]['view'],$value);
			}
		}
	}

	if (isset($engine->cleanPost['MYSQL']['modifyPerms'])) {
		foreach ($engine->cleanPost['MYSQL']['modifyPerms'] as $ident => $values) {
			$security[$ident]['view'] = isset($security[$ident]['view'])?$security[$ident]['view']:"0";
			$security[$ident]['modify'] = isset($security[$ident]['modify'])?$security[$ident]['view']:"0";
			
			foreach ($values as $value) {
				$security[$ident]['modify'] = bcadd($security[$ident]['modify'],$value);
			}
		}
	}

	foreach ($security as $ident => $V) {
		$sql = sprintf("DELETE FROM %s WHERE userID='%s' AND ident='%s'",
			$engine->openDB->escape($engine->dbTables("userPermissions")),
			$engine->cleanGet['MYSQL']['user'],
			$engine->openDB->escape($ident)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);

		$sql = sprintf("INSERT INTO %s (userID,ident,viewValue,modifyValue) VALUES ('%s','%s','%s','%s')",
			$engine->openDB->escape($engine->dbTables("userPermissions")),
			$engine->cleanGet['MYSQL']['user'],
			$engine->openDB->escape($ident),
			$engine->openDB->escape($V['view']),
			$engine->openDB->escape($V['modify'])
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
	}
	
}
?>

<form action="<?= $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] ?>" method="post">
	
	<table>
		<tr>
			<td>
				<input type="button" name="viewAll" value="View All" />
				<br />
				<input type="button" name="viewNone" value="View None" />
			</td>
			<td>
				<input type="button" name="modifyAll" value="Modify All" />
				<br />
				<input type="button" name="modifyNone" value="Modify None" />
			</td>
		</tr>
	</table>

	<?php
	$sql = sprintf("SELECT viewValue, modifyValue, ident FROM %s WHERE userID='%s'",
		$engine->openDB->escape($engine->dbTables("userPermissions")),
		$engine->cleanGet['MYSQL']['user']
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['result']) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$vals[$row['ident']]['view']   = $row['viewValue'];
			$vals[$row['ident']]['modify'] = $row['modifyValue'];
		}
	}

	print $permissions->buildFormChecklist($vals);
	?>

	{engine name="insertCSRF"}
	<input type="submit" name="submitPermissions" value="Update Permissions" />
</form>

<script type="text/javascript">
	$('input[type=button][name=viewAll]').click(function(){
		$('table input[type=checkbox][name*=view]').attr('checked','true');
	})
	$('input[type=button][name=viewNone]').click(function(){
		$('table input[type=checkbox]').removeAttr('checked');
	})
	$('input[type=button][name=modifyAll]').click(function(){
		$('table input[type=checkbox]').attr('checked','true');
	})
	$('input[type=button][name=modifyNone]').click(function(){
		$('table input[type=checkbox][name*=modify]').removeAttr('checked');
	})
	$('table input[type=checkbox][name*=view]').click(function(){
		if (! $(this).attr('checked')) {
			parent = $(this).closest('tr');
			$('input[type=checkbox]', parent).removeAttr('checked');
		}
	})
	$('table input[type=checkbox][name*=modify]').click(function(){
		if ($(this).attr('checked')) {
			parent = $(this).closest('tr');
			$('input[type=checkbox]', parent).attr('checked','true');
		}
	})
</script>

<?php
$engine->eTemplate("include","footer");
?>
