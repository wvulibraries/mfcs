<?php

class listGenerator {

	public static function createInitialSelectList() {
		return file_get_contents("includes/listTemplates/initialSelectList.php");
	}

	public static function createAllObjectList($start=0,$length=50,$orderBy=NULL,$objects=NULL) {

		$engine  = EngineAPI::singleton();
		$objects = isnull($objects)
			? objects::getObjects(0,NULL,FALSE)
			: $objects; //array_slice($objects, $start, $length);

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			$data[] = array(
				$object['ID'],
				$object['idno'],
				isset($object['data'][$form['objectTitleField']]) ? $object['data'][$form['objectTitleField']] : '',
				self::genLinkURLs("view",$object['ID']),
				self::genLinkURLs("edit",$object['ID']),
				// self::genLinkURLs("revisions",$object['ID'])
				);

		}

		return self::createTable($data);

	}

	//@TODO this needs to completely replace the above function, after search is overhauled
	public static function createAllObjectList_new($provided_objects=NULL) {

		$engine = mfcs::$engine;

		if (isnull($provided_objects)) {
			//@TODO this should go into the objects class
			$sql       = sprintf("SELECT COUNT(*) FROM `objects` WHERE `metadata`='0'");
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

			$object_count = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
			//end TODO

			$data_size                = $object_count["COUNT(*)"];
		}
		else {
			$data_size = count($provided_objects);
		}


		$userPaginationCount      = users::user('pagination',25); // how many items to display in the table
		$pagination               = new pagination($data_size);
		$pagination->itemsPerPage = $userPaginationCount;
		$pagination->currentPage  = isset($engine->cleanGet['MYSQL'][ $pagination->urlVar ])
									? $engine->cleanGet['MYSQL'][ $pagination->urlVar ]
									: 1;
		$startPos                 = $userPaginationCount*($pagination->currentPage-1);

		if (isnull($provided_objects)) {
			$objects                  = objects::getObjects($startPos,$userPaginationCount,FALSE);
		}
		else {
			$objects = array_slice($provided_objects, $startPos, $userPaginationCount);
		}


		$excludeFields = array("idno","file");

		$headers = array("Thumbnail","View","Edit","Creation Date","Modified Date","System IDNO","Form IDNO"); //"Revisions",

		$data = array();
		foreach ($objects as $object) {

			// Is this needed? Redundant?
			// $form = forms::get($object['formID']);

			$tmp = array(
				sprintf('<img src="%s" />',files::buildThumbnailURL($object['ID'])),
				self::genLinkURLs("view",$object['ID']),
				self::genLinkURLs("edit",$object['ID']),
				date("Y-m-d h:ia",$object['createTime']),
				date("Y-m-d h:ia",$object['modifiedTime']),
				$object['ID'],
				$object['idno']
				);


			$data[] = $tmp;

		}

		return self::createTable_new($data,$headers,$data_size,$pagination);

	}

	public static function createProjectSelectList() {
		$engine   = EngineAPI::singleton();
		$projects = projects::getProjects();

		$output = '<div class="list-group">';
		foreach ($projects as $project) {
			$output .= sprintf('<a href="%sdata/list/projects/?listType=project&amp;projectID=%s" class="list-group-item">%s</a></li>',
				mfcs::config("siteRoot"),
				$project['ID'],
				$project['projectName']
				);
		}
		$output .= '</div>';

		return $output;
	}

	public static function createFormDropDownList($compatible_with=NULL){
		$engine = EngineAPI::singleton();

		if (!isnull($compatible_with) && forms::get($compatible_with)) {
			$forms  = forms::compatibleForms($compatible_with);
		}
		else {
			$forms  = forms::getForms(TRUE,TRUE);
		}
		$output = '<div class="selectForm"> <select name="selectedFormID" id="selectedFormID"> <option value> Select A Form </option>';

		foreach ($forms as $form) {
			if($form ===  FALSE || !mfcsPerms::isViewer($form['ID'])) continue;
			$output .= sprintf('<option value="%s"> %s </option>',
				$form['ID'],
				forms::title($form['ID'])
			);
		}

		$output .= '</select></div>';

		return $output;
	}

	public static function createFormSelectList() {
		$engine = EngineAPI::singleton();
		$forms  = forms::getForms(TRUE,TRUE);
		$output = '';
		$count  = 0;

		$i = 0;
		foreach ($forms as $form) {
			if ($form === FALSE) continue;
			if (!mfcsPerms::isViewer($form['ID'])) continue;
			$i++;

			// create the panel
			$panel = sprintf('<div class="listForm panel panel-default span6">
									<div class="panel-heading">
										<span>
											<a href="list.php?listType=form&amp;formID=%s"> %s </a>
										</span>
										<i class="expandShelfList fa fa-plus-square-o"></i>
									</div>
									<div class="panel-body shelfList">
										<a href="list.php?listType=formShelfList&amp;formID=%s">Shelf List</a> <br/>
                                        <a href="list.php?listType=formThumbnailView&amp;formID=%s" style="width: 400px; text-align: right;">Thumbnail View</a>
									</div>
								</div>',
				$form['ID'],
				forms::title($form['ID']),
				$form['ID'],
				$form['ID']
			);

			//iterate for every 2 elements wrap in a row
			if($i == 1){
				$output .= "<div class='row'>";
				$output .= $panel;
			}
			if($i == 2){
				$output .= $panel;
				$output .= "</div>";
				$i = 0;
			}
		}

		return($output);

	}

	public static function createFormObjectList($formID, $thumbnail=FALSE) {

		$engine = mfcs::$engine;

		if (($form     = forms::get($formID)) === FALSE) {
			return FALSE;
		}

		$data_size                = forms::countInForm($formID);
		$userPaginationCount      = users::user('pagination',25); // how many items to display in the table
		$pagination               = new pagination($data_size);
		$pagination->itemsPerPage = $userPaginationCount;
		$pagination->currentPage  = isset($engine->cleanGet['MYSQL'][ $pagination->urlVar ])
									? $engine->cleanGet['MYSQL'][ $pagination->urlVar ]
									: 1;
		$startPos                 = $userPaginationCount*($pagination->currentPage-1);
		$objects                  = objects::getAllObjectsForForm($formID,"idno",TRUE,array($startPos,$userPaginationCount));


		$excludeFields = array("idno","file");

		$headers = array("View","Edit","Creation Date","Modified Date","System IDNO","Form IDNO"); //"Revisions",
		foreach($form['fields'] as $field) {
			if (in_array(strtolower($field['type']), $excludeFields)) continue;

			if (str2bool($field['displayTable'])) {
				$headers[] = $field['label'];
			}
		}

		if ($thumbnail) {
			array_unshift($headers, "Thumbnail");
		}

		$data = array();
		foreach ($objects as $object) {

			// Is this needed? Redundant?
			// $form = forms::get($object['formID']);

			$tmp = array(self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),date("Y-m-d h:ia",$object['createTime']),date("Y-m-d h:ia",$object['modifiedTime']),$object['ID'],$object['idno']); //,self::genLinkURLs("revisions",$object['ID'])
			foreach($form['fields'] as $field) {
				if (in_array(strtolower($field['type']), $excludeFields)) continue;

				if (str2bool($field['displayTable'])) {
					$tmp[] = $object['data'][$field['name']];
				}
			}

			if ($thumbnail) {
				array_unshift($tmp, sprintf('<img src="%s" />',files::buildThumbnailURL($object['ID'])));
			}

			$data[] = $tmp;

		}

		return self::createTable_new($data,$headers,$data_size,$pagination,$formID);

