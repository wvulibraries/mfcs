<?php
include("header.php");


$test = new mfcsPermissions("permissions",$engine,"projects","tempPermissions");
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
	print $test->buildFormChecklist("0","0");
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
