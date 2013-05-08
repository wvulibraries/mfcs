<?php

/**
 * Main MFCS object
 * @author David Gersting
 */
class files {

	public static function generateFilePreview($filename,$mimeType=NULL,$fileData=NULL){
		// Determine the object's MIME type
		if(!isset($mimeType)){
			if(isPHP('5.3')){
				$fi = new finfo(FILEINFO_MIME_TYPE);
				$mimeType = $fi->buffer($fileData);
			}else{
				$fi = new finfo(FILEINFO_MIME);
				list($mimeType,$mimeEncoding) = explode(';', $fi->buffer($fileData));
			}
		}

		// Get the file's source
		if(!isset($fileData)) $fileData = file_get_contents($filename);

		// Figure out what to do with the data
		switch(trim(strtolower($mimeType))){
			case 'image/tiff':
				$tmpName = tempnam(sys_get_temp_dir(), 'mfcs').".png";
				shell_exec(sprintf('convert %s %s 2>&1',
					escapeshellarg($filename),
					escapeshellarg($tmpName)));
				echo sprintf('<html><img src="data:%s;base64,%s" /></html>',
					$mimeType,
					base64_encode(file_get_contents($tmpName)));
				unlink($tmpName);
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
				header("Content-type: $mimeType");
				echo $fileContents;
				break;

			default:
				echo '[No preview available - Unknown file type]';
				break;
		}
	}

	/**
	 * Returns the base path to be used when uploading files
	 *
	 * @return string
	 * @author Scott Blake
	 **/
	public static function getBaseUploadPath() {
		return mfcs::config('uploadPath', sys_get_temp_dir().DIRECTORY_SEPARATOR.'mfcs');
	}

	/**
	 * Returns the path to the save directory for a given fileUUID
	 *
	 * @author David Gersting
	 * @param string $type
	 * @param string $fileUUID
	 * @return string
	 */
	public static function getSaveDir($type,$fileUUID){
		// error checking - allow a full filename to be passed (aka: stip off the fileExt)
		if(FALSE !== strpos($fileUUID,'.')) $fileUUID = pathinfo($fileUUID,PATHINFO_FILENAME);

		$savePath       = mfcs::config('savePath');
		$newFileSubpath = str_replace('-',DIRECTORY_SEPARATOR,$fileUUID);
		$result         = $savePath.DIRECTORY_SEPARATOR.trim(strtolower($type)).DIRECTORY_SEPARATOR.$newFileSubpath;

		if(!is_dir($result)) mkdir($result,0755,TRUE);
		return $result;
	}

