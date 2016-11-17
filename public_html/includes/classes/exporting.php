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

  /**
   * Creates the export directories.
	 *
	 * Additionally, creates the control file name.
	 *
	 * @param string $project_name the project name
	 * @param string $export_path the export path.
	 * @param int $time_stamp unix time stamp
	 * @param array $export_directories and array of directories that need to be created.
	 * 							these are where digital files and metadata will be exported too.
	 *
	 * @return mixed false on failure, otherwise array. Key is the directory name, value is the location.
   */
	public static function createExportDirectories($project_name, $export_path, $time_stamp, $export_directories) {

		$directories = array();

		$directories["base_dir"] = sprintf("%s/%s",$export_path,$project_name);
		$directories["export_base_dir"] = sprintf("%s/export",$directories["base_dir"]);
		$directories["control_dir"] = sprintf("%s/control/mfcs",$directories["base_dir"]);
		$directories["export_control_file"] = sprintf("%s/%s.yaml",$directories["control_dir"],$time_stamp);

		$directories["filesExportBaseDir"] = sprintf("%s/%s",$directories["export_base_dir"],$time_stamp);
		if (!mkdir($directories["filesExportBaseDir"])) {
			errorHandle::newError(__METHOD__."() - export base: ".$directories["filesExportBaseDir"], errorHandle::DEBUG);
			return false;
		}

		foreach ($export_directories as $export_directory) {
			if (array_key_exists($export_directory, $directories)) {
				errorHandle::newError(__METHOD__."() - duplicate directory: ".$export_directory, errorHandle::DEBUG);
				return false;
			}

			$directories[$export_directory] = sprintf("%s/%s",$directories["filesExportBaseDir"],$export_directory);
			if (!mkdir($directories[$export_directory])) {
				errorHandle::newError(__METHOD__."() - Error creating: ".$directories[$export_directory], errorHandle::DEBUG);
				return false;
			}
		}

		return $directories;
	}

 /**
  * Generates the control file.
	*
	* The control file is used by whatever automation system is in place for
	* handling automation of the exports into another system. The automation
	* system is beyond the scope of MFCS, and is up to that system or the developers
	* maintaining that system to make use of the file.
	*
	* This function may need to be replaced with a method that handles the replacements
	* via a mapping in the future, if there is an export system that requires more
	* information than is available here. The information here is based on WVU's
	* use when exporting to DLXS and/or Hydra systems.
	*
	* Alternatively, maintainers of systems that require additional information
	* could simply modify this function as needed. Pull requests for supporting
	* additional needs are welcome.
	*
	* @param string $project_name the project_name
	* @param int $timestamp unix time stamp
	* @param string $export_type See comments in export_control_file.yaml for valid values
	* @param int $digital_items_count the count of how many digital items are present
	* @param int $record_count the count of how many records are exported
	*
	* @return string template file with variable replacements.
  */
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

	/**
	 * Writes the control file.
	 *
	 * @param string $filename the filename for the control file
	 * @param string $project_name the project_name
	 * @param int $timestamp unix time stamp
	 * @param string $export_type See comments in export_control_file.yaml for valid values
	 * @param int $digital_items_count the count of how many digital items are present
	 * @param int $record_count the count of how many records are exported
	 *
	 * @return boolean true on success, false otherwise
	 */
	public static function writeControlFile($filename, $project_name, $timestamp, $export_type, $digital_items_count, $record_count) {
		if (!$file = fopen($filename,"w")) {
			errorHandle::newError(__METHOD__."() - Error creating file", errorHandle::DEBUG);
			return false;
		}
		fwrite($file, exporting::generateControlFile($project_name, $timestamp, $export_type, $digital_items_count, $record_count));
		fclose($file);

		return true;
	}

	/**
	 * Sets the date of the last export.
	 *
	 * This is not an automatic function. it is up to the developer to determine
	 * if it is needed/important to set the last export date, and then call the
	 * method from the export script as needed. For additional information on the
	 * use, and why/when this is needed see getExportDate().
	 *
	 * @param integer $form_id the ID of the form we are setting a date for
	 * @param integer $timestamp Unix time stamp
	 *
	 * @return boolean true on success, false otherwise.
	 */
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

	/**
	 * Get the last export date for a form.
	 *
	 * When exporting, it is possible to set the export date using setExportDate().
	 * It is up to the export script maintainer to decide if setting the export date is
	 * important and/or needed for a particular form. By setting the export date,
	 * it is possible to determine when the last export was run. This is useful on
	 * forms with large numbers of digital objects, so that you only have to export
	 * digital objects since the last export date. You can then compare the last export
	 * date to the last modified date of the object. if the Object's modified date
	 * is greater than the last export date it must be exported, otherwise it can
	 * be ignored. If automated exporting is not being utilized*, it is possible
	 * for the exporting to get out of sync. That is, if someone clicks on the export
	 * but then dooes not do an import into which ever respoitory is expecting an input,
	 * data can be missed. In this case MFCS provides utility to remove previous eport dates.
	 * Information on doing this is provided int he wiki here:
	 * https://github.com/wvulibraries/mfcs/wiki/Export-Date-Management
	 *
	 * *** Note: Automated exporting is out of the scope of MFCS and is left up to
	 * individual maintainers to develop a export strategy that works for them.
	 * For an example of how WVU handles this, view the hydra-import-scripts respository.
	 * The exact implimentation will be dependant on the system that is being exported too,
	 * as well as the form being exported from.
	 *
	 * Additionally this is useful for doing delta updates if your repository
	 * supports that feature. That is, only exporting new and/or updated metadata
	 * records.
	 *
	 * @param integer $form_id the ID of the form to retrieve the last export date
	 *
	 * @return integer the date, in unix time, when the form was last updated.
	 */
	public static function getExportDate($form_id) {
		$sql       = sprintf("SELECT MAX(`date`) FROM exports WHERE `formID`='%s'",
		mfcs::$engine->openDB->escape($form_id));
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return false;
		}

		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		return (isnull($row['MAX(`date`)']))?0:$row['MAX(`date`)'];
	}

}

?>
