<?php

class qqFileUploader {
	public $allowedExtensions = array();
	public $sizeLimit = null;
	public $inputName = 'qqfile';
	public $chunksFolder = 'chunks';
	public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg
	public $chunksExpireIn = 604800; // One week
	protected $uploadName;

	public function __construct() {
		$this->sizeLimit = $this->toBytes(ini_get('upload_max_filesize'));
	}

	public function getName() {
		if (isset($_REQUEST['qqfilename'])) {
			return $_REQUEST['qqfilename'];
		}

		if (isset($_FILES[$this->inputName])) {
			return $_FILES[$this->inputName]['name'];
		}
	}

	public function getUploadName() {
		return $this->uploadName;
	}

	public function handleUpload($uploadDirectory, $name = null) {
		if (is_writable($this->chunksFolder) && 1 == mt_rand(1, 1 / $this->chunksCleanupProbability)) {
			$this->cleanupChunks();
		}

		if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
			$size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
			return ['error' => "Server error. Increase post_max_size and upload_max_filesize to " . $size];
		}

		$isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		$folderInaccessible = ($isWin) ? !is_writable($uploadDirectory) : (!is_writable($uploadDirectory) && !is_executable($uploadDirectory));

		if ($folderInaccessible) {
			return ['error' => "Server error. Uploads directory isn't writable" . ((!$isWin) ? " or executable." : ".")];
		}

		if (!isset($_SERVER['CONTENT_TYPE'])) {
			return ['error' => "No files were uploaded."];
		} elseif (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/') !== 0) {
			return ['error' => "Server error. Not a multipart request. Please set forceMultipart to default value (true)."];
		}

		$file = $_FILES[$this->inputName];
		$size = $file['size'];

		if ($name === null) {
			$name = $this->getName();
		}

		if ($name === null || $name === '') {
			return array('error' => 'File name empty.');
		}

		if ($size == 0) {
			return array('error' => 'File is empty.');
		}

		if ($size > $this->sizeLimit) {
			return array('error' => 'File is too large.');
		}

		$pathinfo = pathinfo($name);
		$ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

		if ($this->allowedExtensions && !in_array(strtolower($ext), array_map("strtolower", $this->allowedExtensions))) {
			$these = implode(', ', $this->allowedExtensions);
			return ['error' => 'File has an invalid extension, it should be one of ' . $these . '.'];
		}

		$totalParts = isset($_REQUEST['qqtotalparts']) ? (int)$_REQUEST['qqtotalparts'] : 1;

		if ($totalParts > 1) {
			$chunksFolder = $this->chunksFolder;
			$partIndex = (int)$_REQUEST['qqpartindex'];
			$uuid = $_REQUEST['qquuid'];

			if (!is_writable($chunksFolder) && !is_executable($uploadDirectory)) {
				return ['error' => "Server error. Chunks directory isn't writable or executable."];
			}

			$targetFolder = $this->chunksFolder . DIRECTORY_SEPARATOR . $uuid;

			if (!file_exists($targetFolder)) {
				mkdir($targetFolder);
			}

			$target = $targetFolder . '/' . $partIndex;
			$success = move_uploaded_file($_FILES[$this->inputName]['tmp_name'], $target);

			if ($success && ($totalParts - 1 == $partIndex)) {
				$target = $this->getUniqueTargetPath($uploadDirectory, $name);
				$this->uploadName = basename($target);
				$target = fopen($target, 'wb');

				for ($i = 0; $i < $totalParts; $i++) {
					$chunk = fopen($targetFolder . DIRECTORY_SEPARATOR . $i, "rb");
					stream_copy_to_stream($chunk, $target);
					fclose($chunk);
				}

				fclose($target);

				for ($i = 0; $i < $totalParts; $i++) {
					unlink($targetFolder . DIRECTORY_SEPARATOR . $i);
				}

				rmdir($targetFolder);

				return array("success" => true);
			}

			return array("success" => true);
		} else {
			$target = $this->getUniqueTargetPath($uploadDirectory, $name);

			if ($target) {
				$this->uploadName = basename($target);

				if (move_uploaded_file($file['tmp_name'], $target)) {
					return array('success' => true);
				}
			}

			return array('error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered');
		}
	}

	protected function getUniqueTargetPath($uploadDirectory, $filename) {
		if (function_exists('sem_acquire')) {
			$lock = sem_get(ftok(__FILE__, 'u'));
			sem_acquire($lock);
		}

		$pathinfo = pathinfo($filename);
		$base = $pathinfo['filename'];
		$ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
		$ext = $ext == '' ? $ext : '.' . $ext;

		$unique = $base;
		$suffix = 0;

		while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext)) {
			$suffix += rand(1, 999);
			$unique = $base . '-' . $suffix;
		}

		$result = $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;

		if (!touch($result)) {
			$result = false;
		}

		if (function_exists('sem_acquire')) {
			sem_release($lock);
		}

		return $result;
	}

	protected function cleanupChunks() {
		foreach (scandir($this->chunksFolder) as $item) {
			if ($item == "." || $item == "..") {
				continue;
			}

			$path = $this->chunksFolder . DIRECTORY_SEPARATOR . $item;

			if (!is_dir($path)) {
				continue;
			}

			if (time() - filemtime($path) > $this->chunksExpireIn) {
				$this->removeDir($path);
			}
		}
	}

	protected function removeDir($dir) {
		foreach (scandir($dir) as $item) {
			if ($item == "." || $item == "..") {
				continue;
			}

			unlink($dir . DIRECTORY_SEPARATOR . $item);
		}

		rmdir($dir);
	}

	protected function toBytes($str) {
		$val = trim($str);
		$last = strtolower($str[strlen($str) - 1]);

		switch ($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
}