	/**
	 * Generate a new UUID for file uploads
	 *
	 * This function will generate a UUID (v4) which is
	 * guaranteed to be unique on the filesystem at the time of execution.
	 *
	 * @author David Gersting
	 * @return string
	 */
	public static function newFileUUID(){
		$saveBase = mfcs::config('savePath').DIRECTORY_SEPARATOR.'originals';
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
			$uuidFilepath = str_replace('-',DIRECTORY_SEPARATOR,$uuid);
		}while(is_dir($saveBase.DIRECTORY_SEPARATOR.$uuidFilepath));
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
	 * @param array $field
	 * @return Imagick
	 **/
	public static function addWatermark($image, $field) {
		// Get watermark image data
		$watermarkBlob = self::getWatermarkBlob($field['watermarkImage']);
		$watermark     = new Imagick();
		$watermark->readImageBlob($watermarkBlob);

		// Store image dimensions
		$imageWidth  = $image->getImageWidth();
		$imageHeight = $image->getImageHeight();

		// Store offset values to set watermark away from borders
		if (isset($field['border']) && str2bool($field['border'])) {
			$widthOffset  = isset($field['borderWidth'])  ? $field['borderWidth']  : 0;
			$heightOffset = isset($field['borderHeight']) ? $field['borderHeight'] : 0;
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
		list($positionHeight,$positionWidth) = explode("|",$field['watermarkLocation']);

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

	public static function updateObjectFiles($objectID,$objectField,$uploadID){
		$uploadBase = files::getBaseUploadPath().DIRECTORY_SEPARATOR.$uploadID;
		$saveBase   = mfcs::config('savePath');
		$newFiles  = array();
		if(isnull($objectID)){
			$objectFiles = array();
		}else{
			$object      = objects::get($objectID);
			$objectFiles = (array)$object['data'][$objectField];
		}

		// If the uploadPath dosen't exist, then no files were uploaded
		if(!is_dir($uploadBase)) return $results;

		// Build an array of all uploaded files
		$uploadedFiles = array();
		$files         = scandir($uploadBase);
		natsort($files);
		foreach($files as $file){
			if($file{0} == '.') continue;
			$uploadedFiles[] = $file;
		}

		// If $uploadedFiles is empty, then no files were uploaded. Otherwise, start processing!
		if(!sizeof($uploadedFiles)) return $results;

		// Move the uploaded files into thier new home and make the new file read-only
		foreach($uploadedFiles as $uploadedFile){
			$fileExt     = pathinfo($uploadedFile, PATHINFO_EXTENSION);
			$newFileUUID = self::newFileUUID();
			$newFilepath = self::getSaveDir('originals',$newFileUUID);
			$newFilename = "$newFilepath/$newFileUUID.$fileExt";
			$newFiles[$newFileUUID] = array(
				'filepath' => str_replace('-',DIRECTORY_SEPARATOR,$newFileUUID).DIRECTORY_SEPARATOR.$newFileUUID.'.'.$fileExt,
				'filename' => $uploadedFile,
				'created'  => time()
			);
			rename("$uploadBase/$uploadedFile", $newFilename);
			chmod($newFilename, 0444);
		}

		// Remove the uploads directory (now that we're done with it)
		rmdir($uploadBase);

		return array_merge($newFiles,$objectFiles);
	}

	public static function processObjectFiles($objectID, $objectField, $objectData=NULL){
		$results  = array();
		$saveBase = mfcs::config('savePath');
		$field    = forms::getField(mfcs::$engine->cleanGet['MYSQL']['formID'], $objectField);
		if(isset($objectData)){
			$objectFiles = $objectData[$objectField];
		}else{
			if(isnull($objectID)){
				$objectFiles = array();
			}else{
				$object      = objects::get($objectID);
				$objectFiles = decodeFields($objectData[$object['data'][$objectField]]);
			}
		}

		// If combine files is checked, read this image and add it to the combined object
		if(isset($field['combine']) && str2bool($field['combine'])){
			// Create us some temp working space
			$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
			mkdir($tmpDir,0777,TRUE);

			// Create the hocr file (if needed)
			if(!file_exists("$saveBase/.hocr")){
				if(!file_put_contents("$saveBase/.hocr", 'tessedit_create_hocr 1')){
					errorHandle::newError("Failed to create hocr file.",errorHandle::HIGH);
					return FALSE;
				}
			}

			// Build the temp array of files for our processing
			$files = array();
			foreach($objectFiles as $fileUUID => $fileData){
				if(!self::isUUID($fileUUID)) continue;
				$files[ $fileData['filepath'] ] = $fileData['filename'];
			}
			natsort($files);

			// Start combining!
			foreach($files as $filepath => $filename){
				$fileExt  = pathinfo($filename, PATHINFO_EXTENSION);
				$fileBase = basename($filename,".$fileExt");
				$originalFile = self::getSaveDir('originals',$filepath).DIRECTORY_SEPARATOR.basename($filepath);

				// perform hOCR on the original uploaded file which gets stored in combined as an HTML file
				$cmd = sprintf('tesseract %s %s -l eng %s 2>&1',
					escapeshellarg($originalFile),
					escapeshellarg($tmpDir.DIRECTORY_SEPARATOR.$filename),
					escapeshellarg("$saveBase/.hocr")
				);
				$output = shell_exec($cmd);

				// If a new-line char is in the output, assume it's an error
				if (FALSE !== strpos(trim($output), "\n")) {
					errorHandle::newError("Tesseract Output: ".$output,errorHandle::HIGH);
					return FALSE;
				}

				// Convert original uploaded file to jpg in preparation of final combine (if needed)
				if($fileExt != "jpg"){
					// Convert needed
					$tempFilename = $tmpDir.DIRECTORY_SEPARATOR.$fileBase.".jpg";
					$output = shell_exec(sprintf('convert %s %s 2>&1',
						escapeshellarg($originalFile),
						escapeshellarg($tempFilename)
					));
					if(!is_empty($output)){
						errorHandle::newError("Convert Output: ".$output,errorHandle::HIGH);
						return FALSE;
					}
				}else{
					// No convert needed - but we need to move the file into the right place
					$tempFilename = $tmpDir.DIRECTORY_SEPARATOR.$filename;
					copy($originalFile, $tempFilename);
				}

				$cmd = sprintf('hocr2pdf -i %s -s -o %s < %s 2>&1',
					escapeshellarg($tempFilename),
					escapeshellarg($tmpDir.DIRECTORY_SEPARATOR.$fileBase.".pdf"),
					escapeshellarg($tmpDir.DIRECTORY_SEPARATOR.$filename.".html")
				);
				$output = shell_exec($cmd);
				if (trim($output) !== 'Writing unmodified DCT buffer.') {
					if(FALSE !== strpos($output,'Warning:')){
						errorHandle::newError("hocr2pdf Warning: ".$output,errorHandle::LOW);
					}else{
						errorHandle::errorMsg("Failed to Create PDF: ".basename($filename,"jpg").".pdf");
						errorHandle::newError("hocr2pdf Error: ".$output,errorHandle::HIGH);
					}
				}
			}

			$combinedFileUUID = self::newFileUUID();
			$saveFilepath = self::getSaveDir('combined',$combinedFileUUID).DIRECTORY_SEPARATOR."combined.pdf";

			// Combine all PDF files in directory
			$output = shell_exec(sprintf('gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s -f %s 2>&1',
				$saveFilepath,
				$tmpDir.DIRECTORY_SEPARATOR."*.pdf"
			));
			if (!is_empty($output)) {
				errorHandle::errorMsg("Failed to combine PDFs into single PDF.");
				errorHandle::newError("GhostScript Output: ".$output,errorHandle::HIGH);
			}

			$results['combined'] = array(
				'filepath' => $saveFilepath,
				'filename' => basename($saveFilepath),
				'created'  => time()
			);

			// Lastly, we delete our temp working dir
			foreach(glob("$tmpDir/*.*") as $file){
				unlink($file);
			}
			rmdir($tmpDir);
		}

		// Convert uploaded files into some ofhter size/format/etc
		if (isset($field['convert']) && str2bool($field['convert'])) {
			foreach($objectFiles as $fileUUID => $fileData){
				if(!self::isUUID($fileUUID)) continue;

				$filename     = $fileData['filename'];
				$filepath     = $fileData['filepath'];
				$originalFile = self::getSaveDir('originals',$filepath).DIRECTORY_SEPARATOR.basename($filepath);
				$fileExt      = pathinfo($filename, PATHINFO_EXTENSION);
				$fileBase     = basename($filename,".$fileExt");
				$image        = new Imagick();
				$image->readImage($originalFile);

				// Convert format
				if(!empty($field['convertFormat'])) $image->setImageFormat($field['convertFormat']);

				// Add a border
				if (isset($field['border']) && str2bool($field['border'])) {
					// Resize the image first, taking into account the border width
					$image->scaleImage(
						($field['convertWidth']  - $field['borderWidth']  * 2),
						($field['convertHeight'] - $field['borderHeight'] * 2),
						TRUE
						);

					// Add the border
					$image->borderImage(
						$field['borderColor'],
						$field['borderWidth'],
						$field['borderHeight']
						);
				}
				else {
					// Resize without worrying about the border
					$image->scaleImage($field['convertWidth'], $field['convertHeight'], TRUE);
				}

				// Create a thumbnail
				if (isset($field['thumbnail']) && str2bool($field['thumbnail'])) {
					// Make a copy of the original
					$thumb = $image->clone();

					// Change the format
					$thumb->setImageFormat($field['thumbnailFormat']);

					// Scale to thumbnail size, constraining proportions
					$thumb->thumbnailImage(
						$field['thumbnailWidth'],
						$field['thumbnailHeight'],
						TRUE
					);

					// Store thumbnail
					if ($thumb->writeImage(self::getSaveDir('thumbs', $fileUUID).DIRECTORY_SEPARATOR.$fileUUID.".".strtolower($thumb->getImageFormat())) === FALSE) {
						errorHandle::errorMsg("Failed to create thumbnail: ".$filename);
					}
				}

				// Add a watermark
				if (isset($field['watermark']) && str2bool($field['watermark'])) {
					$image = self::addWatermark($image, $field);
				}

				// Store image
				$writeFilepath = self::getSaveDir('converted',$fileUUID).DIRECTORY_SEPARATOR.$fileUUID.".".strtolower($image->getImageFormat());
				$image->writeImages($writeFilepath, TRUE);
			}
		}

		// Create an OCR text file
		if (isset($field['ocr']) && str2bool($field['ocr'])) {
			// Include TesseractOCR class
			require_once 'class.tesseract_ocr.php';

			foreach($objectFiles as $fileUUID => $fileData){
				if(!self::isUUID($fileUUID)) continue;

				$filename = $fileData['filename'];
				$fileExt  = pathinfo($filename, PATHINFO_EXTENSION);
				$text     = TesseractOCR::recognize(self::getSaveDir('originals',$fileUUID).DIRECTORY_SEPARATOR.$fileUUID.'.'.$fileExt);
				$saveDir  = self::getSaveDir('ocr', $fileUUID).DIRECTORY_SEPARATOR.$fileUUID.".txt";
				if (file_put_contents($saveDir, $text) === FALSE) {
					errorHandle::errorMsg("Failed to create OCR text file: ".$filename);
					errorHandle::newError("Failed to create OCR file for ".self::getSaveDir('originals',$fileUUID).DIRECTORY_SEPARATOR.$filename,errorHandle::DEBUG);
				}
			}
		}

		if (isset($field['mp3']) && str2bool($field['mp3'])) {
			foreach($objectFiles as $fileUUID => $fileData){
				if(!self::isUUID($fileUUID)) continue;

				$fi = new finfo(FILEINFO_MIME);
				$mimeType = $fi->file($newFilepath, FILEINFO_MIME_TYPE);
				if(strpos($mimeType, 'audio/') !== FALSE){
					// @TODO: Perform audio processing here
				}
			}
		}

		return array_merge($results,$objectFiles);
	}
}