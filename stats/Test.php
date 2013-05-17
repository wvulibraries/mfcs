<?php

include("../header.php");

$objects = objects::getAllObjectsForForm("22");
localvars::add("totalTestObjects",count($objects));

$objects = objects::getAllObjectsForForm("23");
localvars::add("totaltest2Objects",count($objects));

$totalMediaItems = 0;
foreach ($objects as $I=>$object) {
	$totalMediaItems += count($object['data']['untitled3']['files']['archive']);
}
localvars::add("totalMediaItems",$totalMediaItems);


$engine->eTemplate("include","header");
?>

<h1>Stats for Test form</h1>

<table>

	<tr>
		<th>
			Total Objects:
		</th>
		<td>
			{local var="totalTestObjects"}
		</td>
	</tr>

</table>

<h1>Stats for test 2 form</h1>

<table>

	<tr>
		<th>
			Total Objects:
		</th>
		<td>
			{local var="totaltest2Objects"}
		</td>
	</tr>
	<tr>
		<th>
			Total Media Items:
		</th>
		<td>
			{local var="totalMediaItems"}
		</td>
	</tr>

</table>

<?php
$engine->eTemplate("include","footer");
?>