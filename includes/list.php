<?php
 
class listGenerator {

	public static function createInitialSelectList() {

		return file_get_contents("includes/listTemplates/initialSelectList.php");

	}

	public static function createAllObjectList($start=0,$length=50,$orderBy=NULL) {

		$engine  = EngineAPI::singleton();
		$objects = objects::getObjects($start,$length,FALSE);

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']));

		}

		return self::createTable($data);

	}

	public static function createProjectSelectList() {
		$engine   = EngineAPI::singleton();
		$projects = projects::getProjects();

		$output = '<ul>';
		foreach ($projects as $project) {
			$output .= sprintf('<li><a href="list.php?listType=project&amp;projectID=%s">%s</a></li>',
				$project['ID'],
				$project['projectName']
				);
		}
		$output .= '</ul>';

		return $output;
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

			$data[] = array(self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']),$object['ID'],$object['idno']);
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
		}

		$table->headers($headers);

		
		return $table->display($data);


	}

	private static function genLinkURLs($url,$objectID) {

		$urls['view']      = sprintf("%sdataView/object.php?objectID=",    localvars::get("siteRoot"));
		$urls['edit']      = sprintf("%sdataEntry/object.php?objectID=",   localvars::get("siteRoot"));
		$urls['revisions'] = sprintf("%sdataEntry/revisions.php?objectID=",localvars::get("siteRoot"));

		if (!isset($urls[strtolower($url)])) return FALSE;

		return sprintf('<a href="%s%s">%s</a>',$urls[strtolower($url)],$objectID,htmlSanitize(strtolower($url)));
	}

}

?>