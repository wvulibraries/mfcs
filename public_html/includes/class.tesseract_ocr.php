<?php
// https://github.com/thiagoalessio/tesseract-ocr-for-php
class TesseractOCR {

	// refactored class with copilot 2024-04-25

	public static function recognize($originalImage) {
		$tifImage       = self::convertImageToTif($originalImage);
		$configFile     = self::generateConfigFile(func_get_args());
		$outputFile     = self::executeTesseract($tifImage, $configFile);
		$recognizedText = self::readOutputFile($outputFile);
		self::removeTempFiles($tifImage, $outputFile, $configFile);
		return $recognizedText;
	}

	private static function convertImageToTif($originalImage) {
		$tifImage = mfcs::config('mfcstmp').'/tesseract-ocr-tif-'.rand().'.tif';
		exec("convert -colorspace gray +matte $originalImage $tifImage");
		return $tifImage;
	}

	private static function generateConfigFile($arguments) {
		$configFile = mfcs::config('mfcstmp').'/tesseract-ocr-config-'.rand().'.conf';
		exec("touch $configFile");
		$whitelist = self::generateWhitelist($arguments);
		if(!empty($whitelist)) {
			$fp = fopen($configFile, 'w');
			fwrite($fp, "tessedit_char_whitelist $whitelist");
			fclose($fp);
		}
		return $configFile;
	}

	private static function generateWhitelist($arguments) {
		array_shift($arguments); //first element is the image path
		$whitelist = '';
		foreach($arguments as $chars) $whitelist.= join('', (array)$chars);
		return $whitelist;
	}

	private static function executeTesseract($tifImage, $configFile) {
		$outputFile = mfcs::config('mfcstmp').'/tesseract-ocr-output-'.rand();
		exec("tesseract $tifImage $outputFile nobatch $configFile 2> /dev/null");
		return $outputFile.'.txt'; //tesseract appends txt extension to output file
	}

	private static function readOutputFile($outputFile) {
		return trim(file_get_contents($outputFile));
	}

	private static function removeTempFiles() { array_map("unlink", func_get_args()); }
}
?>