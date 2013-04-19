<?php

/**
 * Main MFCS object
 * @author David Gersting
 */
class files {
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

		$savePath   = mfcs::config('savePath');
		$newFileSubpath = implode(DIRECTORY_SEPARATOR, explode('-', $fileUUID));
		return $savePath.DIRECTORY_SEPARATOR.trim(strtolower($type)).DIRECTORY_SEPARATOR.$newFileSubpath;
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
		// Loop until no file is found with generated UUID
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
			$testFilename = self::getSaveDir('originals',$uuid).DIRECTORY_SEPARATOR.$uuid.".*";
		}while(sizeof(glob($testFilename)));
		// Return the valid uuid
		return $uuid;
	}

	/**
	 * Performs necessary conversions, thumbnails, etc.
	 *
	 * @param array $field
	 * @param string $uploadID
	 * @return bool|array
	 * @author Scott Blake
	 **/
	public static function processUploads($field,$uploadID) {
		$engine     = EngineAPI::singleton();
		$results    = array();
		$uploadPath = files::getBaseUploadPath().DIRECTORY_SEPARATOR.$uploadID;
		$savePath   = mfcs::config('savePath');
		$files      = scandir($uploadPath);

		// Sort the files in 'natural order' and then start looping!
		natsort($files);
		foreach ($files as $filename) {
			// Skip hidden stuff
			if($filename{0} == '.') continue;

			// Figure out the details of the file we're working with
			$fileUUID     = files::newFileUUID();
			$fileExt      = pathinfo($filename, PATHINFO_EXTENSION);
			$newFilename  = strtolower("$fileUUID.$fileExt");
			$results[]    = array(
				'originalName' => $filename,
				'systemName'   => $newFilename
			);
			$origFilepath = $uploadPath.DIRECTORY_SEPARATOR.$filename;
			$newFilepath  = self::getSaveDir('originals',$fileUUID).DIRECTORY_SEPARATOR.strtolower($newFilename);

			// Move the file to it's now (originals) home and save it's file extension
			if(!is_dir(self::getSaveDir('originals',$fileUUID))) mkdir(self::getSaveDir('originals',$fileUUID), 0777, TRUE);
			rename($origFilepath, $newFilepath);

			// Ensure this file is an image before image specific processing
			if (getimagesize($newFilepath) !== FALSE) {
				// If combine files is checked, read this image and add it to the combined object
				if (isset($field['combine']) && str2bool($field['combine'])) {
					// Create the hocr file
					$output = file_put_contents(
						$savePath.DIRECTORY_SEPARATOR."hocr",
						"tessedit_create_hocr 1"
					);

					if ($output === FALSE) {
						errorHandle::newError("Failed to create hocr file.",errorHandle::HIGH);
						return FALSE;
					}

					// perform hOCR on the original uploaded file which gets stored in combined as an HTML file
					$output = shell_exec(sprintf('tesseract %s %s -l eng %s 2>&1',
						escapeshellarg($newFilepath),
						escapeshellarg(self::getSaveDir('combined', $fileUUID).DIRECTORY_SEPARATOR.basename($filename,".$fileExt")),
						escapeshellarg($savePath.DIRECTORY_SEPARATOR."hocr")
					));

					if (trim($output) !== 'Tesseract Open Source OCR Engine with Leptonica') {
						errorHandle::newError("Tesseract Output: ".$output,errorHandle::HIGH);
						return FALSE;
					}

					// Convert original uploaded file to jpg in preparation of final combine
					$output = shell_exec(sprintf('convert %s %s 2>&1',
						escapeshellarg($newFilepath),
						escapeshellarg(self::getSaveDir('combined', $fileUUID).DIRECTORY_SEPARATOR.basename($filename,".$fileExt").".jpg")
					));

					if (!is_empty($output)) {
						errorHandle::newError("Convert Output: ".$output,errorHandle::HIGH);
						return FALSE;
					}
				}

				// Convert uploaded files into some ofhter size/format/etc
				if (isset($field['convert']) && str2bool($field['convert'])) {
					$image = new Imagick();
					$image->readImage($newFilepath);

					// Convert format
					$image->setImageFormat($field['convertFormat']);

					// Resize image
					$image->scaleImage($field['convertWidth'], $field['convertHeight'], TRUE);

					// Add a border
					if (isset($field['border']) && str2bool($field['border'])) {
						$image->borderImage(
							$field['borderColor'],
							$field['borderWidth'],
							$field['borderHeight']
						);
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
						if ($thumb->writeImage(self::getSaveDir('thumbs', $fileUUID).DIRECTORY_SEPARATOR.basename($filename,".$fileExt").".".strtolower($thumb->getImageFormat())) === FALSE) {
							errorHandle::errorMsg("Failed to create thumbnail: ".$filename);
						}
					}

					// Add a watermark
					if (isset($field['watermark']) && str2bool($field['watermark'])) {
						$fh = fopen($field['watermarkImage'], "rb");

						$watermark = new Imagick();
						$watermark->readImageFile($fh); // Full URL
						// $watermark->readImage("/path/to/file.png"); // Uses path relative to current file

						// Resize the watermark
						$watermark->scaleImage($image->getImageWidth()/1.5, $image->getImageHeight()/1.5, TRUE);

						list($positionHeight,$positionWidth) = explode("|",$field['watermarkLocation']);

						// calculate the position
						switch ($positionHeight) {
							case 'top':
								$y = 0;
								break;

							case 'bottom':
								$y = $image->getImageHeight() - $watermark->getImageHeight();
								break;

							case 'middle':
							default:
								$y = ($image->getImageHeight() - $watermark->getImageHeight()) / 2;
								break;
						}

						switch ($positionWidth) {
							case 'left':
								$x = 0;
								break;

							case 'right':
								$x = $image->getImageWidth() - $watermark->getImageWidth();
								break;

							case 'center':
							default:
								$x = ($image->getImageWidth() - $watermark->getImageWidth()) / 2;
								break;
						}

						// Add watermark to image
						if ($image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y) === FALSE) {
							errorHandle::errorMsg("Failed to create watermark: ".$filename);
						}
					}

					// Store image
					if ($image->writeImages(getUploadDir('converted',$uploadID).DIRECTORY_SEPARATOR.basename($filename,".$fileExt").".".strtolower($image->getImageFormat()), TRUE) === FALSE) {
						errorHandle::errorMsg("Failed to create image: ".$filename);
					}
				}

				// Create an OCR text file
				if (isset($field['ocr']) && str2bool($field['ocr'])) {
					// Include TesseractOCR class
					require_once 'class.tesseract_ocr.php';

					$text = TesseractOCR::recognize(getUploadDir('originals',$uploadID).DIRECTORY_SEPARATOR.$filename);

					if (file_put_contents(getUploadDir('ocr',$uploadID).DIRECTORY_SEPARATOR.basename($filename,".$fileExt").".txt", $text) === FALSE) {
						errorHandle::errorMsg("Failed to create OCR text file: ".$filename);
						errorHandle::newError("Failed to create OCR file for ".getUploadDir('originals',$uploadID).DIRECTORY_SEPARATOR.$filename,errorHandle::DEBUG);
					}
				}
			}

			// Ensure this file is an audio file before audio specific processing
			$fi = new finfo(FILEINFO_MIME);
			$mimeType = $fi->file($newFilepath, FILEINFO_MIME_TYPE);
			if(strpos($mimeType, 'audio/') !== FALSE){
				// Perform audio processing here
			}
		}

		// Write the combined PDF to disk
		if (isset($field['combine']) && str2bool($field['combine'])) {
			$combinedDir = self::getSaveDir('combined', $fileUUID).DIRECTORY_SEPARATOR;

			// Combine HTML and JPG files into individual PDF files
			foreach (glob($combinedDir."*.jpg") as $file) {
				$output = shell_exec(sprintf('hocr2pdf -i %s -s -o %s < %s 2>&1',
					escapeshellarg($file),
					escapeshellarg($combinedDir.basename($file,"jpg")."pdf"),
					escapeshellarg($combinedDir.basename($file,"jpg")."html")
				));

				if (trim($output) !== 'Writing unmodified DCT buffer.') {
					errorHandle::errorMsg("Failed to Create PDF: ".basename($file,"jpg")."pdf");
					errorHandle::newError("hocr2pdf Output: ".$output,errorHandle::HIGH);
				}
			}

			// Combine all PDF files in directory
			$output = shell_exec(sprintf('gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s -f %s 2>&1',
				$combinedDir."combined.pdf",
				$combinedDir."*.pdf"
			));

			if (!is_empty($output)) {
				errorHandle::errorMsg("Failed to combine PDFs into single PDF.");
				errorHandle::newError("GhostScript Output: ".$output,errorHandle::HIGH);
			}

			// Delete all the files except "combined.pdf"
			foreach(glob($combinedDir."*") AS $file) {
				if ($file !== $combinedDir."combined.pdf") {
					unlink($file);
				}
			}
		}
		return $results;
	}
}