<?php

class revisions {

	public static function create() {
		return new revisionControlSystem('objects','revisions','ID','modifiedTime');
	}

	public static function generateFieldDisplay($object,$fields) {

		$output = '';
		$data   = is_array($object['data']) ? $object['data'] : decodeFields($object['data']);
		$form   = forms::get($object['formID']);

		$select_field_info = array();
		foreach ($form['fields'] as $field) {
			if ($field['type'] != "select" && $field['type'] != "multiselect") continue;
			if (isset($field['choicesType']) && strtolower($field['choicesType']) != "form") continue;
			
			$select_field_info[$field['name']] = $field['choicesField'];
			
		}

		foreach ($fields as $field) {

			$type  = $field['type'];
			$name  = $field['name'];
			$label = $field['label'];

			switch($type){
				case 'idno':
				$output .= sprintf('<div class="objectField">
										<div class="fieldName"> %s </div>
										<div class="fieldValue"> %s </div>
									</div>',
					$label,
					$object[$name]
					);
				break;


				case 'file':

					// if the archive isn't set, we assume no files and break out.
					// otherwise the revisions page won't load properly.
					if (!isset($data[$name]['files']['archive'])) break;

					$fileLIs = array();

					foreach($data[$name]['files']['archive'] as $index=>$file){
						$fileLIs[] = sprintf('<li><a href="%sincludes/fileViewer.php?type=archive&objectID=%s&field=%s&fileID=%s" target="_blank">%s</a></li>', 
							mfcs::config("siteRoot"),
							$object['ID'],
							$name,
							$index,
							$file['name']
							);
					}

					$output .= sprintf('<div class="objectField">
											<div class="fieldName">%s</div>
											<div class="fieldValue">
												%s file %s
												<a href="javascript:;" class="toggleFileList">click to list</a>
												<ul style="display:none;">%s</ul>
											</div>
										</div>',
						$label,
						sizeof($fileLIs),
						sizeof($fileLIs)>1 ? 's' : '',
						implode('',$fileLIs)
						);
				break;

				case 'multiselect':

				if (is_array($data[$name])) {
					$multiselect_list = "<ul>";
					foreach ($data[$name] as $metadataID) {
						$multiselect_list .= sprintf("<li>%s</li>",(array_key_exists($name, $select_field_info))? get_select_by($metadataID,$select_field_info[$name]):"Missing Field");
					}
					$multiselect_list .= "</ul>";
				}
				else {
					$multiselect_list = "";
				}

				$output .= sprintf('<div class="objectField">
											<div class="fieldName">%s</div>
											<div class="fieldValue">%s</div>
											<!--<aside><button class="btn btn-mini" type="button">Show Diff</button></aside>-->
										</div>',
						$label,
						$multiselect_list
						);

				break;

				case 'select':

				$display_value = (array_key_exists($name, $select_field_info))? get_select_by($data[$name],$select_field_info[$name]):$data[$name];

				$output .= sprintf('<div class="objectField">
											<div class="fieldName">%s</div>
											<div class="fieldValue">%s</div>
											<!--<aside><button class="btn btn-mini" type="button">Show Diff</button></aside>-->
										</div>',
						$label,
						$display_value
						);

				break;

				default:
					case 'text':
					$output .= sprintf('<div class="objectField">
											<div class="fieldName">%s</div>
											<div class="fieldValue">%s</div>
											<!--<aside><button class="btn btn-mini" type="button">Show Diff</button></aside>-->
										</div>',
						$label,
						$data[$name]
						);
				break;
			}
		}

		return $output;
	}

	// object = array | integer
	// $type = created | modified
	public static function history_build($objectID,$type) {

		if (is_array($objectID)) {
			$object = $objectID;
		}
		else if (($object = objects::get($objectID)) === FALSE) {
			return FALSE;
		}

		$type_by = (strtolower($type) == "created")?"createdBy"  : "modifiedBy";
		$type_on = (strtolower($type) == "created")?"createTime" : "modifiedTime";

		if (!is_empty($object[$type_by])) {
			$user = users::get($object[$type_by]);
			$user = $user['username'];
		}
		else {
			$user = "Unavailable";
		}

		$date = date('D, d M Y H:i',$object[$type_on]);

		return array($user,$date);

	}

	public static function history_created($objectID) {

		$created = self::history_build($objectID,"created");

		localvars::add("createdOnDate",     $created[1]);
		localvars::add("createdByUsername", $created[0]);

		return $created;

	}

	public static function history_last_modified($objectID) {

		$modified = self::history_build($objectID,"modified");

		localvars::add("modifiedOnDate",     $modified[1]);
		localvars::add("modifiedByUsername", $modified[0]);

		return $modified;

	}

	public static function history_revision_history($objectID) {

		$history = array();

		$revisions = revisions::create();
		foreach($revisions->getSecondaryIDs($objectID, 'ASC') as $secondaryID){
			
			$revision                 = $revisions->getMetadataForID($revisions->getRevisionID($objectID,$secondaryID));
			$revision['modifiedTime'] = $secondaryID;

			$history[] = self::history_build($revision,"modified");
		}

		return $history;

	} 
}

?>