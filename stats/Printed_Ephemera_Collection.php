<?php

include("../header.php");

$objects = objects::getAllObjectsForForm("2");
localvars::add("totalPECObjects",count($objects));

$totalMediaItems       = 0;
$totalMediaItemsPublic = 0;
$totalPECObjectsPublic = 0;
$totalItemCount        = 0;
$totalItemCountPublic  = 0;

foreach ($objects as $I=>$object) {
	$totalMediaItems       += ($object['data']['hasMedia'] == "Yes")? 1 : 0;
	$totalMediaItemsPublic += ($object['data']['hasMedia'] == "Yes" && $object['data']['publicRelease'] == "Yes")? 1 : 0;
	$totalPECObjectsPublic += ($object['data']['publicRelease'] == "Yes")? 1 : 0;
	$totalItemCount        += ((int)$object['data']['itemCount'] > 0)? (int)$object['data']['itemCount'] : 0;
	$totalItemCountPublic  += ((int)$object['data']['itemCount'] > 0 && $object['data']['publicRelease'] == "Yes")? (int)$object['data']['itemCount'] : 0;
}
localvars::add("totalMediaItems",$totalMediaItems);
localvars::add("totalMediaItemsPublic",$totalMediaItemsPublic);
localvars::add("totalPECObjectsPublic",$totalPECObjectsPublic);
localvars::add("totalItemCount",$totalItemCount);
localvars::add("totalItemCountPublic",$totalItemCountPublic);

$metadataForms   = forms::getObjectFormMetaForms("2");
$metaInformation = "<table>";
foreach ($metadataForms as $form) {

	$metaInformation .= sprintf("<tr><td><strong>%s</strong></td><td>%d</td></tr>",
		$form['displayTitle'],
		count(objects::getAllObjectsForForm($form['ID']))
		);
}
$metaInformation .= "</table>";

localvars::add("metaInformation",$metaInformation);

$engine->eTemplate("include","header");
?>

<h1>Stats for PEC</h1>

<table id="statsTable" width="600">

	<tr style="background-color: #EEEEFF;">
		<th align="left">
			Type
		</th>
		<th align="right">
			Total Count
		</th>
		<th align="right">
			Public Count
		</th>
	</tr>
	<tr>
		<td bgcolor="#bfbfbf" align="left">
			Records:
		</td>
		<td bgcolor="#bfbfbf" align="right">
			{local var="totalPECObjects"}
		</td>
		<td bgcolor="#bfbfbf" align="right">
			{local var="totalPECObjectsPublic"}
		</td>
	</tr>
	<tr>
		<td bgcolor="#f2f2f2" align="left">
			Items:
		</td>
		<td bgcolor="#f2f2f2" align="right">
			{local var="totalItemCount"}
		</td>
		<td bgcolor="#f2f2f2" align="right">
			{local var="totalItemCountPublic"}
		</td>
	</tr>
	<tr>
		<td bgcolor="#bfbfbf" align="left">
			Has Media:
		</td>
		<td bgcolor="#bfbfbf" align="right">
			{local var="totalMediaItems"}
		</td>
		<td  bgcolor="#bfbfbf" align="right">
			{local var="totalMediaItemsPublic"}
		</td>
	</tr>

</table>

<h1>Metadata Form Stats</h1>

{local var="metaInformation"}


<?php
$engine->eTemplate("include","footer");
?>