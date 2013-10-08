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
	public static function search($post) {

		if (isempty($post['formList'])) {
			return FALSE;
		}

		// Save the post for later use (like pagination pages)
		sessionSet('searchPOST', $post);

		if (!isempty($post['startDate']) && !isempty($post['endDate'])) {
			$date = TRUE;
			$dateWhere = sprintf(" AND `createTime`>='%s' AND `createTime` <='%s'",
				strtotime($post['startDate']),
				strtotime($post['endDate']));
		}
		else {
			$date = FALSE;
			$dateWhere = "";
		}

		$objects = objects::getAllObjectsForForm($post['formList']);

		$results = array();
		foreach ($objects as $object) {

			$found = FALSE;
			if (!isempty($post['query']) ) { 
				if (isset($object['data'][$post['fieldList']]) && stripos($object['data'][$post['fieldList']],$post['query']) !== FALSE) {
					$results[$object['ID']] = $object;
					$found = TRUE;	
				}
				else if ($post['fieldList'] == "idno" && stripos($object[$post['fieldList']],$post['query']) !== FALSE) {
					$results[$object['ID']] = $object;
					$found = TRUE;	
				}
			}
			else if (isempty($post['query']) && $date === TRUE) {
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