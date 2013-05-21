<?php

class listGenerator {

	public static function createInitialSelectList() {

		return file_get_contents("includes/listTemplates/initialSelectList.php");

	}

	public static function createAllObjectList($start=0,$length=50,$orderBy=NULL,$objects=NULL) {

		$engine  = EngineAPI::singleton();
		if (isnull($objects)) $objects = objects::getObjects($start,$length,FALSE);

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$data[] = array(
				$object['ID'],
				$object['idno'],
				isset($object['data'][$form['objectTitleField']]) ? $object['data'][$form['objectTitleField']] : '',
				self::genLinkURLs("view",$object['ID']),
				self::genLinkURLs("edit",$object['ID']),
				self::genLinkURLs("revisions",$object['ID']));

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
		$forms   = forms::getForms(TRUE,TRUE);

		$output = '<ul class="pickList">';
		foreach ($forms as $form) {

			if ($form === FALSE) continue;

			if (!mfcsPerms::isViewer($form['ID'])) continue;

			$output .= sprintf('<li><a href="list.php?listType=form&amp;formID=%s" class="btn">%s</a></li>',
				$form['ID'],
				$form['title']
				);
		}
		$output .= '</ul>';

		return($output);

	}

	public static function createFormObjectList($formID) {

		$engine        = mfcs::$engine;
		$objects       = objects::getAllObjectsForForm($formID);
		if (($form          = forms::get($formID)) === FALSE) {
			return FALSE;
		}
		$excludeFields = array("idno","file");

		$headers = array("View","Edit","Revisions","System IDNO","Form IDNO");
		foreach($form['fields'] as $field) {
			if (in_array(strtolower($field['type']), $excludeFields)) continue;

			if (str2bool($field['displayTable'])) {
				$headers[] = $field['label'];
			}
		}

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$tmp = array(self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']),$object['ID'],$object['idno']);
			foreach($form['fields'] as $field) {
				if (in_array(strtolower($field['type']), $excludeFields)) continue;

				if (str2bool($field['displayTable'])) {
					$tmp[] = $object['data'][$field['name']];
				}
			}

			$data[] = $tmp;

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

		$table->summary  = "Object Listing";
		$table->sortable = FALSE;
		$table->class    = "table table-striped table-bordered";
		$table->id       = "objectListingTable";
		$table->layout   = TRUE;

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

		$userPaginationCount = users::user('pagination',25);
		if(sizeof($data) > $userPaginationCount){
			$engine                   = mfcs::$engine;
			$pagination               = new pagination(sizeof($data));
			$pagination->itemsPerPage = $userPaginationCount;
			$pagination->currentPage  = isset($engine->cleanGet['MYSQL'][ $pagination->urlVar ])
				? $engine->cleanGet['MYSQL'][ $pagination->urlVar ]
				: 1;

			$startPos   = $userPaginationCount*($pagination->currentPage-1);
			$dataNodes  = array_slice($data, $startPos, $userPaginationCount);
			$tableHTML  = $table->display($dataNodes);
			$tableHTML .= $pagination->nav_bar();
			return $tableHTML;
		}else{
			return $table->display($data);
		}
	}

	private static function genLinkURLs($type,$objectID) {
		switch(trim(strtolower($type))){
			case 'view':
				return sprintf('<a href="%sdataView/object.php?objectID=%s">View</a>', localvars::get("siteRoot"), $objectID);
				break;
			case 'edit':
				return sprintf('<a href="%sdataEntry/object.php?objectID=%s">Edit</a>', localvars::get("siteRoot"), $objectID);
				break;
			case 'revisions':
				$revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');
				return $revisions->hasRevisions($objectID)
					? sprintf('<a href="%sdataEntry/revisions.php?objectID=%s">View</a>', localvars::get("siteRoot"), $objectID)
					: '<span style="font-style:italic; color:#ccc;">View</span>';
				break;
			default:
				errorHandle::newError(__METHOD__."() - Invalid type passed!", errorHandle::LOW);
				return '';
				break;
		}
	}

	public static function generateFormSelectList($objectID = NULL) {

		if (isnull($objectID) && ($forms = forms::getObjectForms(TRUE)) === FALSE) {
			return FALSE;
		}
		else if (!isnull($objectID) && ($forms = forms::getObjectProjectForms($objectID)) === FALSE) {
			return FALSE;
		}

		if (($currentProjects = users::loadProjects()) === FALSE) {
			return FALSE;
		}

		$currentProjectFormList = '<h1 class="pickListHeader">Current Projects:</h1> <br /><ul class="pickList">';
		$formList               = '<h1 class="pickListHeader">All Other Forms:</h1> <br /><ul class="pickList">';

		foreach ($forms as $form) {

			if ($form === FALSE) continue;

			if (!mfcsPerms::isViewer($form['ID'])) continue;

			foreach ($currentProjects as $projectID => $projectName) {
				if (forms::checkFormInProject($projectID,$form['ID'])) {
					$currentProjectFormList .= sprintf('<li><a href="object.php?formID=%s%s" class="btn">%s</a></li>',
						htmlSanitize($form['ID']),
						(!isnull($objectID))?"&amp;parentID=".$objectID:"", // parent information
						htmlSanitize($form['title'])
						);

					continue 2;
				}
			}

			$formList .= sprintf('<li><a href="object.php?formID=%s%s" class="btn">%s</a></li>',
				htmlSanitize($form['ID']),
				(!isnull($objectID))?"&amp;parentID=".$objectID:"", // parent information
				htmlSanitize($form['title'])
				);

		}
		$formList               .= "</ul>";
		$currentProjectFormList .= "</ul>";

		return $currentProjectFormList . $formList;

	}

	private static function generateAccordionFormList_links($form,$entry,$metadata = FALSE) {

		if (!isset($form['ID']) || !isset($form['title'])) {
			return FALSE;
		}

		if ($entry === FALSE) {
			return sprintf('<a href="index.php?id=%s">%s</a>',
				htmlSanitize($form['ID']),
				htmlSanitize(!empty($form['displayTitle']) ? $form['displayTitle'] : (!empty($form['title']) ? $form['title'] : '[No form title]'))
				);
		}
		else {
			return sprintf('<a href="%sdataEntry/%s.php?formID=%s">%s</a>',
				localvars::get("siteRoot"),
				($metadata === TRUE)?"metadata":"object",
				htmlSanitize($form['ID']),
				htmlSanitize(!empty($form['displayTitle']) ? $form['displayTitle'] : (!empty($form['title']) ? $form['title'] : '[No form title]'))
				);
		}

	}

	// if entry is TRUE, the links will go to the data entry pages, otherwise form creator
	// pages
	public static function generateAccordionFormList($entry=FALSE) {

		if (($forms = forms::getObjectForms($entry)) === FALSE) {
			errorHandle::errorMsg("Error getting Object Forms");
			return FALSE;
		}

		if (($metaForms = forms::getMetadataForms($entry)) === FALSE) {
			errorHandle::errorMsg("Errot getting Metadata Forms.");
			return FALSE;
		}

		$output = '<div class="accordion" id="formListAccordion">';

		$count = 0;
		foreach ($forms as $form) {

			if ($form === FALSE) continue;

			if (($metedataForms = forms::getObjectFormMetaForms($form['ID'])) === FALSE) {
				errorHandle::errorMsg("Error getting Metadata Forms");
				return FALSE;
			}

			$output .= '<div class="accordion-group">';
			$output .= '<div class="accordion-heading" style="padding: 5px;">';
			$output .= '<div>';
			$output .= self::generateAccordionFormList_links($form,$entry);
			if(sizeof(forms::getObjectFormMetaForms($form['ID']))){
				$output .= sprintf('<a class="pull-right metadataListAccordionToggle" data-toggle="collapse" data-parent="#formListAccordion" href="#collapse%s">Show Metadata Forms</a>',
					++$count);
			}
			$output .= '</div>';
			$output .= "</div>"; // heading
			$output .= sprintf('<div id="collapse%s" class="accordion-body collapse">', $count);
     		$output .= '<div class="accordion-inner">';

     		$output .= '<ul>';
     		foreach ($metedataForms as $I=>$metadataForm) {

     			if (isset($metaForms[$I])) {
     				unset($metaForms[$I]);
     			}

     			$output .= '<li>';
     			if (($output .= self::generateAccordionFormList_links($metadataForm,$entry,($entry===TRUE)?TRUE:FALSE)) === FALSE) {
     				return FALSE;
     			}
     			$output .= '</li>';
     		}
     		$output .= '</ul>';

	 		$output .= "</div>"; // inner
	 		$output .= "</div>"; // body
			$output .= "</div>"; // group
		}
		$output .= "</div>";

		if (count($metaForms) > 0) {
			$output .= '<h1>Unassigned Metadata Forms</h1>';
			$output .= "<ul>";
			foreach ($metaForms as $metadataForm) {

				if ($metadataForm === FALSE) continue;

				$output .= '<li>';
     			if (($output .= self::generateAccordionFormList_links($metadataForm,$entry,($entry===TRUE)?TRUE:FALSE)) === FALSE) {
     				return FALSE;
     			}
				$output .= '</li>';
			}
			$output .= "</ul>";
		}

		return $output;
	}

	public static function generateFormSelectListForFormCreator($metadata = TRUE) {

		if ($metadata === TRUE) {
			if (($forms = forms::getMetadataForms()) === FALSE) {
				errorHandle::errorMsg("Error getting Metadata Forms");
				return FALSE;
			}
		}
		else if ($metadata === FALSE) {
			if (($forms = forms::getObjectForms()) === FALSE) {
				errorHandle::errorMsg("Error getting Object Forms");
				return FALSE;
			}
		}

		$formList = '<ul class="pickList">';
		foreach ($forms as $form) {

			if (!mfcsPerms::isViewer($form['ID'])) continue;

			$formList .= sprintf('<li><a href="index.php?id=%s" class="btn">%s</a></li>',
				htmlSanitize($form['ID']),
				htmlSanitize($form['title'])
				);

		}
		$formList .= "<ul>";

		return $formList;

	}

		/**
	 * Display a list, with optional links, of children for a given object
	 *
	 * @param string $objectID The ID of the object
	 * @return string|bool
	 * @author Scott Blake
	 **/
	public static function generateChildList($objectID,$link=TRUE) {
		if (!validate::integer($objectID)) {
			return FALSE;
		}

		$engine = EngineAPI::singleton();

		if (($children = objects::getChildren($objectID)) === FALSE) {
			return FALSE;
		}

		$output = '';
		foreach ($children as $child) {
			$form = forms::get($child['formID']);

			$output .= sprintf('<li>%s%s%s</li>',
				($link === TRUE) ? '<a href="?objectID='.$child['ID'].'">' : "",
				htmlSanitize($child['data'][$form['objectTitleField']]),
				($link === TRUE) ? '</a>' : ""
				);
		}

		return $output;
	}

	public static function availableUsersList($users) {

		if (!is_array($users)) {
			return FALSE;
		}

		$availableUsersList = '<option value="null">Select a User</option>';
		foreach($users as $row) {
			$name = array();
			if (!is_empty($row['lastname'])) {
				$name[] = htmlSanitize($row['lastname']);
			}
			if (!is_empty($row['firstname'])) {
				$name[] = htmlSanitize($row['firstname']);
			}

			$availableUsersList .= sprintf('<option value="%s">%s (%s)</option>',
				htmlSanitize($row['ID']),
				implode(", ",$name),
				htmlSanitize($row['username'])
				);
		}

		return $availableUsersList;
	}

	public static function metadataObjects($formID,$objectID) {

		// get all the object forms that have this metadata form linked to it
		$forms = forms::getFormsLinkedTo($formID);

		$data = array();
		foreach ($forms as $formID=>$field) {
			$objects = objects::getAllObjectsForForm($formID);
			$form    = forms::get($formID);

			foreach ($objects as $object) {
				if (strtolower($field['type']) == "select") {
					if ($object['data'][$field['name']] == $objectID) {
						$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']));
					}
				}
				else if (strtolower($field['type']) == "multiselect") {
					if (in_array($objectID,$object['data'][$field['name']])) {
						$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']));
					}
				}
			}

		}

		return self::createTable($data);

		return;

	}

}

?>