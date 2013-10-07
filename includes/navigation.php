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

		if(!is_array($form['navigation'])) return $output;
		foreach ($form['navigation'] as $item) {

			// deal with field sets
			if ($item['grouping'] != $currentGroup) {
				if ($currentGroup != "") {
					$output .= "</ul></li>";
				}
				if (!is_empty($item['grouping'])) {
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

				$item['url'] = preg_replace("/{siteRoot}/", mfcs::config("siteRoot"), $item['url']);

				$output .= sprintf('<a href="%s">%s</a>',
					htmlSanitize($item['url']),
					htmlSanitize($item['label'])
				);
			}
			else if ($item['type'] == "objectForm" || $item['type'] == "metadataForm") {

				$form = forms::get($item['formID']);

				$output .= sprintf('<a href="" data-formID="%s" data-header="%s" data-toggle="modal" class="metadataObjectEditor">%s</a>',
					htmlSanitize($item['formID']),
					htmlSanitize($item['label']),
					htmlSanitize(!empty($form['displayTitle']) ? $form['displayTitle'] : (!empty($form['title']) ? $form['title'] : '[No form title]'))
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
