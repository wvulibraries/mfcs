<?php
/**
*
*/
class validator {

	function __construct() {
	}

	public static function validate($type,$data,$extraData=NULL) {
		if (in_array($type, self::getValidationTypes())) {

			if (strtolower($type) === 'regexp' && validate::regexp($extraData,$data)) {
				return TRUE;
			}
			// else if (strtolower($type) === 'date' && validate::date($data,$extraData)) {
			// 	return TRUE;
			// }
			else if (method_exists("validate", $type) && validate::$type($data)) {
				return TRUE;
			}

		}
		errorHandle::errorMsg("Entry, ".htmlSanitize($data).", is not valid.");
		return FALSE;

	}

	public static function getValidationTypes() {
		return array(
			"phoneNumber"          => "Phone Number",
			"ipAddr"               => "IP Address",
			"ipAddrRange"          => "IP Address Range",
			"url"                  => "URL",
			"optionalURL"          => "Optional URL",
			"emailAddr"            => "Email Address",
			"internalEmailAddr"    => "Internal Email Address",
			"integer"              => "Integer",
			"integerSpaces"        => "Integer with Spaces",
			"alphaNumeric"         => "Letters and Numbers",
			"alphaNumericNoSpaces" => "Letters and Numbers, No Spaces",
			"alpha"                => "Letters",
			"alphaNoSpaces"        => "Letters, No Spaces",
			"noSpaces"             => "No Spaces",
			"noSpecialChars"       => "No Special Characters",
			"date"                 => "Date",
			"serialized"           => "Serialized String",
			"regexp"               => "Regular Expression",
			);
	}

}
?>