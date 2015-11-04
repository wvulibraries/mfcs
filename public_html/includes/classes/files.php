<?php

/**
 * Main MFCS object
 * @author David Gersting
 */
class files {

	private static $insertFieldNames = array();

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
			$convert      = str2bool($fieldOptions['convert']);
			$convertVideo = str2bool($fieldOptions['convertVideo']);
			$convertAudio = str2bool($fieldOptions['convertAudio']);

			// other options
			$combine      = str2bool($fieldOptions['combine']);
			$ocr          = str2bool($fieldOptions['ocr']);
			$thumbnail    = str2bool($fieldOptions['thumbnail']);


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

		// Figure out what to do with the data
		switch(trim(strtolower($mimeType))){
			case 'image/tiff':
				self::printImage($filename,$mimeType);
				break;

			case 'image/gif':
			case 'image/jpeg':
			case 'image/png':
			case 'text/css':
			case 'text/csv':
			case 'text/html':
			case 'text/javascript':
			case 'text/plain':
			case 'text/xml':
			case 'application/javascript':
			case 'application/pdf':
				ini_set('memory_limit',-1);
				header("Content-type: $mimeType");
				die(file_get_contents($filename));
				break;

			default:
				echo '[No preview available - Unknown file type]';
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

			// If there's nothing uploaded for the field, no need to continue
			if(empty($object['data'][ $field['name'] ])) continue;

			// Figure out some needed vars for later
			$fileDataArray = $object['data'][$field['name']];
			$assetsID      = $fileDataArray['uuid'];
			$fileLIs = array();


			uasort($fileDataArray['files']['archive'],function($a,$b) { return strnatcasecmp($a['name'],$b['name']); });

			foreach($fileDataArray['files']['archive'] as $fileID => $file){
				$_filename = pathinfo($file['name']);
				$filename  = $_filename['filename'];
				$links     = array();

				$links['Original'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
					localvars::get('siteRoot'),
					$objectID,
					$field['name'],
					$fileID,
					'archive');

				if(str2bool($field['convert'])){
					$links['Converted'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'processed');
				}
				if(str2bool($field['thumbnail'])){
					$links['Thumbnail'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'thumbs');
				}
				if(str2bool($field['ocr'])){
					$links['OCR'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&fileID=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$fileID,
						'ocr');
				}
				if(str2bool($field['combine'])){
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


				$previewLinks  = array();
				$downloadLinks = array();
				foreach($links as $linkLabel => $linkURL){
					$previewLinks[]  = sprintf('<li><a tabindex="-1" href="javascript:;" onclick="previewFile(this,\'%s\')">%s</a></li>', $linkURL, $linkLabel);
					$downloadLinks[] = sprintf('<li><a tabindex="-1" href="%s&download=1">%s</a></li>',$linkURL, $linkLabel);
				}

				// Build the preview dropdown HTML
				$previewDropdown  = '<div class="btn-group">';
				$previewDropdown .= '	<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">';
				$previewDropdown .= '		Preview <span class="caret"></span>';
				$previewDropdown .= '	</a>';
				$previewDropdown .= sprintf('<ul class="dropdown-menu">%s</ul>', implode('', $previewLinks));
				$previewDropdown .= '</div>';

				// Build the download dropbox HTML
				$downloadDropdown  = '<div class="btn-group">';
				$downloadDropdown .= '	<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">';
				$downloadDropdown .= '		Download <span class="caret"></span>';
				$downloadDropdown .= '	</a>';
				$downloadDropdown .= sprintf('<ul class="dropdown-menu">%s</ul>', implode('', $downloadLinks));
				$downloadDropdown .= '</div>';

				$fileLIs[] = sprintf('<li><div class="filename">%s</div><!-- TODO <button class="btn">Field Details</button> -->%s%s</li>',
					$file['name'],
					$previewDropdown,
					$downloadDropdown);
			}

			$output .= sprintf('<div class="filePreviewField"><header>%s</header><ul class="filePreviews">%s</ul></div>', $field['label'], implode('', $fileLIs));
		}

		// Include the filePreview Modal, and the CSS and JavaScript links
		$output .= '<div id="filePreviewModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3></h3></div><div class="modal-body"><iframe class="filePreview"></iframe></div><div class="modal-footer"><a class="btn previewDownloadLink">Download File</a><a class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a></div></div>';
		$output .= sprintf('<link href="%sincludes/css/filePreview.css" rel="stylesheet">', localvars::get('siteRoot'));
		$output .= sprintf('<script src="%sincludes/js/filePreview.js"></script>', localvars::get('siteRoot'));

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

		$thumbname = (($combined != FALSE)? "thumb" : $filename).'.'.strtolower($options['thumbnailFormat']);
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

			// Insert into filesChecks table (fixity)
			if (!self::fixityInsert(self::getSaveDir($assetsID,'archive',FALSE).DIRECTORY_SEPARATOR.$cleanedFilename)) {
				errorHandle::newError(__METHOD__."() - couldn't create fixity entry.", errorHandle::DEBUG);
				// @todo : we need a script that periodically checks to make sure all files are in
				// filesChecks table ... I don't think we want to return FALSE here on failure because some files
				// have already been moved ... 
			}
		}

