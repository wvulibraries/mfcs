<?php

function defineList($s, $tableName) {
	$l      = new listManagement($tableName);

	$l->addField(array(
		"field"    => "name",
		"label"    => "Name",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $s->getCronsArray()
		));

	$l->addField(array(
		"field"    => "minute",
		"label"    => "Minute",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $s->minuteSelect()
		));

	$l->addField(array(
		"field"    => "hour",
		"label"    => "Hour",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $s->createSelect(0, 23)
		));

	$l->addField(array(
		"field"    => "dayofmonth",
		"label"    => "Day of the Month",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $s->createSelect(1, 31)
		));

	$l->addField(array(
		"field"    => "month",
		"label"    => "Month",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $s->monthSelect()
		));

	$l->addField(array(
		"field"    => "dayofweek",
		"label"    => "Day of the Week",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $s->weekdaySelect()
		));

	$l->addField(array(
		"field"    => "runnow",
		"label"    => "Run Task after Saving?",
		"dupes"    => TRUE,
		"type"     => "yesNo"
		));

	$l->addField(array(
		"field"    => "active",
		"label"    => "Is the Task Active?",
		"dupes"    => TRUE,
		"type"     => "yesNo"
		));

	$l->addField(array(
		"field"    => "lastrun",
		"label"    => "Last Run Time",
		"disabled" => TRUE,
		"blank" 	 => TRUE, 
		));

	return $l;
}

?>
