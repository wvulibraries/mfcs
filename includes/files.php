<?php

/**
 * Main MFCS object
 * @author David Gersting
 */
class files {

	private static function printTiff($filename,$mimeType) {
		$tmpName = tempnam(sys_get_temp_dir(), 'mfcs').".png";
		shell_exec(sprintf('convert %s %s 2>&1',
			escapeshellarg($filename),
			escapeshellarg($tmpName)));
		printf('<html><img src="data:%s;base64,%s" /></html>',
			$mimeType,
			base64_encode(file_get_contents($tmpName)));
		unlink($tmpName);

		return TRUE;
	}

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
				self::printTiff($filename,$mimeType);
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
				header("Content-type: $mimeType");
				echo $fileData;
				break;

			default:
				echo '[No preview available - Unknown file type]';
				break;
		}
	}

	public static function buildFilesPreview($objectID,$fieldName=NULL){

		if (objects::validID(TRUE,$objectID) === FALSE) {
			return FALSE;
		}

		if (($object = objects::get($objectID)) === FALSE) {
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
			$assetsID      = $object['data'][$field['name']];
			$assetsDir     = self::getSaveDir($assetsID);
			$originalsDir  = self::getSaveDir($assetsID,'originals');
			$originalFiles = scandir($originalsDir);
			$fileLinks     = array();

			// Needed to put the files in the right order for processing
			natsort($originalFiles);

			$fileLIs = array();
			foreach($originalFiles as $originalFile){
				if($originalFile{0} == '.') continue;

				$originalFile = $originalsDir.$originalFile;
				$_filename    = pathinfo($originalFile);
				$filename     = $_filename['filename'];
				$fileExt      = $_filename['extension'];
				$links        = array();

				$links['Original'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&file=%s&type=%s',
					localvars::get('siteRoot'),
					$objectID,
					$field['name'],
					basename($originalFile),
					'originals');

				if(str2bool($field['convert'])){
					$links['Converted'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&file=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$filename,
						'processed');
				}
				if(str2bool($field['thumbnail'])){
					$links['Thumbnail'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&file=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$filename,
						'thumbs');
				}
				if(str2bool($field['ocr'])){
					$links['OCR'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&file=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						$filename,
						'ocr');
				}
				if(str2bool($field['combine'])){
					$links['Combined'] = sprintf('%sincludes/fileViewer.php?objectID=%s&field=%s&type=%s',
						localvars::get('siteRoot'),
						$objectID,
						$field['name'],
						'combine');
				}

				// Build preview and download links
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
					basename($originalFile),
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
	 * @return string
	 * @author Scott Blake
	 **/
	public static function getBaseUploadPath() {
		return mfcs::config('uploadPath', sys_get_temp_dir().DIRECTORY_SEPARATOR.'mfcs');
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
	 * @return string
	 */
	public static function getSaveDir($assetsID, $type=NULL) {

		// Build the path
		$path = join(DIRECTORY_SEPARATOR,
			array(
				// If the type is "originals" use 'archivalPathMFCS' else use 'convertedPath' as the base path
				((strtolower($type) == 'originals')?mfcs::config('archivalPathMFCS'):mfcs::config('convertedPath')),
				(self::assetsIDToPath($assetsID)),
				// The full UID up to this point
				$assetsID,
				// Add the type to the path for the exports
				((strtolower($type) == 'originals' || isnull($type))?"":trim(strtolower($type)).DIRECTORY_SEPARATOR)
				)
			);

		// check to make sure that if the $path exists that it is a directory.
		if (file_exists($path) && !is_dir($path)) {
			return FALSE;
		}

		// Make sure the directory exists
		if (!is_dir($path)) {
			mkdir($path,0755,TRUE);
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

	public static function processObjectUploads($objectID,$uploadID){
		$uploadBase = files::getBaseUploadPath().DIRECTORY_SEPARATOR.$uploadID;
		$saveBase   = mfcs::config('convertedPath');

		// If the uploadPath dosen't exist, then no files were uploaded
		if(!is_dir($uploadBase)) return '';

		// Generate new assets UUID and make the directory (this should be done quickly to prevent race-conditions
		$assetsID          = self::newAssetsUUID();

		if (($originalsFilepath = self::getSaveDir($assetsID,'originals')) === FALSE) {
			return FALSE;
		}

		// Start looping through the uploads and move them to their new home
		$files = scandir($uploadBase);
		foreach($files as $filename){
			if($filename{0} == '.') continue;

			// Clean the filename
			$cleanedFilename = preg_replace('/[^a-z0-9-_\.]/i','',$filename);
			$newFilename = "$originalsFilepath/$cleanedFilename";

			// Move the uploaded files into thier new home and make the new file read-only
			rename("$uploadBase/$filename", $newFilename);
			chmod($newFilename, 0444);
		}

		// Remove the uploads directory (now that we're done with it) and lock-down the originals dir
		rmdir($uploadBase);
		chmod($originalsFilepath, 0555);

		// Return the assetsID
		return $assetsID;
	}

	public static function processObjectFiles($assetsID, $options){

		$saveBase          = mfcs::config('convertedPath');
		$assetsPath        = self::getSaveDir($assetsID);
		$originalsFilepath = self::getSaveDir($assetsID,'originals');
		$originalFiles     = scandir($originalsFilepath);

		// Needed to put the files in the right order for processing
		natsort($originalFiles);

		try{
			// If combine files is checked, read this image and add it to the combined object
			if(isset($options['combine']) && str2bool($options['combine'])){
				try{
					// Create us some temp working space
					$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
					mkdir($tmpDir,0777,TRUE);

					// Create the hocr file (if needed)
					if(!file_exists("$saveBase/hocr.cfg")){
						if(!file_put_contents("$saveBase/hocr.cfg", 'tessedit_create_hocr 1')){
							errorHandle::newError("Failed to create hocr file.",errorHandle::HIGH);
							return FALSE;
						}
					}

					foreach($originalFiles as $filename){
						if($filename{0} == '.') continue;

						// Figure some stuff out about the file
						$originalFile = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
						$_filename    = pathinfo($originalFile);
						$filename     = $_filename['filename'];
						$fileExt      = $_filename['extension'];

						// perform hOCR on the original uploaded file which gets stored in combined as an HTML file
						$_exec = shell_exec(sprintf('tesseract %s %s -l eng %s 2>&1',
							escapeshellarg($originalFile), // input.ext
							escapeshellarg($tmpDir.DIRECTORY_SEPARATOR.$filename), // output.html
							escapeshellarg("$saveBase/hocr.cfg") // hocr config file
							));

						// If a new-line char is in the output, assume it's an error
						// Tesseract failed, let's normalize the image and try again
						if (strpos(trim($_exec), "\n") !== FALSE) {
							errorHandle::warningMsg("Unable to process OCR for ".basename($originalFile).". Continuing&hellip;");

							// Ensure HTML file exists
							touch($tmpDir.DIRECTORY_SEPARATOR.$filename.".html");
						}

						// Create an OCR'd pdf of the file
						$_exec = shell_exec(sprintf('hocr2pdf -i %s -s -o %s < %s 2>&1',
							escapeshellarg($originalFile), // input.ext
							escapeshellarg($tmpDir.DIRECTORY_SEPARATOR.$filename.".pdf"), // output.pdf
							escapeshellarg($tmpDir.DIRECTORY_SEPARATOR.$filename.".html") // input.html
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

						// We're done with this file, delete it
						unlink($tmpDir.DIRECTORY_SEPARATOR.$filename.".html");
					}

					// Combine all PDF files in directory
					$_exec = shell_exec(sprintf('gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s -f %s 2>&1',
						self::getSaveDir($assetsID,'combine')."combined.pdf",
						$tmpDir.DIRECTORY_SEPARATOR."*.pdf"
					));
					if (!is_empty($_exec)) {
						errorHandle::errorMsg("Failed to combine PDFs into single PDF.");
						throw new Exception("GhostScript Error: ".$_exec);
					}

					// Lastly, we delete our temp working dir (always nice to cleanup after yourself)
					foreach(glob("$tmpDir/*.*") as $file){
						unlink($file);
					}
					rmdir($tmpDir);
				}catch(Exception $e){
					// We need to delete our working dir
					if(isset($tmpDir) and is_dir($tmpDir)){
						foreach(glob("$tmpDir/*.*") as $file){
							unlink($file);
						}
						rmdir($tmpDir);
					}
					throw new Exception($e->getMessage(), $e->getCode(), $e);
				}
			}

			// Convert uploaded files into some ofhter size/format/etc
			if(isset($options['convert']) && str2bool($options['convert'])){
				foreach($originalFiles as $filename){
					if($filename{0} == '.') continue;

					$originalFile = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
					$_filename    = pathinfo($originalFile);
					$filename     = $_filename['filename'];
					$fileExt      = $_filename['extension'];
					$image        = new Imagick();
					$image->readImage($originalFile);

					// Convert format?
					if(!empty($options['convertFormat'])) $image->setImageFormat($options['convertFormat']);

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

					// Create a thumbnail
					if(isset($options['thumbnail']) && str2bool($options['thumbnail'])){
						// Make a copy of the original
						$thumb = $image->clone();

						// Change the format
						$thumb->setImageFormat($options['thumbnailFormat']);

						// Scale to thumbnail size, constraining proportions
						if ($options['thumbnailWidth'] > 0 || $options['thumbnailHeight'] > 0) {
							$thumb->thumbnailImage(
								$options['thumbnailWidth'],
								$options['thumbnailHeight'],
								TRUE
							);
						}

						// Store thumbnail
						if($thumb->writeImage(self::getSaveDir($assetsID,'thumbs').$filename.'.'.strtolower($thumb->getImageFormat())) === FALSE){
							throw new Exception("Failed to create thumbnail: ".$filename);
						}
					}

					// Add a watermark
					if(isset($options['watermark']) && str2bool($options['watermark'])){
						$image = self::addWatermark($image, $options);
					}

					// Store image
					if($image->writeImage(self::getSaveDir($assetsID,'processed').$filename.'.'.strtolower($image->getImageFormat())) === FALSE){
						throw new Exception("Failed to create processed image: ".$filename);
					}
				}
			}

			// Create an OCR text file
			if (isset($options['ocr']) && str2bool($options['ocr'])) {
				// Include TesseractOCR class
				require_once 'class.tesseract_ocr.php';

				foreach($originalFiles as $filename){
					if($filename{0} == '.') continue;

					$originalFile = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
					$_filename    = pathinfo($originalFile);
					$filename     = $_filename['filename'];
					$fileExt      = $_filename['extension'];

					$text = TesseractOCR::recognize($originalFile);
					if (file_put_contents(self::getSaveDir($assetsID,'ocr').DIRECTORY_SEPARATOR."$filename.txt", $text) === FALSE) {
						errorHandle::errorMsg("Failed to create OCR text file: ".$filename);
						throw new Exception("Failed to create OCR file for $filename");
					}
				}
			}

			if (isset($options['mp3']) && str2bool($options['mp3'])) {
				foreach($originalFiles as $filename){
					if($filename{0} == '.') continue;

					$originalFile = $originalsFilepath.DIRECTORY_SEPARATOR.$filename;
					$_filename    = pathinfo($originalFile);
					$filename     = $_filename['filename'];
					$fileExt      = $_filename['extension'];

					$fi = new finfo(FILEINFO_MIME);
					$mimeType = $fi->file($originalFile, FILEINFO_MIME_TYPE);
					if(strpos($mimeType, 'audio/') !== FALSE){
						// @TODO: Perform audio processing here
					}
				}
			}

		}catch(Exception $e){
			errorHandle::newError(__METHOD__."() - {$e->getMessage()} {$e->getLine()}:{$e->getFile()}", errorHandle::HIGH);
			return FALSE;
		}
	}
}