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

		$output = '<ul class="pickList">';
		foreach ($projects as $project) {
			$output .= sprintf('<li><a href="list.php?listType=project&amp;projectID=%s" class="btn">%s</a></li>',
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

		$output = '<ul class="pickList">';
		foreach ($forms as $form) {
			$output .= sprintf('<li><a href="list.php?listType=form&amp;formID=%s" class="btn">%s</a></li>',
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

	public static function createProjectObjectList($projectID) {

		$engine  = EngineAPI::singleton();
		$objects = objects::getAllObjectsForProject($projectID);

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']));

		}

		return self::createTable($data);

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

	public static function generateFormSelectList() {

		if (($forms = forms::getObjectForms()) === FALSE) {
			return FALSE;
		}

		if (($currentProjects = users::loadProjects()) === FALSE) {
			return FALSE;
		}  

		$currentProjectFormList = '<h1 class="pickListHeader">Current Projects:</h1> <br /><ul class="pickList">';
		$formList               = '<h1 class="pickListHeader">All Other Forms:</h1> <br /><ul class="pickList">';

		foreach ($forms as $form) {

			// @TODO
			// if (projects::checkPermissions($row['ID']) === TRUE) {
			// }
			
			foreach ($currentProjects as $projectID => $projectName) {
				if (forms::checkFormInProject($projectID,$form['ID'])) {
					$currentProjectFormList .= sprintf('<li><a href="object.php?formID=%s" class="btn">%s</a></li>',
						htmlSanitize($form['ID']),
						htmlSanitize($form['title'])
						);

					continue 2;
				}
			}

			$formList .= sprintf('<li><a href="object.php?formID=%s" class="btn">%s</a></li>',
				htmlSanitize($form['ID']),
				htmlSanitize($form['title'])
				);

		}
		$formList               .= "</ul>";
		$currentProjectFormList .= "</ul>";

		return $currentProjectFormList . $formList;

	}

	public static function generateFormSelectListForFormCreator($metadata = TRUE) {

		$engine  = EngineAPI::singleton();

		// @TODO object forms and metadata forms need separated
		$sql       = sprintf("SELECT `ID`, `title` FROM `forms` ORDER BY `metadata`, `title`");
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			errorHandle::errorMsg("Error getting Projects");
			return FALSE;
		}

		$formList = '<ul class="pickList">';
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		// if (projects::checkPermissions($row['ID']) === TRUE) {
		// }
			$formList .= sprintf('<li><a href="index.php?id=%s" class="btn">%s</a></li>',
				$engine->openDB->escape($row['ID']),
				$engine->openDB->escape($row['title'])
				);

		}
		$formList .= "<ul>";

		return $formList;

	}

}

?>