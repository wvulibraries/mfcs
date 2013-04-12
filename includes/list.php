<?php

class listGenerator {

	public static function createInitialSelectList() {

		return file_get_contents("includes/listTemplates/initialSelectList.php");

	}

	public static function createAllObjectList($start=0,$length=50,$orderBy=NULL) {

		$engine  = EngineAPI::singleton();
		$objects = objects::get();

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],"View","Edit","Revisions","Delete");

		}

		return self::createTable($data);

	}

	public static function createFormSelectList() {

		$engine  = EngineAPI::singleton();
		$forms   = forms::getForms(TRUE);

		$output = '<ul>';
		foreach ($forms as $form) {
			$output .= sprintf('<li><a href="list.php?listType=form&amp;formID=%s">%s</a></li>',
				$form['ID'],
				$form['title']
				);
		}
		$output .= '</ul>';

		return($output);

	}

	public static function createFormObjectList($formID) {

		$engine  = EngineAPI::singleton();
		$objects = objects::getAllObjectsForForm($formID);
		$form    = forms::get($formID);

		$headers   = array();
		$headers[] = "View";
		$headers[] = "Edit";
		$headers[] = "Revisions";
		$headers[] = "Delete";
		$headers[] = "System IDNO";
		$headers[] = "Form IDNO";
		foreach($form['fields'] as $field) {
			if (strtolower($field['type']) == "idno") continue;

			if ($field['displayTable'] == "true") {
				$headers[] = $field['label'];
			}
		}

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$data[] = array("View","Edit","Revisions","Delete",$object['ID'],$object['idno']);
			foreach($form['fields'] as $field) {
				if (strtolower($field['type']) == "idno") continue;

				if ($field['displayTable'] == "true") {
					$data[] = $object['data'][$field['name']];
				}
			}

		}

		return self::createTable($data,$headers);

	}

	private static function createTable($data,$headers = NULL) {

		$table = new tableObject("array");

		$table->summary = "Object Listing";
		$table->sortable = TRUE;

		if (isnull($headers)) {
			$headers = array();
			$headers[] = "System IDNO";
			$headers[] = "Form IDNO";
			$headers[] = "Title";
			$headers[] = "View";
			$headers[] = "Edit";
			$headers[] = "Revisions";
			$headers[] = "Delete";
		}

		$table->headers($headers);

		
		return $table->display($data);


	}

}

?>