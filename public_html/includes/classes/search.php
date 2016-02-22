<?php

class mfcsSearch {

	//Template Stuff
	private $pattern = "/\{mfcsSearch\s+(.+?)\}/";
	private $function = "mfcsSearch::templateMatches";

	function __construct() {		
		mfcs::$engine->defTempPattern($this->pattern,$this->function,$this);
	}

	public static function buildInterface() {

		$searchInterface = file_get_contents("../../includes/templates/searchInterfaceTemplate.html");

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
			case "formFieldList":
				$output = mfcsSearch::formFieldListing();
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

			if (mfcsPerms::isViewer($form['ID']) === FALSE) continue;

			$output .= sprintf('<option value="%s" %s>%s</option>',
				$form['ID'],
				($form['ID'] == sessionGet("lastSearchForm"))?"selected":"",
				$form['title']
				);
		}

		return $output;
	}

	public static function formFieldListing($formID = NULL) {

		$old_formID = sessionGet("lastSearchForm");
		if (validate::integer($old_formID)) $formID = $old_formID;

		$options  = '<option value="idno">IDNO</option>';
		$options .= '<option value="mfcs_keyword">Any Field</option>';

		if (isnull($formID)) {
			return $options;
		}

		return $options . mfcsSearch::formFieldOptions($formID);

	}

	public static function formFieldOptions($formID) {

		$form = forms::get($formID);

		$output = '<optgroup label="Form Fields">';
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

		// If no form was selected
		if (isempty($post['formList'])) {
			return FALSE;
		}

		// Save the post for later use (like pagination pages)
		sessionSet('searchPOST', $post);

		// build date clause
		if (!isempty($post['startDate']) && isempty($post['endDate'])) {
			// start provided, but no end
			$date_clause = sprintf("AND `createTime` >= '%s'",
				strtotime($post['startDate'])
				);
		}
		else if (isempty($post['startDate']) && !isempty($post['endDate'])) {
			// end provided, but no start
			$date_clause = sprintf("AND `createTime` <= '%s'",
				strtotime($post['endDate'])
				);
		}
		else if (!isempty($post['startDate']) && !isempty($post['endDate'])) {
			// both start and end provided
			$date_clause = sprintf("AND `createTime` >= '%s' AND `createTime` <= '%s'",
				strtotime($post['startDate']),
				strtotime($post['endDate'])
				);
		}
		else {
			$date_clause = "";
		}

		// build query
		$queryString = ($post['fieldList'] == "idno")?sprintf("LOWER(`%s`)", $post['fieldList']):"`objectsData`.`value`";

		if (preg_match('/^\\\\"(.+?)\\\\"/',trim($post['query']),$matches)) {
			// Qouted string, exact match
			$queryString .= sprintf("='%s'",
				strtolower($matches[1])
				);
		}
		else if (preg_match('/^(.+?)\*$/',trim($post['query']),$matches)) {
			// wild card at the end
			$queryString .= sprintf(" LIKE '%s%%'",
				strtolower($matches[1])
				);
		}
		else if (preg_match('/^\*(.+?)$/',trim($post['query']),$matches)) {
			// wild card at the beginning
			$queryString .= sprintf(" LIKE '%%%s'",
				strtolower($matches[1])
				);
		}
		else {
			// normal keyword (search anywhere)
			$queryString .= sprintf(" LIKE '%%%s%%'",
				strtolower($post['query'])
				);
		}

		if ($post['fieldList'] == "mfcs_keyword") {
			$query_field = "";
		}
		else {
			$query_field = sprintf("AND `objectsData`.`fieldName`='%s'",$post['fieldList']);
		}

		// IDNO search. Easy PEasy from the objcets table
		if ($post['fieldList'] == "idno") {

			$sql = sprintf("SELECT * FROM `objects` WHERE %s AND `formID`='%s' %s ORDER BY LENGTH(idno), `idno`",
					$queryString,
					$post['formList'],
					$date_clause
					);

		}
		else {

			$sql = sprintf("SELECT `objects`.* FROM `objects` LEFT JOIN `objectsData` ON `objectsData`.`objectID`=`objects`.`ID` WHERE `objectsData`.`formID`='%s' %s AND %s %s ORDER BY LENGTH(idno), `idno`",
				$post['formList'],
				$query_field,
				$queryString,
				$date_clause
				);

		}

		return objects::getObjectsForSQL($sql);

	}

}

?>