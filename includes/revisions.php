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
				$output .= sprintf('<section class="objectField"><header>%s</header>%s</section>',
					$label,
					$object[$name]
					);
				break;


				case 'file':
					$fileLIs = array();
					foreach($data[$name]['files']['archive'] as $file){
						$fileLIs[] = sprintf('%s', $file['name']);
					}

					$output .= sprintf('<section class="objectField"><header>%s</header>%s file%s <a href="javascript:;" class="toggleFileList">click to list</a><ul style="display:none;">%s</ul></section>',
						$label,
						sizeof($fileLIs),
						sizeof($fileLIs)>1 ? 's' : '',
						implode('',$fileLIs)
						);
				break;

				default:
					case 'text':
					$output .= sprintf('<section class="objectField"><header>%s</header>%s<!--<aside><button class="btn btn-mini" type="button">Show Diff</button></aside>--></section>',
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