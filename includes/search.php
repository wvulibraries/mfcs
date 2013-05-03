<?php

class mfcsSearch {

	public static function buildInterface() {

		$searchInterface = file_get_contents("../includes/templates/searchInterfaceTemplate.html");

		return ($searchInterface);

	}

}

?>