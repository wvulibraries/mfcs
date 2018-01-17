<?php

/**
 * Main MFCS object
 * @author David Gersting
 */
class files {

	private static $insertFieldNames = array();
	private static $fixity_files     = array();

	private static function printImage($filename,$mimeType) {
		$tmpName = tempnam(mfcs::config('mfcstmp'), 'mfcs').".jpeg";
		shell_exec(sprintf('convert %s -quality 50 %s 2>&1',
			escapeshellarg($filename),
			escapeshellarg($tmpName)));
		printf('<html><img src="data:image/jpeg;base64,%s" /></html>',
			base64_encode(file_get_contents($tmpName)));
		unlink($tmpName);

		return TRUE;
	}

	public static function errorOldProcessingJobs() {

		$oldDate = time() - 604800;

		$sql       = sprintf("UPDATE `objectProcessing` SET `state`='3' WHERE `timestamp`<'%s'",
			$oldDate
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	public static function deleteOldProcessingJobs() {
		$sql       = sprintf("DELETE FROM `objectProcessing` WHERE `state`='0'");
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;
	}

	public static function addProcessingField($fieldname) {

		self::$insertFieldNames[] = $fieldname;

		return TRUE;
	}

	/**
	 * Remove a processing field
	 * @param  string $fieldname the fieldnaem to remove
	 * @return bool            TRUE on success, FALSE if not found
	 */
	public static function removeProcessingField($fieldname) {
		for($I=0;$I<count(self::$insertFieldNames); $I++) {
			if (self::$insertFieldNames[$I] == $fieldname) {
				unset(self::$insertFieldNames[$I]);
				return TRUE;
			}
		}

		return NULL;
	}

	public static function resetProcessingFields() {
		self::$insertFieldNames = array();
		return TRUE;
	}

	public static function insertIntoProcessingTable($objID, $state=1) {

		if (!validate::integer($state)) {
			return FALSE;
		}

		// Insert into filesChecks table (fixity)
		foreach (self::$fixity_files as $location) {
			if (!self::fixityInsert($location,$objID)) {
				errorHandle::newError(__METHOD__."() - couldn't create fixity entry.", errorHandle::DEBUG);
				// @todo : we need a script that periodically checks to make sure all files are in
				// filesChecks table ... I don't think we want to return FALSE here on failure because some files
				// have already been moved ...
			}
		}

		// @TODO returns true if no insert fields are set. should imply that there are
		// no files to process. could need a debug though ... ???
		if (is_empty(self::$insertFieldNames)) {
			return TRUE;
			errorHandle::newError(__METHOD__."() - no fields set.", errorHandle::DEBUG);
		}

		// start transactions
		if (mfcs::$engine->openDB->transBegin("objects") !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		foreach (self::$insertFieldNames as $fieldname) {
			$sql       = sprintf("INSERT INTO `objectProcessing` (`objectID`,`fieldName`,`state`, `timestamp`) VALUES('%s','%s','%s','%s')",
				mfcs::$engine->openDB->escape($objID),
				mfcs::$engine->openDB->escape($fieldname),
				mfcs::$engine->openDB->escape($state),
				time()
				);
			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (!$sqlResult['result']) {

				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}
		}

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		// reset the processing fields
		self::resetProcessingFields();

		return TRUE;

	}

	public static function insert_into_processing_table($objectID,$fieldname,$state=1) {

		$sql = sprintf("INSERT INTO `objectProcessing` (`objectID`,`fieldName`,`state`, `timestamp`) VALUES('%s','%s','%s','%s')",
				mfcs::$engine->openDB->escape($objectID),
				mfcs::$engine->openDB->escape($fieldname),
				mfcs::$engine->openDB->escape($state),
				time()
				);
			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

			return TRUE;

	}

	private static function setProcessingState($rowID,$state) {

		$sql       = sprintf("UPDATE `objectProcessing` SET `state`='%s' WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($state),
			mfcs::$engine->openDB->escape($rowID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	// if ObjectID is null, processes everything with a $state of 1
	// if ObjectID is an integer, processes that objectID
	//
	// if $state is modified, processes everything with that state. valid states are 1 and 3 (2 are currently being processed. 0's are done and ready for deleting)
	//
	// if $returnArray is TRUE, only 1 fieldName will be processed. Returns a complete 'files' array
	public static function process($objectID=NULL,$fieldname=NULL,$state=1,$returnArray=FALSE) {

		if ((string)$state != "1" && (string)$state != "3") {
			errorHandle::newError(__METHOD__."() - Invalid state provided: ".$state, errorHandle::DEBUG);
			return FALSE;
		}

		// was a valid objectID provided
		if (!isnull($objectID) && validate::integer($objectID)) {
			$objectWhere = sprintf(" AND `objectID`='%s'",
				mfcs::$engine->openDB->escape($objectID)
				);
		}
		else if (!isnull($objectID) && !validate::integer($objectID)) {
			errorHandle::newError(__METHOD__."() - Invalid Object ID: ".$objectID, errorHandle::DEBUG);
			return FALSE;
		}
		else {
			$objectWhere = "";
		}

		// was a valid fieldname provided
		if (!isnull($fieldname) && is_string($fieldname)) {
			$fieldnameWhere = sprintf(" AND `fieldName`='%s'",
				mfcs::$engine->openDB->escape($fieldname)
				);
		}
		else {
			$fieldnameWhere = "";
		}

		$sql       = sprintf("SELECT * FROM `objectProcessing` WHERE `objectProcessing`.`state`='%s'%s%s",
			mfcs::$engine->openDB->escape($state),
			$objectWhere,
			$fieldnameWhere
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		// I'm not sure about database transactions here
		// We are modifying the file system (exports). transaction rollbacks would
		// have to be done on the file system as well.

		while ($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

			// set the state of the row to 2
			self::setProcessingState($row['ID'],2);

			// get the object, and ignore the cache since we are updating in a loop
			$object   = objects::get($row['objectID'],TRUE);
			$files    = $object['data'][$row['fieldName']];
			$assetsID = $files['uuid'];

			$fieldOptions = forms::getField($object['formID'],$row['fieldName']);

			// do we need to do any processing?
			// @TODO, i don't like how these are hard coded

			// base options
			$convert      = isset($fieldOptions['convert']) ? str2bool($fieldOptions['convert']) : false;
			$convertVideo = isset($fieldOptions['convertVideo']) ? str2bool($fieldOptions['convertVideo']) : false;
			$convertAudio = isset($fieldOptions['convertAudio']) ? str2bool($fieldOptions['convertAudio']) : false;

			// other options
			$combine      = isset($fieldOptions['combine']) ? str2bool($fieldOptions['combine']) : false;
			$ocr          = isset($fieldOptions['ocr']) ? str2bool($fieldOptions['ocr']) : false;
			$thumbnail    = isset($fieldOptions['thumbnail']) ? str2bool($fieldOptions['thumbnail']) : false;


			// if no processing break the while loop
			// if any of them are true then we
			if (!$combine && !$convert && !$ocr && !$thumbnail && !$convertVideo && !$convertAudio && !$convertVideo) {
				self::setProcessingState($row['ID'],0);
				continue;
			}

			$processedFiles = self::processObjectFiles($assetsID,$fieldOptions);

			if(!$processedFiles){
				$setRowValue = 3;
				self::setProcessingState($row['ID'],$setRowValue);
				return FALSE;
			}

			$files['files']                    = array_merge($files['files'],$processedFiles);
			$object['data'][$row['fieldName']] = $files;

			$return = objects::update($objectID,$object['formID'],$object['data'],$object['metadata'],$object['parentID']);

			// @TODO this return value isn't descriptive enough. It can fail and still
			// return a valid array. we likely need to return an array with an error
			// code as well as the array to save to the data


			if (!$return) {
				$setRowValue = 3;
			}
			else {
				$setRowValue = 0;
			}

			// Processing is done, set state to 0
			self::setProcessingState($row['ID'],$setRowValue);

			if ($returnArray === TRUE) {
				return $object['data'][$row['fieldName']];
			}

		}

		return TRUE;

	}

	public static function generateFilePreview($filename,$mimeType=NULL){
		// Determine the object's MIME type
		if(!isset($mimeType)){
			if(isPHP('5.3')){
				$fi = new finfo(FILEINFO_MIME_TYPE);
				$mimeType = $fi->file($filename);
			}else{
				$fi = new finfo(FILEINFO_MIME);
				list($mimeType,$mimeEncoding) = explode(';', $fi->file($filename));
			}
		}

		// Get the file's source
		if(!isset($fileData)) $fileData = file_get_contents($filename);

		// List of Video Mime types
		$videoMimeTypes = array( 'application/mp4', 'application/ogg', 'video/3gpp', 'video/3gpp2', 'video/flv', 'video/h264', 'video/mp4', 'video/mpeg', 'video/mpeg-2', 'video/mpeg4', 'video/ogg', 'video/ogm', 'video/quicktime', 'video/avi');

		$audioMimeTypes  = array('audio/acc', 'audio/mp4', 'audio/mp3', 'audio/mp2', 'audio/mpeg', 'audio/oog', 'audio/midi', 'audio/wav', 'audio/x-ms-wma','audio/webm');


		$imageMimeTypes = array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon');

		$pdfMimeTypes   = array('application/pdf');

		// Figure out what to do with the data
		switch(trim(strtolower($mimeType))){
			case 'image/tiff':
				self::printImage($filename,$mimeType);
			break;

			case in_array($mimeType, $imageMimeTypes):
			case in_array($mimeType, $pdfMimeTypes):
			case in_array($mimeType, $audioMimeTypes):
			case in_array($mimeType, $videoMimeTypes):
				ini_set('memory_limit',-1);
				header("Content-type: $mimeType");
				die(file_get_contents($filename));
			break;

			default:
				echo '[No preview available - Unknown file type please download the file]';
				break;
		}
	}

	/**
	 * Takes a file and returns its mime type
	 *
	 * @author Scott Blake
	 * @param string $filename
	 * @return string
	 **/
	public static function getMimeType($filepath) {
		if (isPHP('5.3')) {
			$fi = new finfo(FILEINFO_MIME_TYPE);
			return $fi->file($filepath);
		}

		$fi = new finfo(FILEINFO_MIME);
		list($mimeType,$mimeEncoding) = explode(';', $fi->file($filepath));
		return $mimeType;
	}

	// Chris Jester-Young's combined with php.net's and a precision argument
	public function formatBytes($size, $precision = 1){
    	$base = log($size, 1024);
   	 	$suffixes = array('', 'KB', 'MB', 'GB', 'TB');
    	return round(pow(1024, $base - floor($base)), $precision) . " " . $suffixes[floor($base)];
	}

	public static function buildFilesPreview($objectID,$fieldName=NULL){

		if (objects::validID(TRUE,$objectID) === FALSE) {
			return FALSE;
		}

		if (($object = objects::get($objectID,TRUE)) === FALSE) {
			return FALSE;
		}

		$output = '';

		if (isset($fieldName)) {
			$field  = forms::getField($object['formID'],$fieldName);
			$fields = array($field);
		}
		else {
			$fields = forms::getFields($object['formID']);
		}

		$fileLIs = array();
		foreach($fields as $field){

			if($field['type'] != 'file') continue;
			if(empty($object['data'][ $field['name'] ])) continue;

			// Figure out some needed vars for later
			$fileDataArray = $object['data'][$field['name']];
			$assetsID      = $fileDataArray['uuid'];
			$fileLIs       = array();

			uasort($fileDataArray['files']['archive'],function($a,$b) { return strnatcasecmp($a['name'],$b['name']); });

			foreach($fileDataArray['files']['archive'] as $fileID => $file){

				$_filename = pathinfo($file['name']);
				$filename  = $_filename['filename'];
				$icon      = "";
				$links     = array();
				$type 	   = explode('/', $file['type']);

				$fi            = new finfo();
				$filePathFull  = mfcs::config("archivalPathMFCS").DIRECTORY_SEPARATOR.$file['path'].DIRECTORY_SEPARATOR.$file['name'];
				$filesize      = filesize($filePathFull);

				$extraFileInfo = $fi->file($filePathFull);
				$filesize      = self::formatBytes($filesize);

				$sql       = sprintf("SELECT `checksum`, `pass`, `lastChecked` FROM `filesChecks` WHERE `location`='%s'",$file['path'].DIRECTORY_SEPARATOR.$file['name']);
				$sqlResult_cs = mfcs::$engine->openDB->query($sql);

				if (!$sqlResult_cs['result']) {
					errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
					return FALSE;
				}

				$row_cs   = mysql_fetch_array($sqlResult_cs['result'],  MYSQL_ASSOC);
				$checksum = (isnull($row_cs['checksum']))?"Not Available":$row_cs['checksum'];
				if (isnull($row_cs['lastChecked'])) {
					$checksum_pass_class = "checksum_not_checked";
				}
				else if ($row_cs['pass'] == "0") {
					$checksum_pass_class = "checksum_fail";
				}
				else {
					$checksum_pass_class = "checksum_pass";
				}


				// Choose an Icon for the type of data
				switch ($type[0]) {
					case 'image':
						$icon = '<i class="fa fa-file-image-o"></i>';
						break;
					case 'video':
						$icon = '<i class="fa fa-file-video-o"></i>';
						break;
					case 'audio':
						$icon = '<i class="fa fa-file-sound-o"></i>';
						break;
					case 'text':
						$icon = '<i class="fa fa-file-text-o"></i>';
						break;
					case 'application':
						if($type[1] == 'pdf'){
							$icon = '<i class="fa fa-file-pdf-o"></i>';
						} else {
							$icon = '<i class="fa fa-file-o"></i>';
						}
						break;
					default:
						$icon = '<i class="fa fa-file-o"></i>';
						break;
				}


				$links['Original'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
					localvars::get('siteRoot'),
					$objectID,
					$field['name'],
					$fileID,
					'archive');

				if(isset($field['convert']) && str2bool($field['convert'])){
					$links['Converted'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'processed');
				}
				if(isset($field['thumbnail']) && str2bool($field['thumbnail'])){
					$links['Thumbnail'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'thumbs');
				}
				if(isset($field['ocr']) && str2bool($field['ocr'])){
					$links['OCR'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'ocr');
				}
				if(isset($field['combine']) && str2bool($field['combine'])){
					$links['Combined PDF'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						'combinedPDF');
					$links['Combined Thumbnail'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						'combinedThumb');
				}
				if(isset($field['convertAudio']) && str2bool($field['convertAudio'])){
					$links['Converted Audio'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'audio');
				}
				if(isset($field['convertVideo']) && str2bool($field['convertVideo'])){
					$links['Converted Video'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'video');
				}
				if(isset($field['videothumbnail']) && str2bool($field['videothumbnail'])){
					$numVideoThumbs = $field['videoThumbFrames'];

					for($i = 0; $i < $numVideoThumbs; $i++){
						$filename = $file['name'];
						$filename = explode(".", $filename);
						$filename = $filename[0];

						if(!$i == 0){
							$filename = $filename."_".$i;
						}

						$links["Thumbnail_".$i] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&type=%s&name=%s',
							localvars::get('siteRoot'),
							$objectID,
							$field['name'],
							'thumbnails',
							$filename
						);
					}
				}

				$previewLinks  = array();
				$downloadLinks = array();
				$iFrameOutput;
				foreach($links as $linkLabel => $linkURL){

					// Build Links
					$previewLinks[]  = sprintf('<li><a tabindex="-1" href="javascript:void(0);" data-target="modal" data-url="%s">%s</a></li>', $linkURL, $linkLabel);
					$downloadLinks[] = sprintf('<li><a tabindex="-1" href="%s&download=1">%s</a></li>',$linkURL, $linkLabel);
				}

				// Build the preview dropdown HTML
				$previewDropdown  = '<div class="btn-group">';
				$previewDropdown .= '	<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">';
				$previewDropdown .= '		Preview <span class="caret"></span>';
				$previewDropdown .= '	</a>';
				$previewDropdown .= sprintf('<ul class="dropdown-menu fileModalPreview">%s</ul>', implode('', $previewLinks));
				$previewDropdown .= '</div>';


				// Build the download dropbox HTML
				$downloadDropdown  = '<div class="btn-group">';
				$downloadDropdown .= '	<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">';
				$downloadDropdown .= '		Download <span class="caret"></span>';
				$downloadDropdown .= '	</a>';
				$downloadDropdown .= sprintf('<ul class="dropdown-menu">%s</ul>', implode('', $downloadLinks));
				$downloadDropdown .= '</div>';


				$fileLIs[] = sprintf('<li><span class="filename span6">%s %s </span><span class="dropdowns span6"> %s %s </span><br /><span class="filesize">File size:  %s </span><br /><span class="file_checksum %s">Checksum: %s</span><br /><span class="file_dir">Location: %s</span></li>',
					$icon,
					$file['name'],
					$previewDropdown,
					$downloadDropdown,
					$filesize,
					$checksum_pass_class,
					$checksum,
					$file['path'].DIRECTORY_SEPARATOR.$file['name']
				);
			}


			$output .= sprintf('<div class="filePreviewField"><header><i class="fa fa-folder-open"></i> %s</header><ul class="filePreviews">%s</ul></div>', $field['label'], implode('', $fileLIs));

			// $output .= $iFrameOutput;

		}
		return $output;
	}

	public static function buildThumbnailURL($objectID) {

		if (objects::validID(TRUE,$objectID) === FALSE) {
			return FALSE;
		}

		if (($object = objects::get($objectID,TRUE)) === FALSE) {
			return FALSE;
		}

		$output = "";

		$fields = forms::getFields($object['formID']);

		foreach ($fields as $field) {

			if ($field['type'] != 'file') continue;

			// If there's nothing uploaded for the field, no need to continue
			if (empty($object['data'][ $field['name'] ])) continue;

			$fileDataArray = $object['data'][$field['name']];

			uasort($fileDataArray['files']['archive'],function($a,$b) { return strnatcasecmp($a['name'],$b['name']); });

			foreach ($fileDataArray['files']['archive'] as $fileID => $file) {

				$_filename = pathinfo($file['name']);
				$filename  = $_filename['filename'];

				$output = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'thumbs');

				break;

			}

		}

		return $output;

	}

	/**
	 * Returns the base path to be used when uploading files
	 *
	 * @author Scott Blake
	 * @return string
	 **/
	public static function getBaseUploadPath() {
		return mfcs::config('uploadPath', mfcs::config('mfcstmp').DIRECTORY_SEPARATOR.'mfcs');
	}

	private static function assetsIDToPath($assetsID) {

		$assetsID = str_replace('-',"",$assetsID);
		$assetsID = str_split($assetsID);
		return implode(DIRECTORY_SEPARATOR,$assetsID);

	}

	/**
	 * Returns the path to the save directory for a given fileUUID
	 *
	 * @author David Gersting
	 * @author Scott Blake
	 * @param string $type
	 * @param string $fileUUID
	 * @param bool $fullPath
	 * @return bool|string
	 */
	public static function getSaveDir($assetsID, $type=NULL, $fullPath=TRUE) {
		$type = strtolower($type);
		$path = array();

		if ($fullPath === TRUE) {
			$path[] = ($type == 'archive') ? mfcs::config('archivalPathMFCS') : mfcs::config('convertedPath');
		}
		$path[] = self::assetsIDToPath($assetsID);
		$path[] = $assetsID;
		if ($type != 'archive' && !isnull($type)) {
			$path[] = trim($type).DIRECTORY_SEPARATOR;
		}

		// Build the path as a string
		$path = implode(DIRECTORY_SEPARATOR, $path);

		if ($fullPath === TRUE) {
			// check to make sure that if the $path exists that it is a directory.
			if (file_exists($path) && !is_dir($path)) {
				return FALSE;
			}

			// Make sure the directory exists
			if (!is_dir($path)) {
				mkdir($path,0755,TRUE);
			}
		}

		return $path;
	}

	/**
	 * Generate a new asset UUID for file uploads
	 *
	 * This function will generate a UUID (v4) which is
	 * guaranteed to be unique on the filesystem at the time of execution.
	 *
	 * @author David Gersting
	 * @return string
	 */
	public static function newAssetsUUID(){
		$savePath = mfcs::config('archivalPathMFCS');
		do{
			/**
			 * Generate a UUID (version 4)
			 * @see http://www.php.net/manual/en/function.uniqid.php#94959
			 */
			$uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			);
		}while(file_exists($savePath.DIRECTORY_SEPARATOR.(self::assetsIDToPath($uuid))));

		return $uuid;
	}

	/**
	 * Returns TRUE if the input is a UUID
	 *
	 * @param string $input
	 * @return bool
	 */
	public static function isUUID($input){
		return (bool)preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $input);
	}

	/**
	 * Add a watermark to an image
	 *
	 * @author Scott Blake
	 * @param Imagick $image
	 * @param array $options
	 * @return Imagick
	 **/
	public static function addWatermark($image, $options) {
		// Get watermark image data
		$watermarkBlob = self::getWatermarkBlob($options['watermarkImage']);
		$watermark     = new Imagick();
		$watermark->readImageBlob($watermarkBlob);

		// Store image dimensions
		$imageWidth  = $image->getImageWidth();
		$imageHeight = $image->getImageHeight();

		// Store offset values to set watermark away from borders
		if (isset($options['border']) && str2bool($options['border'])) {
			$widthOffset  = isset($options['borderWidth'])  ? $options['borderWidth']  : 0;
			$heightOffset = isset($options['borderHeight']) ? $options['borderHeight'] : 0;
		}

		// Resize the watermark
		$watermark->scaleImage(
			($imageWidth  - $widthOffset  * 2) / 1.5, // 75% of the image width minus borders
			($imageHeight - $heightOffset * 2) / 1.5, // 75% of the image height minus borders
			TRUE
			);

		// Store watermark dimensions
		$watermarkWidth  = $watermark->getImageWidth();
		$watermarkHeight = $watermark->getImageHeight();

		// Get the watermark placement Example: 'top','left'
		list($positionHeight,$positionWidth) = explode("|",$options['watermarkLocation']);

		// Calculate the position
		switch ($positionHeight) {
			case 'top':
				$y = $heightOffset;
				break;

			case 'bottom':
				$y = $imageHeight - $heightOffset - $watermarkHeight;
				break;

			case 'middle':
			default:
				$y = ($imageHeight - $watermarkHeight) / 2;
				break;
		}

		switch ($positionWidth) {
			case 'left':
				$x = $widthOffset;
				break;

			case 'right':
				$x = $imageWidth - $widthOffset - $watermarkWidth;
				break;

			case 'center':
			default:
				$x = ($imageWidth - $watermarkWidth) / 2;
				break;
		}

		if ($image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y) === FALSE) {
			errorHandle::errorMsg("Failed to create watermark");
			errorHandle::newError("Failed to create watermark");
		}

		return $image;
	}

	/**
	 * Returns the binary blob for a given watermark, or FALSE on errer
	 * @param int $id
	 * @return bool|string
	 */
	public static function getWatermarkBlob($id){
		$sql = sprintf("SELECT data FROM watermarks WHERE ID='%s' LIMIT 1", mfcs::$engine->openDB->escape($id));
		$res = mfcs::$engine->openDB->query($sql);
		if($res['result']) return mysql_result($res['result'],0,'data');

		// If we're here, an error happened
		errorHandle::newError(__METHOD__."() - Failed to get watermark! (MySQL Error: {$res['error']})", errorHandle::HIGH);
		return FALSE;
	}

	/**
	 * Create and store a thumbnail of a given Imagick image object
	 *
	 * @author Scott Blake
	 * @param Imagick $image
	 * @param array $options
	 * @param string $savePath
	 * @return bool
	 **/
	private static function createThumbnail($originalFile,$filename,$options,$assetsID,$combined=FALSE) {

		if ($originalFile instanceof Imagick) {
			$image = $originalFile;
		}
		else {
			$image        = new Imagick();
			$image->readImage($originalFile);
		}

		$assetsDirectory = ($combined != FALSE)? "combine" : 'thumbs';

		$thumbname = (($combined != FALSE) ? "thumb" : $filename);
		$savePath  = self::getSaveDir($assetsID,$assetsDirectory).$thumbname;

		// Make a copy of the original
		$thumb = $image->clone();

		// Set the Width
		if (isset($options['thumbnailWidth'])) {
			$width = $options['thumbnailWidth'];
		}
		else {
			$width = 0;
		}

		// Set the Height
		if (isset($options['thumbnailHeight'])) {
			$height = $options['thumbnailHeight'];
		}
		else {
			$height = 0;
		}

		// Change the format
		if (isset($options['thumbnailFormat'])) {
			$thumb->setImageFormat($options['thumbnailFormat']);
		}

		// Scale to thumbnail size, constraining proportions
		if ($width > 0 || $height > 0) {
			$thumb->thumbnailImage($width, $height, TRUE);
		}

		// Store thumbnail, returns TRUE on success, FALSE on failure
		if ($thumb->writeImage($savePath) === FALSE) return FALSE;

		return array(
			'name'   => $thumbname,
			'path'   => self::getSaveDir($assetsID,$assetsDirectory,FALSE),
			'size'   => filesize($savePath),
			'type'   => self::getMimeType($savePath),
			'errors' => '',
			);
	}

	public static function processObjectUploads($objectID,$uploadID) {

		if (is_empty($uploadID)) return array();

		$uploadBase = files::getBaseUploadPath().DIRECTORY_SEPARATOR.$uploadID;
		$saveBase   = mfcs::config('convertedPath');

		// If the uploadPath dosen't exist, then no files were uploaded
		if(!is_dir($uploadBase)) return TRUE;

		// Generate new assets UUID and make the directory (this should be done quickly to prevent race-conditions
		$assetsID          = self::newAssetsUUID();

		if (($originalsFilepath = self::getSaveDir($assetsID,'archive')) === FALSE) {
			return array();
		}

		$return['uuid'] = $assetsID;

		// Start looping through the uploads and move them to their new home
		$files = scandir($uploadBase);
		foreach($files as $filename){
			if($filename[0] == '.') continue;

			// Clean the filename
			$cleanedFilename = preg_replace('/[^a-z0-9-_\.]/i','',$filename);
			$newFilename = $originalsFilepath.DIRECTORY_SEPARATOR.$cleanedFilename;

			// Move the uploaded files into their new home and make the new file read-only
			if (@rename("$uploadBase/$filename", $newFilename) === FALSE) {
				errorHandle::newError(__METHOD__."() - renaming files: $uploadBase/$filename", errorHandle::DEBUG);
				return FALSE;
			}
			chmod($newFilename, 0444);

			$return['files']['archive'][] = array(
				'name'   => $cleanedFilename,
				'path'   => self::getSaveDir($assetsID,'archive',FALSE),
				'size'   => filesize($newFilename),
				'type'   => self::getMimeType($newFilename),
				'errors' => '',
				);

			array_push(self::$fixity_files, self::getSaveDir($assetsID,'archive',FALSE).DIRECTORY_SEPARATOR.$cleanedFilename);
		}

		// Remove the uploads directory (now that we're done with it) and lock-down the originals dir
		rmdir($uploadBase);
		chmod($originalsFilepath, 0555);

		// Return the array
		return $return;
	}

	// Take a location and put it into the
	// @TODO this should be set back to private once batch upload processing
	// gets cleaned up
	public static function fixityInsert($location,$objectID) {

		$sql       = sprintf("INSERT INTO `filesChecks` (`location`,`ObjectID`) VALUES('%s','%s')",
			mfcs::$engine->openDB->escape($location),
			mfcs::$engine->openDB->escape($objectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	private static function createHOCR($path=NULL) {

		if (isnull($path)) {
			$path = mfcs::config('convertedPath').DIRECTORY_SEPARATOR."hocr.cfg";
		}

		// Create the hocr file (if needed)
		if(!file_exists($path)){
			if(!file_put_contents($path, 'tessedit_create_hocr 1')) {
				errorHandle::newError("Failed to create hocr file.",errorHandle::HIGH);
				return FALSE;
			}
		}

		return TRUE;
	}

	public static function processObjectFiles($assetsID, $options) {
		// Disable PHP's max execution time
		set_time_limit(0);

		$saveBase          = mfcs::config('convertedPath');
		$originalsFilepath = self::getSaveDir($assetsID,'archive');
		$originalFiles     = scandir($originalsFilepath);

		// Setup return array
		$return = array(
			'processed' => array(),
			'combine'   => array(),
			'thumbs'    => array(),
			'ocr'       => array(),
		);

		// Remove dot files from array
		foreach ($originalFiles as $I => $filename) {
			if ($filename[0] == '.') {
				unset($originalFiles[$I]);
			}
			if(empty($filename)){ 
				unset($originalFiles[$I]); 
			}
		}

		if(empty($originalFiles)){
			return TRUE; 
		}

		// Needed to put the files in the right order for processing
		if (natcasesort($originalFiles) === FALSE) {
			return FALSE;
		}

		try {
			// If combine files is checked, read this image and add it to the combined object
			if (isset($options['combine']) && str2bool($options['combine'])) {

				try {
					$errors      = array();
					$createThumb = TRUE;

					// Create us some temp working space
					$tmpDir = mfcs::config('mfcstmp').DIRECTORY_SEPARATOR.uniqid();
					mkdir($tmpDir,0777,TRUE);

					// Ensure that the HOCR file is created
					if (!self::createHOCR("$saveBase/hocr.cfg")) return FALSE;


					$gsTemp = $tmpDir.DIRECTORY_SEPARATOR.uniqid();
					touch($gsTemp);

					foreach ($originalFiles as $filename) {

						// Figure some stuff out about the file
						$originalFile = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
						$_filename    = pathinfo($originalFile);
						$filename     = $_filename['filename'];


						$baseFilename = $tmpDir.DIRECTORY_SEPARATOR.$filename;

						// Create a thumbnail of the first image
						if ($createThumb === TRUE) {

							if (($return['combine'][] = self::createThumbnail($originalFile,$filename,$options,$assetsID,TRUE)) === FALSE) {
								throw new Exception("Failed to create thumbnail: ".$filename);
							}

							// Prevent making multiple thumbnails
							$createThumb = FALSE;
						}

						// perform hOCR on the original uploaded file which gets stored in combined as an HTML file
						$_exec = shell_exec(sprintf('tesseract %s %s -l eng %s 2>&1',
							escapeshellarg($originalFile), // input.ext
							escapeshellarg($baseFilename), // output.html
							escapeshellarg("$saveBase/hocr.cfg") // hocr config file
							));

						// If a new-line char is in the output, assume it's an error
						// Tesseract failed, let's normalize the image and try again
						if (strpos(trim($_exec), "\n") !== FALSE) {
							$errors[] = "Unable to process OCR for ".basename($originalFile).". Continuing&hellip;";
							errorHandle::warningMsg("Unable to process OCR for ".basename($originalFile).". Continuing&hellip;");

							// Ensure HTML file exists
							touch($baseFilename.".html");
						}

						// Create an OCR'd pdf of the file
						$_exec = shell_exec(sprintf('hocr2pdf -i %s -s -o %s < %s 2>&1',
							escapeshellarg($originalFile), // input.ext
							escapeshellarg($baseFilename.".pdf"), // output.pdf
							escapeshellarg($baseFilename.".html") // input.html
							));

						if (trim($_exec) !== 'Writing unmodified DCT buffer.') {
							if (strpos($_exec,'Warning:') !== FALSE) {
								errorHandle::newError("hocr2pdf Warning: ".$_exec, errorHandle::DEBUG);
							}
							else {
								errorHandle::errorMsg("Failed to Create PDF: ".basename($filename,"jpg").".pdf");
								throw new Exception("hocr2pdf Error: ".$_exec);
							}
						}

						// Add this pdf to a temp file that will be read in by gs
						file_put_contents($gsTemp, $baseFilename.".pdf".PHP_EOL, FILE_APPEND);

						// We're done with this file, delete it
						unlink($baseFilename.".html");
					}



					// Combine all PDF files in directory
					$_exec = shell_exec(sprintf('gs -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s @%s 2>&1',
						self::getSaveDir($assetsID,'combine')."combined.pdf",
						$gsTemp
					));
					if (!is_empty($_exec)) {
						errorHandle::errorMsg("Failed to combine PDFs into single PDF.");
						throw new Exception("GhostScript Error: ".$_exec);
					}



					$return['combine'][] = array(
						'name'   => 'combined.pdf',
						'path'   => self::getSaveDir($assetsID,'combine',FALSE),
						'size'   => filesize(self::getSaveDir($assetsID,'combine').'combined.pdf'),
						'type'   => 'application/pdf',
						'errors' => $errors,
					);

					// Lastly, we delete our temp working dir (always nice to cleanup after yourself)
					if (self::cleanupTempDirectory($tmpDir) === FALSE) {
						errorHandle::errorMsg("Unable to clean up temporary directory: ".$tmpDir);
						throw new Exception("Unable to clean up temporary directory: ".$tmpDir);
					}


				}
				catch (Exception $e) {
					// We need to delete our working dir
					if (isset($tmpDir) && is_dir($tmpDir)) {
						if (self::cleanupTempDirectory($tmpDir) === FALSE) {
							errorHandle::errorMsg("Unable to clean up temporary directory (in Exception): ".$tmpDir);
						}
					}
					throw new Exception($e->getMessage(), $e->getCode(), $e);
					return FALSE;
				}
			} // If Combine

			// This conditional needs updated when different conversion options are added or removed.
			// If the file has no processing to do, don't do any ...
			// @TODO - NEEDS TO BE DYNAMIC
			if (!isset($options['convert']) && !isset($options['thumbnail']) && !isset($options['ocr'])
				 && !isset($options['convertAudio']) && !isset($options['convertAudio']) && !isset($options['videothumbnail']) ) {
				return $return;
			}

			foreach ($originalFiles as $filename) {
				$originalFile     = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
				$_filename        = pathinfo($originalFile);
				$filename         = $_filename['filename'];
				$thumbnailCreated = false;

				// Convert uploaded files into some ofhter size/format/etc
				if (isset($options['convert']) && str2bool($options['convert'])) {
					// we create the Imagick object here so that we can pass it to thumbnail creation
					$image = new Imagick();
					$image->readImage($originalFile);
					// Convert it
					if (($image = self::convertImage($image,$options,$assetsID,$filename)) === FALSE) {
						throw new Exception("Failed to create processed image: ".$originalFile);
					}

					$filename = $filename.'.'.strtolower($image->getImageFormat());

					// Create a thumbnail that includes converted options
					if (isset($options['thumbnail']) && str2bool($options['thumbnail'])) {
						if (($return['thumbs'][] = self::createThumbnail($image,$filename,$options,$assetsID)) === FALSE) {
							throw new Exception("Failed to create thumbnail: ".$filename);
						}
						$thumbnailCreated = true;
					}

					// Set the return array
					$return['processed'][] = array(
						'name'   => $filename,
						'path'   => self::getSaveDir($assetsID,'processed',FALSE),
						'size'   => filesize(self::getSaveDir($assetsID,'processed').$filename),
						'type'   => self::getMimeType(self::getSaveDir($assetsID,'processed').$filename),
						'errors' => '',
					);
				}

				// Create a thumbnail without any conversions
				if (isset($options['thumbnail']) && str2bool($options['thumbnail']) && ($thumbnailCreated === false)) {
					if (($return['thumbs'][] = self::createThumbnail($originalFile,$filename,$options,$assetsID)) === FALSE) {
						throw new Exception("Failed to create thumbnail: ".$filename);
					}
				}

				// Create an OCR text file
				if (isset($options['ocr']) && str2bool($options['ocr'])) {
					if (($return['ocr'][] = self::createOCRTextFile($originalFile,$assetsID,$filename)) === FALSE) {
						errorHandle::errorMsg("Failed to create OCR text file: ".$filename);
						throw new Exception("Failed to create OCR file for $filename");
					}
				}

				// Convert Audio
				if (isset($options['convertAudio']) && str2bool($options['convertAudio'])) {
					$convertAudio =  self::convertAudio($assetsID, $filename, $originalFile, $options);
					if(isset($convertAudio['error'])){
						throw new Exception('Failed to convert audio:'.$convertAudio['errror']);
					} else {
						$return['audio'][] = $convertAudio;
					}
				}

				// Convert Video
				if (isset($options['convertVideo']) && str2bool($options['convertVideo'])) {
					$convertVideo =  self::convertVideo($assetsID, $filename, $originalFile, $options);
					if($convertVideo['errors']){
						throw new Exception($convertVideo['errors']);
					} else {
						$return['video'][] = $convertVideo;
					}
				}

				// Video Thumbnails
				if (isset($options['videothumbnail']) && str2bool($options['videothumbnail'])) {
					$createThumbs =  self::createVideoThumbs($assetsID, $filename, $originalFile, $options);
					if(isset($createThumbs['errors'])){
						throw new Exception('Failed to create video thumbnails');
					}
					else {
						$return['videoThumbs'][] = $createThumbs;
					}
				}
			}

		} // Catch All Try
		catch (Exception $e) {
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::DEBUG);
			return FALSE;
		}

		return $return;
	}


	public static function convertVideo($assetsID, $name, $originalFile, $options){
		try {
            $ffmpeg        = new FFmpeg();
            $inputFile     = $ffmpeg->input($originalFile);
            $uploadedVideo = $ffmpeg->getMetadata();

            // set some default values that are needed for good conversions
            // most conversions will not be able to go past a certain sample rate
            $defaultFrameRate    = "24";
            $resolutionMaxWidth  = "1920";
            $resolutionMaxHeight = "1080";

            // Get Option Info For Saving
            // ----------------------------------------------------------------------
			$savePath = self::getSaveDir($assetsID,'video');
			$format   = ".".$options['videoFormat'];

            // Set Defaults Needed for Good Conversions
            // ----------------------------------------------------------------------
            $ffmpeg->frameRate($defaultFrameRate);
            $ffmpeg->set('-strict', '-2');
            $ffmpeg->logLevel('quiet');

            // Aspect Ratio
            // @TODO Need to figure out the process for converting videos to a
            // certain aspect ratio that may not be in that aspect ratio with
            // bars added in places that need bars
            // ---------------------------------------------------------------------
            if(isset($options['aspectRatio']) && !is_empty($options['aspectRatio'])){
                $aspectRatio = explode(":", $options['aspectRatio']);
                $numAspectRatio = $aspectRatio[0] / $aspectRatio[1];
            } else {
                $numAspectRatio = $uploadedVideo['width'] / $uploadedVideo['height'];
            }

            // Check and Modify the Size
            // Helps to Make sure that Max Res is not exceeded
            // ----------------------------------------------------------------------
            // use defaults
            if((!isset($options['videoWidth']) && !isset($options['videoHeight'])) || (is_empty($options['videoWidth']) && is_empty($options['videoHeight']))){
                 $videoWidth  = $uploadedVideo['width'];
                 $videoHeight = $uploadedVideo['height'];
            }

            // use width and find height
            if(isset($options['videoWidth']) && !is_empty($options['videoWidth']) && !isset($options['videoHeight'])){
                $videoWidth  = ($options['videoWidth'] <= $resolutionMaxWidth ? $options['videoWidth'] : $resolutionMaxWidth);
                $videoHeight =  FFmpeg::aspectRatioCalc($numAspectRatio, $uploadedVideo['width'], $uploadedVideo['height'], $options['videoWidth']);
            }
            // use height and find width
            else if(!isset($options['videoWidth']) && isset($options['videoHeight']) && !is_empty($options['videoHeight'])){
                $videoHeight = ($options['videoHeight'] <= $resolutionMaxHeight ? $options['videoHeight'] : $resolutionMaxHeight);
                $videoWidth  = FFmpeg::aspectRatioCalc($numAspectRatio, $uploadedVideo['width'], $uploadedVideo['height'], null, $options['videoHeight']);
            }
            // if both are set, unset the video height and use the width so that video retains aspect ratio
            // check to see if they put portriat or landscape heights
            else {
                if($options['videoWidth'] < $options['videoHeight']){
                    $options['videoWidth'] = $options['videoHeight'];
                }
                $videoWidth  = ($options['videoWidth'] <= $resolutionMaxWidth ? $options['videoWidth'] : $resolutionMaxWidth);
                $videoHeight =  FFmpeg::aspectRatioCalc( $numAspectRatio, $uploadedVideo['width'], $uploadedVideo['height'], $options['videoWidth']);
            }

            $ffmpeg->size($videoWidth."x".$videoHeight);

            // This rotates the video if it is vertical for the output settings
            // this sets the metadata of the video to rotate 90 and play the vertical video
            if($uploadedVideo['rotation'] == 90){
                $ffmpeg->set('-metadata:s:v', 'rotate="90"');
                $ffmpeg->transpose(0);
            }

            // BitRates of Video
            // ----------------------------------------------------------------------
            if(isset($options['videobitRate']) && !is_empty($options['videobitRate'])){
                $bitrate = floor(($options['videobitRate'] * 1024));
                if($bitrate > floatval($uploadedVideo['videoBitRate'])){
                    $bitrate = floatval($uploadedVideo['videoBitRate']);
                }
            } else {
                $bitrate = $uploadedVideo['videoBitRate'];
            }

            // Make sure its not null
            if($bitrate !== null){
                $ffmpeg->videoBitrate($bitrate);
            }


            if(!is_dir($savePath)){
                throw new Exception("Can not save file because directory doesn't exsist");
            }

            // Where does this go?
            $ffmpeg->output($savePath.$name.$format);

            // Make it Happen
            $conversion = $ffmpeg->ready();

            if($conversion !== 0){
                throw new Exception('There was a problem with the video conversion check ffmpeg command: ' . $ffmpeg->command);
            }

        } catch (Exception $e) {
            errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
            errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::DEBUG);
            return array(
                'errors' => $e->getMessage(),
            );
        }

		$return = array(
			'name'   => $name.'.'.$format,
			'path'   => self::getSaveDir($assetsID,'video',FALSE),
			'size'   => filesize(self::getSaveDir($assetsID,'video').$name.$format),
			'type'   => self::getMimeType(self::getSaveDir($assetsID,'video').$name.$format),
			'errors' => '',
		);

        return $return;
	}

	public static function createVideoThumbs($assetsID, $name, $originalFile, $options){
		try{
            $ffmpeg        = new FFmpeg();
            $inputFile     = $ffmpeg->input($originalFile);
            $uploadedVideo = $ffmpeg->getMetadata();


            // Removes Error Logs and sets strict file conversions
            // ----------------------------------------------------------------------
            $ffmpeg->set('-strict', '-2');
            $ffmpeg->logLevel('quiet');

            // Get Thumbnail Information
            // ----------------------------------------------------------------------
            $numberOfThumbnails = $options['videoThumbFrames'];
            $thumbSize          = $options['videoThumbWidth']."x".$options['videoThumbHeight'];
            $thumbFormat        = $options['videoFormatThumb'];
            $path               = self::getSaveDir($assetsID,'thumbnails');

            // Create number to use as intervals for time caputers
            // ----------------------------------------------------------------------
            $timeOfCap          = floor($uploadedVideo['duration'] / $numberOfThumbnails);

            if(!is_dir($path)){
                throw new Exception("Thumbnail directory is not setup.");
            }

            // Loop through and save file information
            // ----------------------------------------------------------------------
            for($i = 0; $i < $numberOfThumbnails; $i++){
                $thumbName = $name."_$i";
                $time = $timeOfCap * $i;

                if($time == 0){
                    $time = 1; // need to start at frame 1
                }

                $ffmpeg->thumb($thumbSize, $time)->output($path.$thumbName.$thumbFormat);
                $conversion = $ffmpeg->ready();

				$return[] = array(
					'name'   => $name.'.'.$format,
					'path'   => self::getSaveDir($assetsID,'videoThumbs',FALSE),
					'size'   => filesize(self::getSaveDir($assetsID,'videoThumbs').$thumbName.$thumbFormat),
					'type'   => self::getMimeType(self::getSaveDir($assetsID,'videoThumbs').$thumbName.$thumbFormat),
					'errors' => '',
				);

                if($conversion !== 0){
                    throw new Exception('Could not make thumbs: ' . $ffmpeg->command);
                }
            }
        } catch (Exception $e) {
            errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
            errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::DEBUG);

            return array('errors' => $e->getMessage());
        }

        return $return;
    }

	public static function convertAudio($assetsID, $name, $originalFile, $options){
        try{
            $ffmpeg       = new FFmpeg();
            $inputFile    = $ffmpeg->input($originalFile);
            $uploadedData = $ffmpeg->getMetadata();

            // Formatting and Path
            // ----------------------------------------------------------------------
            $format   = $options['audioFormat'];
            $savePath = self::getSaveDir($assetsID,'audio');

            // Removes Error Logs and sets strict file conversions
            // ----------------------------------------------------------------------
            $ffmpeg->set('-strict', '-2');
            $ffmpeg->logLevel('debug');

            // Set Audio Type for ogg files
            // ogg doesn't use bitrate they use quality level
            if($format == 'ogg'){
                 $ffmpeg->set('-acodec', 'libvorbis');
                 $ffmpeg->set('-qscale:a', '5');
            } else {
            	// Other File types do use them
                if(isset($uploadedData['maxBitRate']) && !is_null($uploadedData['maxBitRate'])){
                	$maxBitRate = $uploadedData['maxBitRate'];
                }

                $bitrate = isset($options['bitRate']) ? floor(($options['bitRate'] * 1024)) : $uploadedData['audioBitRate'];

                if(isset($maxBitRate) && $bitrate > $maxBitRate){
                    $bitrate = $maxBitRate;
                }

                $ffmpeg->audioBitrate($bitrate); // set bitrate
                $ffmpeg->set('vol', 265); // set volume
            }

            if(!is_dir($savePath)){
                throw new Exception("Directory is not setup");
            }

            $ffmpeg->output($savePath.$name.".".$format);
            $conversion = $ffmpeg->ready();

            if($conversion !== 0){
                throw new Exception('Could not convert audio: ' . $ffmpeg->command);
            }

        } catch (Exception $e) {
            errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
            return array('errors' => $e->getMessage());
        }

		$return = array(
			'name'   => $name.'.'.$format,
			'path'   => self::getSaveDir($assetsID,'audio',FALSE),
			'size'   => filesize(self::getSaveDir($assetsID,'audio').$name.'.'.$format),
			'type'   => self::getMimeType(self::getSaveDir($assetsID,'audio').$name.'.'.$format),
			'errors' => '',
		);

        return $return;
	}


	public static function createOCRTextFile($originalFile,$assetsID,$filename) {

		$text = TesseractOCR::recognize($originalFile);

		if (file_put_contents(self::getSaveDir($assetsID,'ocr').DIRECTORY_SEPARATOR.$filename.'.txt', $text) === FALSE) {
			return FALSE;
		}

		$return['ocr'][] = array(
			'name'   => $filename.'.txt',
			'path'   => self::getSaveDir($assetsID,'ocr',FALSE),
			'size'   => filesize(self::getSaveDir($assetsID,'ocr').$filename.'.txt'),
			'type'   => self::getMimeType(self::getSaveDir($assetsID,'ocr').$filename.'.txt'),
			'errors' => '',
		);
	}

	public static function convertImage($image,$options,$assetsID,$filename) {

		// Convert format?
		if (!empty($options['convertFormat'])) $image->setImageFormat($options['convertFormat']);

		// Change resolution
		if (isset($options['convertResolution'])) {
			$image->setImageUnits(Imagick::RESOLUTION_PIXELSPERINCH);
			$image->setImageResolution($options['convertResolution'],$options['convertResolution']);
			$image->resampleImage($options['convertResolution'], $options['convertResolution'], Imagick::FILTER_UNDEFINED, 0);
		}

		// Add a border
		if (isset($options['border']) && str2bool($options['border'])) {
			if ($options['convertWidth'] > 0 || $options['convertHeight'] > 0) {
				// Resize the image first, taking into account the border width
				$image->scaleImage(
					($options['convertWidth']  - $options['borderWidth']  * 2),
					($options['convertHeight'] - $options['borderHeight'] * 2),
					TRUE);
			}

			// Add the border
			$image->borderImage(
				$options['borderColor'],
				$options['borderWidth'],
				$options['borderHeight']);
		}
		else if ($options['convertWidth'] > 0 || $options['convertHeight'] > 0) {
			// Resize without worrying about the border
			$image->scaleImage($options['convertWidth'], $options['convertHeight'], TRUE);
		}

		// Add a watermark
		if (isset($options['watermark']) && str2bool($options['watermark'])) {
			$image = self::addWatermark($image, $options);
		}

		// Store image
		if ($image->writeImage(self::getSaveDir($assetsID,'processed').$filename.'.'.strtolower($image->getImageFormat())) === FALSE) {
			return FALSE;
		}

		return $image;
	}

	private static function cleanupTempDirectory($tmpDir) {

		// If the given path is not a directory, just return
		if (!is_dir($tmpDir)) return TRUE;

		// If the given path is not writable, return FALSE
		if (!is_writable($tmpDir)) return FALSE;

		$return = TRUE;
		foreach (glob("$tmpDir/*") as $file) {

			// if the unlink is unsuccessful, return FALSE
			if (unlink($file) === FALSE) return FALSE;

		}

		// delete the directory, return the bool of rmdir
		return rmdir($tmpDir);
	}

	public static function get_upload_directories() {

		$return = "";

		$upload_dirs = scandir(mfcs::config('ftpUploadDirectory'));
		foreach ($upload_dirs as $directory) {
			if (!is_dir(mfcs::config('ftpUploadDirectory')."/".$directory) || preg_match("/^\./",$directory)) continue;

			$return .= sprintf('<option value="%s">%s</option>',$directory,$directory);

		}

		return $return;

	}

}
