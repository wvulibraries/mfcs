<?php

class revisions {

	public static function create() {
		return new revisionControlSystem('objects','revisions','ID','modifiedTime');
	}

	public static function generateFieldDisplay($object,$fields) {

		$output = '';
		$data   = is_array($object['data']) ? $object['data'] : decodeFields($object['data']);

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

					foreach($data[$name]['files']['archive'] as $file){
						$fileLIs[] = sprintf('%s', $file['name']);
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
}

?>