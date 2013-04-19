<?php

class navigation {

	public static function updateFormNav($groupings) {

		$groupings = json_decode($groupings, TRUE);

		if (!is_empty($groupings)) {
			foreach ($groupings as $I => $grouping) {
				$positions[$I] = $grouping['position'];
			}

			array_multisort($positions, SORT_ASC, $groupings);
		}

		$groupings = encodeFields($groupings);

		$sql = sprintf("UPDATE `forms` SET `navigation`='%s' WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($groupings),
			mfcs::$engine->cleanGet['MYSQL']['id']
		);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

public static function buildProjectNavigation($formID) {
	
	if (($form = forms::get($formID)) === FALSE) {
		return(FALSE);
	}

	localvars::add("formID",htmlSanitize($formID));

	$output       = "";
	$currentGroup = "";

	foreach ($form['navigation'] as $item) {

		// deal with field sets
		if ($item['grouping'] != $currentGroup) {
			if ($currentGroup != "") {
				$output .= "</ul></li>";
			}
			if (!isempty($item['grouping'])) {
				$output .= sprintf('<li><strong>%s</strong><ul>',
					$item['grouping']
				);
			}
			$currentGroup = $item['grouping'];
		}

		$output .= "<li>";
		if ($item['type'] == "logout") {
			$output .= sprintf('<a href="%s">%s</a>',
				htmlSanitize($item['url']),
				htmlSanitize($item['label'])
			);
		}
		else if ($item['type'] == "link") {
			$output .= sprintf('<a href="%s">%s</a>',
				htmlSanitize($item['url']),
				htmlSanitize($item['label'])
			);
		}
		else if ($item['type'] == "objectForm" || $item['type'] == "metadataForm") {
			$output .= sprintf('<a href="#metadataModal" data-formID="%s" data-header="%s" data-toggle="modal" class="metadataObjectEditor">%s</a>',
				htmlSanitize($item['formID']),
				htmlSanitize($item['label']),
				htmlSanitize($item['label'])
			);
		}
		else {
			$output .= sprintf('%s',
				htmlSanitize($item['label'])
			);
		}
		$output .= "</li>";

	}


	return $output;
}

}

?>