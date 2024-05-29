<?php

class valid {

	// takes associative array
	// array("objectID" = boolean, default: TRUE, check that we have a valid object ID (NOTE: this does not explicitly require that an ObjectID be present. Use objectIF_required)
	// formID = boolean, default: true, check that we have a valid form ID
	// authtype = viewer | editor | admin | FALSE, if false, doesn't check auth type. Default FALSE. 
	// metadata = boolean | null, default: null, If true only allow metadata. If false only allow object. If NULL allow either. 
	// $productionReady = boolean, default TRUE (if formID is also true), Form is production ready
	// $objectID_required = boolean, default: FALSE, if true require that ObjectID is provided.
	// $object_in_form = boolean, default: TRUE, if true object must be part of the form. Requires objectID in queryString $engine->cleanGet['MYSQL']['objectID']
	// $parent_id = boolean, default: FALSE. If true, checks $engine->cleanGet['MYSQL']['parentID'] for a valid ObjectID
	// )
	public static function validate($test) {
		$engine = mfcs::$engine;
		$objects = new objects($engine);
		$forms = new forms($engine);

		// setup default values
		$test = array_merge(array(
			'objectID' => true,
			'formID' => true,
			'authtype' => false,
			'metadata' => null,
			'productionReady' => ($test['formID']) ? true : false,
			'objectID_required' => false,
			'object_in_form' => true,
			'parent_id' => false,
		), $test);

		// validate options
		if (!is_bool($test['objectID_required'])) {
			return "Invalid 'objectID_required' provided";
		}

		if ($test['objectID'] && !$objects->validID($test['objectID_required'])) {
			return "ObjectID Provided is invalid.";
		}

		if ($test['parent_id'] && !$objects->validID(true, $engine->cleanGet['MYSQL']['parentID'])) {
			return "ParentID Provided is invalid.";
		}

		if ($test['formID'] && !$forms->validID()) {
			return "Invalid/No Form ID Provided.";
		}

		if ($test['metadata'] === false && $forms->isMetadataForm($engine->cleanGet['MYSQL']['formID'])) {
			return "Metadata form provided (Object forms only).";
		}

		if ($test['metadata'] === true && !$forms->isMetadataForm($engine->cleanGet['MYSQL']['formID'])) {
			return "Object form provided (Metadata forms only).";
		}

		if ($test['productionReady'] && !isset($engine->cleanGet['MYSQL']['formID'])) {
			return "No Form ID provided to test for Production Ready.";
		}

		if ($test['productionReady'] && isset($engine->cleanGet['MYSQL']['formID']) && !forms::isProductionReady($engine->cleanGet['MYSQL']['formID'])) {
			return "Form is not production ready.";
		}

		if ($test['object_in_form'] && !isnull($engine->cleanGet['MYSQL']['objectID']) && !objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'], $engine->cleanGet['MYSQL']['objectID'])) {
			return "Object not from this form.";
		}

		if ($test['authtype']) {
			if (!mfcsPerms::isActive()) {
				return "Account is not active.";
			}

			switch (strtolower($test['authtype'])) {
				case "admin":
					if (!mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID'])) {
						return "Admin Permission Denied";
					}
					break;
				case "editor":
					if (!mfcsPerms::isEditor($engine->cleanGet['MYSQL']['formID'])) {
						return "Edit Permission Denied";
					}
					break;
				case "viewer":
					if (!mfcsPerms::isViewer($engine->cleanGet['MYSQL']['formID'])) {
						return "Viewer Permission Denied";
					}
					break;
				default:
					return "Permission Denied. (Fallback)";
			}
		}

		return true;
	}
	
}

?>
