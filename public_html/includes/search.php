<?php

class mfcsSearch {

	//Template Stuff
	private $pattern = "/\{mfcsSearch\s+(.+?)\}/";
	private $function = "mfcsSearch::templateMatches";

	function __construct() {		
		mfcs::$engine->defTempPattern($this->pattern,$this->function,$this);
	}

	public static function buildInterface() {

		$searchInterface = file_get_contents("../includes/templates/searchInterfaceTemplate.html");

		return ($searchInterface);

	}


	public static function templateMatches($matches) {
		$search   = mfcs::$engine->retTempObj("mfcsSearch");
		$attPairs = attPairs($matches[1]);
		
		$output = "Error in mfcsSearch";

		switch($attPairs['name']) {
			case "formList":
				$output = mfcsSearch::formListing();
				break;
			default:
			    $output = "Error: name function '".$attPairs['name']."' not found.";
		}

		return($output);
	}

	public static function formListing() {
		$forms = forms::getObjectForms();

		$output = '<option value="NULL">-- Select a Form --</option>';
		foreach ($forms as $form) {
			$output .= sprintf('<option value="%s">%s</option>',
				$form['ID'],
				$form['title']
				);
		}

		return $output;
	}

	public static function formFieldOptions($formID) {
		$form = forms::get($formID);

		$output = '<option value="idno">IDNO</option><optgroup label="Form Fields">';
		foreach ($form['fields'] as $field) {

			if (isset($field['choicesType'])) continue;

			$output .= sprintf('<option value="%s">%s</option>',
				$field['name'],
				$field['label']
				);
		}
		$output .= "</optgroup>";

		return($output);
	}

	// post is expected to be mysql sanitized
	// 
	// @TODO ... this search function needs a lot of work. Its awful. 
	public static function search($post) {

		if (isempty($post['formList'])) {
			return FALSE;
		}

		// Save the post for later use (like pagination pages)
		sessionSet('searchPOST', $post);

		if (!isempty($post['startDate']) && !isempty($post['endDate'])) {
			$date = TRUE;

			// @tODO build where clause for date here
		}
		else {
			$date = FALSE;
		}

		// build query for idno searches
		if ($post['fieldList'] == "idno" && preg_match('/^\\\\"(.+?)\\\\"/',trim($post['query']),$matches)) {
			$queryString = sprintf("LOWER(`idno`)='%s'",
				strtolower($matches[1])
				);
		}
		else if ($post['fieldList'] == "idno" && preg_match('/^(.+?)\*$/',trim($post['query']),$matches)) {
			$queryString = sprintf("LOWER(`idno`) LIKE '%s%%'",
				strtolower($matches[1])
				);
		}
		else if ($post['fieldList'] == "idno" && preg_match('/^\*(.+?)$/',trim($post['query']),$matches)) {
			$queryString = sprintf("LOWER(`idno`) LIKE '%%%s'",
				strtolower($matches[1])
				);
		}
		else {
			$queryString = sprintf("LOWER(`idno`) LIKE '%%%s%%'",
				strtolower($post['query'])
				);
		}

		// if idno search, build mysql here and search
		if ($post['fieldList'] == "idno") {
			$sql = sprintf("SELECT * FROM `objects` WHERE %s",
				$queryString
				);
			$objects = objects::getObjectsForSQL($sql);

		}
		else if ($post['fieldList'] == "idno" && $date === TRUE) {
			$sql = sprintf("SELECT * FROM `objects` WHERE `idno` LIKE '%%%s%%' AND `formID`='%s' AND `createTime` >= '%s' AND `createTime` <= '%s'",
					$post['query'],
					$post['formList'],
					strtotime($post['startDate']),
					strtotime($post['endDate'])
					);

			$objects = objects::getObjectsForSQL($sql);
		}
		// else if there is a date range, build a date range search to get 
		else if ($date === TRUE) {

			$sql = sprintf("SELECT * FROM `objects` WHERE AND `formID`='%s' AND `createTime` >= '%s' AND `createTime` <= '%s'",
				$post['formList'],
				strtotime($post['startDate']),
				strtotime($post['endDate'])
				);
			
			$objects = objects::getObjectsForSQL($sql);
		}
		// else, get everything to perform search. 
		else {
			$objects = objects::getAllObjectsForForm($post['formList'],"idno",FALSE);
		}

		$results = array();
		foreach ($objects as $object) {

			// check that the item is in the date range, if a date range is specified.
			// if ($date === TRUE && ($object['createTime'] < strtotime($post['startDate']) || $object['createTime'] > strtotime($post['endDate']))) {
			// 	continue;
			// }

			$found = FALSE;
			if (!isempty($post['query']) ) { 
				if ($post['fieldList'] == "idno") {
					$found = TRUE;	
				}
				else if (isset($object['data'][$post['fieldList']]) && stripos($object['data'][$post['fieldList']],$post['query']) !== FALSE) {
					$found = TRUE;	
				}
			}
			// If the query is empty we assume that everything should be returned. 
			// it has already been filtered by date at this point.
			else if (is_empty($post['query'])) {
				$found = TRUE;
			}

			if ($found === TRUE) {
				$results[$object['ID']] = $object;
			}

		}

		return($results);
	}

}

?>