#		return self::createTable($data,$headers,TRUE,$formID);

	}

	private static function createTable_new($data,$headers = NULL,$array_size=TRUE,$pagination,$formID=NULL) {
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
			// $headers[] = "Revisions";
		}

		$table->headers($headers);

		$userPaginationCount = users::user('pagination',25);
		if($array_size > $userPaginationCount){

			$tableHTML  = $table->display($data);
			$tableHTML .= sprintf('<div class="span6"> %s </div>', $pagination->nav_bar());
			$tableHTML .= sprintf('<div class="span2"> <span class="paginationJumpLabel">Jump to Page:</span> %s</div>',
				$pagination->dropdown()
				);
			$tableHTML .= sprintf('<div class="span2"><span class="paginationJumpLabel">Records per page:</span> %s</div>',
				$pagination->recordsPerPageDropdown()
				);
			$tableHTML .= sprintf('<div class="span2"><form id="jumpToIDNOForm"><span class="paginationJumpLabel">Jump to IDNO:</span> <input type="text" name="jumpToIDNO" id="jumpToIDNO" data-formid="%s" value="" /></form></div>',
				(isnull($formID))?"":htmlSanitize($formID)
				);

			return $tableHTML;
		}else{
			return $table->display($data);
		}
	}

	public static function createFormShelfList($formID) {

		$engine        = mfcs::$engine;
		$objects       = objects::getAllObjectsForForm($formID,"idno",FALSE);
		if (($form          = forms::get($formID)) === FALSE) {
			return FALSE;
		}

		$excludeFields = array("idno","file");

		$headers = array("Form IDNO","Edit","View","Creation Date","Modified Date");  //,"Revisions"

		$data = array();
		foreach ($objects as $object) {

			$tmp    = array($object['idno'],self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("view",$object['ID']),date("Y-m-d h:ia",$object['createTime']),date("Y-m-d h:ia",$object['modifiedTime'])); //,self::genLinkURLs("revisions",$object['ID'])
			$data[] = $tmp;

		}


		return self::createTable($data,$headers,FALSE);

	}

	public static function createProjectObjectList($projectID) {

		$engine  = EngineAPI::singleton();
		$objects = objects::getAllObjectsForProject($projectID);

		$data = array();
		foreach ($objects as $object) {

			$form = forms::get($object['formID']);

			//,self::genLinkURLs("revisions",$object['ID'])
			$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']));

		}

		return self::createTable($data);

	}

	private static function createTable($data,$headers = NULL,$pagination=TRUE,$formID=NULL) {
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
			// $headers[] = "Revisions";
		}

		$table->headers($headers);

		$userPaginationCount = users::user('pagination',25);
		if($pagination && sizeof($data) > $userPaginationCount){
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
			$tableHTML .= sprintf('<p><span class="paginationJumpLabel">Jump to Page:</span> %s</p>',
				$pagination->dropdown()
				);
			$tableHTML .= sprintf('<p><span class="paginationJumpLabel">Records per page:</span> %s</p>',
				$pagination->recordsPerPageDropdown()
				);
			$tableHTML .= sprintf('<p><form id="jumpToIDNOForm"><span class="paginationJumpLabel">Jump to IDNO:</span> <input type="text" name="jumpToIDNO" id="jumpToIDNO" data-formid="%s" value="" /></form></p>',
				(isnull($formID))?"":htmlSanitize($formID)
				);

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
					? sprintf('<a href="%sdataEntry/revisions/index.php?objectID=%s">View</a>', localvars::get("siteRoot"), $objectID)
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
						forms::title($form['ID'])
						);

					continue 2;
				}
			}

			$formList .= sprintf('<li><a href="object.php?formID=%s%s" class="btn">%s</a></li>',
				htmlSanitize($form['ID']),
				(!isnull($objectID))?"&amp;parentID=".$objectID:"", // parent information
				forms::title($form['ID'])
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
			if($metadata === FALSE){
				return sprintf('<a href="index.php?id=%s" class="%s">%s %s</a>',
					htmlSanitize($form['ID']),
					(is_empty(forms::description($form['ID'])) ? '' : 'hasDescription'),
					forms::title($form['ID']),
					(is_empty(forms::description($form['ID'])) ? '' : '<p>'.forms::description($form['ID']).'</p>')
				);
			} else {
				return sprintf('<a href="index.php?id=%s">%s</a>',
					htmlSanitize($form['ID']),
					forms::title($form['ID'])
				);
			}
		}
		else {
			return sprintf('<a href="%sdataEntry/%s.php?formID=%s">%s</a>',
				localvars::get("siteRoot"),
				($metadata === TRUE)?"metadata":"object",
				htmlSanitize($form['ID']),
				forms::title($form['ID'])
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

			if(!mfcsPerms::isEditor($form['ID'])) continue;

			if (($metedataForms = forms::getObjectFormMetaForms($form['ID'])) === FALSE) {
				errorHandle::errorMsg("Error getting Metadata Forms");
				return FALSE;
			}

			$output .= '<div class="accordion-group panel panel-default">';
			$output .= '<div class="accordion-heading panel-heading" style="padding: 10px;">';
			$output .= '<div>';
			$output .= self::generateAccordionFormList_links($form,$entry);

			if(sizeof(forms::getObjectFormMetaForms($form['ID']))){
				$output .= sprintf('<a class="pull-right metadataListAccordionToggle" data-toggle="collapse" data-parent="#formListAccordion" href="#collapse%s">
					<i class="fa fa-plus-square-o"></i> Metadata Forms</a>',
					++$count);
			}

			// shelf list
			$output .= sprintf('<a class="icon-item" href="%sdataView/list.php?listType=formShelfList&formID=%s"><i class="fa fa-list"></i></a>',mfcs::config("siteRoot"),$form['ID']);

			// thumbnail list
			$output .= sprintf('<a class="icon-item" href="%sdataView/list.php?listType=formThumbnailView&formID=%s"><i class="fa fa-picture-o"></i></a>',mfcs::config("siteRoot"),$form['ID']);


			// paginated list
			$output .= sprintf('<a class="icon-item" href="%sdataView/list.php?listType=form&formID=%s"><i class="fa fa-list-ol"></i></a>',mfcs::config("siteRoot"),$form['ID']);


			$output .= '</div>';
			$output .= "</div>"; // heading
			$output .= sprintf('<div id="collapse%s" class="accordion-body collapse">', $count);
			$output .= '<div class="accordion-inner panel-body">';

			$output .= '<ul>';
			foreach ($metedataForms as $I=>$metadataForm) {

				if (isset($metaForms[$I])) {
					unset($metaForms[$I]);
				}

				$output .= '<li>';
				// these are metadata forms the end should be true.
				if (($output .= self::generateAccordionFormList_links($metadataForm,$entry,TRUE)) === FALSE) {
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
			$output .= '<div class="unassignedForms"> <h1>Unassigned Metadata Forms</h1>';
			$output .= "<ul>";
			foreach ($metaForms as $metadataForm) {

				if ($metadataForm === FALSE) continue;
				if(!mfcsPerms::isEditor($metadataForm['ID'])) continue;

				$output .= '<li>';
				if (($output .= self::generateAccordionFormList_links($metadataForm,$entry,($entry===TRUE)?TRUE:FALSE)) === FALSE) {
					return FALSE;
				}
				$output .= '</li>';
			}
			$output .= "</ul> </div>";
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
				forms::title($form['ID'])
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
					if (is_array($object['data'][$field['name']]) && in_array($objectID,$object['data'][$field['name']])) {
						$data[] = array($object['ID'],$object['idno'],$object['data'][$form['objectTitleField']],self::genLinkURLs("view",$object['ID']),self::genLinkURLs("edit",$object['ID']),self::genLinkURLs("revisions",$object['ID']));
					}
				}
			}

		}
		return self::createTable($data);
		return;
	}

	public static function getMetadataStandards($id = null){
		$engine = EngineAPI::singleton();

		if (!isnull($id)){
			$sql = sprintf("SELECT * FROM `metadataStandards` WHERE `formID`=%s",
				$engine->openDB->escape($id)
			);
		}
		else {
			$sql = "SELECT * FROM `metadataStandards`";
		}

		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects for form: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[$row['typeID']] = $row['type'];
		}

		return $objects;
	}


	public static function moveObjectListResults($objects, $thumbs = false) {

		$engine = mfcs::$engine;

		// if no results display no results
		if (isnull($objects)) {
			return "<div class='no-results'> <p> There were no results generated to move to the form, please refine your search and try again. </p> </div>";
		}


		$thumbHeaders   = array("","Creation Date","Modified Date","System IDNO","Form IDNO", "Thumbnail");
		$noThumbHeaders = array("","Creation Date","Modified Date","System IDNO","Form IDNO");
		$headers        = ($thumbs == true ? $thumbHeaders : $noThumbHeaders);

		$data    = array();
		foreach ($objects as $object) {
			$tmp = array(
				sprintf('<input type="checkbox" class="moveObjectCheckbox" value="%s" />', $object['ID']),
				date("Y-m-d h:ia",$object['createTime']),
				date("Y-m-d h:ia",$object['modifiedTime']),
				$object['ID'],
				$object['idno'],
			);

			if($thumbs == true){
				array_push($tmp, sprintf('<img src="%s" />', files::buildThumbnailURL($object['ID'])));
			}

			$data[] = $tmp;
		}

		return sprintf('<h2> Select Objects to Move </h2> <div id="objectsContainer" class="objectsTable">%s</div>', self::createTable_new($data,$headers,$data_size,false));
	}
}

?>
