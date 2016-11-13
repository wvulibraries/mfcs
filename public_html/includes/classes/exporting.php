<?php

class exporting {

	private $exportDirs = array();

	private $skipDirs   = array(".","..","index.php","lost+found",".DS_Store");

	public function __construct($dir) {

		if (!is_readable($dir)) {
			return FALSE;
		}

		$dir = opendir($dir); // open the cwd..also do an err check.
		while(false != ($file = readdir($dir))) {
			if(!in_array($file,$this->skipDirs)) {
				$this->exportDirs[] = array('file' => $file, 'name' => str_replace("_"," ",basename($file,".php")));
			}
		}
		closedir($dir);

		sort($this->exportDirs);

	}

	public function showExportListing() {

		$output = "<ul>";
		foreach ($this->exportDirs as $dir) {
			$output .= sprintf('<li><a href="'.$dir['file'].'">'.$dir['name'].'</a></li>');
		}
		$output .= "</ul>";

		return $output;

	}

	public static function determine_add($field,$type) {

		if (strtolower($type) == "label") {
			return $field['label'];
		}
		else if (strtolower($type) == "name") {
			return $field['name'];
		}
		else if (strtolower($type) == "class") {
			return $field['class'];
		}
		else if (strtolower($type) == "id") {
			return $field['id'];
		}

		return "";

	}

	public static function determine_metadataStandard($field,$target_schema) {

		if (!isset($field['metadataStandard'])) return array();

		$items = array_map("trim",explode(":",$field['metadataStandard']));

		if (is_empty($items[0]) || is_empty($items[1]) || $items[0] != $target_schema) {
			return false;
		}

		$options = explode("%%",$items[1]);

		$return = array();
		$return['predicate'] = $options[0];

		if (isset($options[1]) && !is_empty($options[1])) {
			$return['options'] = array();

			$options_string = explode("|",$options[1]);
			foreach ($options_string as $option_pair) {
				$options = array_map("trim",explode("=",$option_pair));

				switch($options[0]) {

					case "delimiter":
					$options[1] = preg_replace("/'/","",$options[1]);
					break;
					case "prepend":
					$options[1] = self::determine_add($field,$options[1]);
					break;
					case "append":
					$options[1] = self::determine_add($field,$options[1]);
					break;
					case "combine":
					$options[1] = array_map("trim",explode(",",$options[1]));
					break;
				}

				$return['options'][$options[0]] = $options[1];
			}
		}

		return $return;

	}

	public static function get_data_value($object,$form_fields,$dc_fields,$field_name) {

		if ((isnull($object['data'][$field_name]) || is_empty($object['data'][$field_name])) && ($form_fields[$field_name]['readonly'] == "true" || $form_fields[$field_name]['disabled'] == "true") && isset($form_fields[$field_name]['value']) && !is_empty($form_fields[$field_name]['value'])) {
			$data = $form_fields[$field_name]['value'];
		}
		else {
			$data = $object['data'][$field_name];
		}

		if (isset($dc_fields[$field_name]["options"]["prepend"]) && !is_empty($dc_fields[$field_name]["options"]["prepend"])) {
			$data = sprintf("%s %s",$dc_fields[$field_name]["options"]["prepend"],$data);
		}
		if (isset($dc_fields[$field_name]["options"]["append"]) && !is_empty($dc_fields[$field_name]["options"]["append"])) {
			$data = sprintf("%s %s",$dc_fields[$field_name]["options"]["append"],$data);
		}

		if (class_exists("cleanup",false)) $data = cleanup::clean($data);

		return $data;

	}

	public static function createExportDirectories($project_name, $export_path, $time_stamp, $export_directories) {

		$directories = array();

		$directories["base_dir"] = sprintf("%s/%s",$project_name,$export_path);
		$directories["export_base_dir"] = sprintf("%s/export",$directories["base_dir"]);
		$directories["control_dir"] = sprintf("%s/control/mfcs",$directories["base_dir"]);
		$directories["export_control_file"] = sprintf("%s/%s.yaml",$directories["control_dir"],$time_stamp);

		$directories["filesExportBaseDir"] = sprintf("%s/%s",$directories["export_base_dir"],$time_stamp);
		if (!mkdir($directories["filesExportBaseDir"])) {
			errorHandle::newError(__METHOD__."() - export base.", errorHandle::DEBUG);
			return false;
		}

		foreach ($export_directories as $export_directory) {
				if (!mkdir(sprintf("%s/%s",$directories["filesExportBaseDir"],$export_directory))) {
					errorHandle::newError(__METHOD__."() - Error creating ".$export_directory, errorHandle::DEBUG);
					return false;
				}
		}

		return $directories;
	}

	public static function generateControlFile($project_name, $timestamp, $export_type, $digital_items_count, $record_count) {
		if (($template = file_get_contents(mfcs::config("exportControlTemplate"))) === FALSE) {
			print "Error opening Export Control Template.";
			exit;
		}

		$template = preg_replace("/{{ project_name }}/", $project_name, $template);
		$template = preg_replace("/{{ timestamp }}/", $timestamp, $template);
		$template = preg_replace("/{{ export_type }}/", $export_type, $template);
		$template = preg_replace("/{{ digital_items_count }}/", $digital_items_count, $template);
		$template = preg_replace("/{{ record_count }}/",$record_count, $template);

		return $template;
	}

	public static function writeControlFile($filename,$project_name, $timestamp, $export_type, $digital_items_count, $record_count) {
		if (!$file = fopen($filename,"w")) {
			errorHandle::newError(__METHOD__."() - Error creating file", errorHandle::DEBUG);
			return false;
		}
		fwrite($file, exporting::generateControlFile($project_name, $timestamp, $export_type, $digital_items_count, $record_count));
		fclose($file);

		return true;
	}

	public static function setExportDate($form_id,$timestamp) {
		$sql       = sprintf("INSERT INTO `exports` (`formID`,`date`) VALUES('%s','%s')",
			mfcs::$engine->openDB->escape($form_id),
			mfcs::$engine->openDB->escape($timestamp)
		);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return false;
		}
		return true;
	}

	public static function getExportDate($form_id) {
		$sql       = sprintf("SELECT MAX(`date`) FROM exports WHERE `formID`='%s'",
		mfcs::$engine->openDB->escape($form_id));
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return false;
		}

		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		return (isnull($row['MAX(`date`)']))?0:$row['MAX(`date`)'];
	}

}

?>
