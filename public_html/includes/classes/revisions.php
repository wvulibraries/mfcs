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
}

?>