		// Remove the uploads directory (now that we're done with it) and lock-down the originals dir
		rmdir($uploadBase);
		chmod($originalsFilepath, 0555);

		// Return the array
		return $return;
	}

	// Take a location and put it into the 
	private static function fixityInsert($location) {

		$sql       = sprintf("INSERT INTO `filesChecks` (`location`) VALUES('%s')",
			mfcs::$engine->openDB->escape($location)
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
			if (!isset($options['convert']) && !isset($options['thumbnail']) && !isset($options['ocr'])
				 && !isset($options['convertAudio']) && !isset($options['convertAudio']) && !isset($options['videothumbnail']) ) {
				return $return;
			}

			foreach ($originalFiles as $filename) {
				$originalFile = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
				$_filename    = pathinfo($originalFile);
				$filename     = $_filename['filename'];

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
				if (isset($options['thumbnail']) && str2bool($options['thumbnail'])) {
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
					if(!$convertAudio){
						throw new Exception('Failed to convert audio');
					} else {
						$return['audio'][] = $convertAudio;
					}
				}


				// Convert Video
				if (isset($options['convertVideo']) && str2bool($options['convertVideo'])) {
					$convertVideo =  self::convertVideo($assetsID, $filename, $originalFile, $options);
					if($convertVideo['errors']){
						throw new Exception("VideoFail:  ".$convertVideo['errorMessage']);
					} else {
						$return['video'][] = $convertVideo;
					}
				}

				// Video Thumbnails
				if (isset($options['videothumbnail']) && str2bool($options['videothumbnail'])) {
					$createThumbs =  self::createVideoThumbs($assetsID, $filename, $originalFile, $options);
					if($createThumbs['errors']){
						throw new Exception('Failed to create video thumbnails');
					}
					else {
						$return['videoThumbs'][] = $createThumbs;
					}
				}

			} // Foreach File

		} // Catch All Try
		catch (Exception $e) {
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::DEBUG);
			return FALSE;
		}

		return $return;
	}


	public static function convertVideo($assetsID, $name, $originalFile, $options){
		try{
			$ffmpeg           = new FFMPEG($originalFile);
			$originalFileData = $ffmpeg->getMetadata();

			// video options
			$savePath = self::getSaveDir($assetsID,'video');
			$format   = ".".$options['videoFormat'];

			// bitrate conversions
			$bitrate = (isset($options['videobitRate']) ? floor(($options['videobitRate'] * 1024)) : number_format(256 * 1024, 2));
			$conversionOptions['ab'] = $bitrate;

			// return stuff
			$returnArray = array(
				'name'    => $name.$format,
				'path'    => $savePath,
				'format'  => $format,
				'options' => $conversionOptions,
				'info'    => $ffmpeg->returnInformation()
			);

			// Valid File?
			if(!$ffmpeg->isValid() && !$ffmpeg->isVideo()){
				throw new Exception("File is not valid, or the video file was not a video. Can't Convert Video.");
			}

			// path exsists
			if(!is_dir($savePath)){
				throw new Exception("Directory is not setup");
			}


			// Catch all for not allowing bad aspect ratios
			// Setup the variables for video size based on aspect ratio and such
			if(isset($options['videoHeight']) && isset($options['videoWidth'])){
				if(isset($options['aspectRatio']) && !isnull($options['aspectRatio'])){
					// force the aspect ratio in the height by using the width
					$aspectRatio = explode(":", $options['aspectRatio']);
					$width = $options['videoWidth'];
					$height = floor(($width/$aspectRatio[0]) * $aspectRatio[1]);
					$videoSize = sprintf("%sx%s",
						$width,
						$height
					);
					$conversionOptions['aspect'] = $options['aspectRatio'];
				}
				else {
					// height width set for original files aspect ratio
					if(isset($originalFileData['width']) && isset($originalFileData['height'])){
						$ratio     = $originalFileData['width'] / $originalFileData['height'];
						$videoSize = sprintf("%sx%s",
							$options['videoWidth'],
							floor($options['videoWidth'] / $ratio)
						);
					}
				}
				// add to conversion options
				$conversionOptions['s'] = $videoSize;
			}
			// conversion options
			$conversion = $ffmpeg->convert($savePath.$name.$format, array(), $conversionOptions);

		} catch (Exception $e) {
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::DEBUG);

			//setup return errors
			$returnArray['options']      = $conversionOptions;
			$returnArray['errors']       = TRUE;
			$returnArray['errorMessage'] = $e->getMessage();
			return $returnArray;
		}

		// return errors for no expections
		$returnArray['errors'] = FALSE;
		return $returnArray;
	}

	public static function createVideoThumbs($assetsID, $name, $originalFile, $options){
		try{
			$ffmpeg             = new FFMPEG($originalFile);
			$originalFileData   = $ffmpeg->getMetadata();

			// create thumbnails
			$numberOfThumbnails = $options['videoThumbFrames'];
			$thumbHeight        = $options['videoThumbHeight'];
			$thumbWidth         = $options['videoThumbWidth'];
			$thumbFormat        = $options['videoFormatThumb'];
			$path               = self::getSaveDir($assetsID,'thumbnails');

			$returnArray = array(
				'name'    => $name,
				'path'    => $path,
				'format'  => $thumbFormat,
				'options' => array(
							'height' => $thumbHeight,
							'width'  => $thumbWidth,
						 ),
				'info'    => $ffmpeg->returnInformation(),
			);

			// path exsists
			if(!is_dir($path)){
				throw new Exception("Thumbnail directory is not setup.");
			}

			// valid
			if(!$ffmpeg->isValid() && !$ffmpeg->isVideo()){
				throw new Exception("File is not valid, or the video file was not a video. Can't create thumbs");
			}

			// get thumbnails
			$ffmpeg->getThumbnails($numberOfThumbnails, $path, $name, $thumbHeight, $thumbWidth, $thumbFormat);

		} catch (Exception $e) {
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::DEBUG);
			$returnArray['errors'] = TRUE;
			return $returnArray;
		}

		$returnArray['errors'] = FALSE;
		return $returnArray;
	}

	public static function convertAudio($assetsID, $name, $originalFile, $options){
		try{
			$ffmpeg            = new FFMPEG($originalFile);

			// Valid File?
			if(!$ffmpeg->isValid() && !$ffmpeg->isAudio()){
				throw new Exception("File is not valid, or the audio is not long enough.");
				return FALSE;
			}

			// Conversion Options
			// Also changes the Kbs to Bytes
			$bitrate = (isset($options['bitRate']) ? floor(($options['bitRate'] * 1024)) : number_format(256 * 1024, 2));

			$conversionOptions = array(
				"ab"  => $bitrate,
				"vol" => "256",
			);

			$savePath = self::getSaveDir($assetsID,'audio');
			$format   = $options['audioFormat'];

			if(!is_dir($savePath)){
				throw new Exception("Directory is not setup");
				return FALSE;
			}

			$ffmpeg->convert($savePath.$name.".".$format, array(), $conversionOptions); // convert to flash

		} catch (Exception $e) {
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
		}

		$returnArray = array(
			'name'    => $name.".".$format,
			'path'    => $savePath,
			'format'  => $format,
			'options' => $conversionOptions,
			'info'    => $ffmpeg->returnInformation(),
			'errors'  => '',
		);

		return $returnArray;
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


}
