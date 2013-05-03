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
		return '<option>Here!</option>';
	}

}

?>