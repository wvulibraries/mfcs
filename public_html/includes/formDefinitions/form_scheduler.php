<?php

function defineList($scheduler, $tableName) {
	$listitem = new listManagement($tableName);

	$listitem->addField(array(
		"field"    => "name",
		"label"    => "Name",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $scheduler->getCronsArray()
		));

	$listitem->addField(array(
		"field"    => "minute",
		"label"    => "Minute",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $scheduler->minuteSelect()
		));

	$listitem->addField(array(
		"field"    => "hour",
		"label"    => "Hour",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $scheduler->createSelect(0, 23)
		));

	$listitem->addField(array(
		"field"    => "dayofmonth",
		"label"    => "Day of the Month",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $scheduler->createSelect(1, 31)
		));

	$listitem->addField(array(
		"field"    => "month",
		"label"    => "Month",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $scheduler->monthSelect()
		));

	$listitem->addField(array(
		"field"    => "dayofweek",
		"label"    => "Day of the Week",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => $scheduler->weekdaySelect()
		));

	$listitem->addField(array(
		"field"    => "runnow",
		"label"    => "Run Task after Saving?",
		"dupes"    => TRUE,
		"type"     => "yesNo"
		));

	$listitem->addField(array(
		"field"    => "active",
		"label"    => "Is the Task Active?",
		"dupes"    => TRUE,
		"type"     => "yesNo"
		));

	$listitem->addField(array(
		"field"    => "lastrun",
		"label"    => "Last Run Time",
		"disabled" => TRUE,
		"blank" 	 => TRUE,
		));

	return $listitem;
}

?>
