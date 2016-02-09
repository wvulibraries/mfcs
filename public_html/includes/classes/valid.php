<?php

class valid {

	// takes associative array
	// array("objectID" = boolean, default: TRUE, check that we have a valid object ID (NOTE: this does not explicitly require that an ObjectID be present. Use objectIF_required)
	// formID = boolean, default: true, check that we have a valid form ID
	// authtype = viewer | editor | admin | FALSE, if false, doesn't check auth type
	// metadata = boolean | null, default: False, If true only allow metadata. If false only allow object. If NULL allow either. 
	// $productionReady = boolean, default TRUE, Form is production ready
	// $objectID_required = boolean, default: FALSE, if true require that ObjectID is provided.
	// $object_in_form = boolean, default: TRUE, if true object must be part of the form
	// )
	public static function validate($objectID=TRUE,$formID=TRUE,$authType=viewer,$metadata=FALSE,$productionReady=TRUE,$objectID_required=FALSE,$object_in_form = TRUE) {


	}
	
}

?>
