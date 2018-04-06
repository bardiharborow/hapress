<?php

class Snapshot_Helper_Zip_Archive extends Snapshot_Helper_Zip_Abstract {

	public function initialize () {
		$this->_zip = new ZipArchive;
	}

	public function add ($files=array(), $relative_path=false) {
		if (!is_array($files)) $files = array($files);
		if (empty($files)) return false;

		$flags = null;
		if (!file_exists($this->_path)) $flags = ZipArchive::CREATE;

		$handle = $this->_zip->open($this->_path, $flags);
		if (!$handle) return false;

		$limit = 200;
		$count = 0;

		foreach ($files as $file) {
			$count++;
			$file = wp_normalize_path($file);
			if (!file_exists($file)) continue;
			$this->_zip->addFile($file, $this->_to_root_relative($file, $relative_path));
			/*
				Apparently, there is a limit to the number of files that can be added at once.
				So we are setting a limit of 200 files per add session.
				Then we close the archive and re-open.
			*/
			if ($count >= $limit) {
				$this->_zip->close();
				$this->_zip->open($this->_path, $flags);
				$count = 0;
			}
		}

		return $this->_zip->close();
	}

	public function has ($path) {
		$path = $this->_to_root_relative($path);
		if (empty($path)) return false;

		$handle = $this->_zip->open($this->_path);
		if (!$handle) return false;

		$status = $this->_zip->locateName($path);
		$this->_zip->close();

		return false === $status ? false : true;
	}

	public function extract ($destination) {
		if (empty($destination)) return false;

		$destination = wp_normalize_path($destination);
		if (empty($destination) || !file_exists($destination)) return false;

		$handle = $this->_zip->open($this->_path);
		if (!$handle) return false;

		$status = $this->_zip->extractTo($destination);
		$this->_zip->close();

		return $status;
	}

	public function extract_specific ($destination, $files) {
		if (empty($destination)) return false;

		if (empty($files)) return false;
		if (!is_array($files)) return false;

		$destination = wp_normalize_path($destination);
		if (empty($destination) || !file_exists($destination)) return false;

		$handle = $this->_zip->open($this->_path);
		if (!$handle) return false;

		$status = $this->_zip->extractTo($destination, $files);
		$this->_zip->close();

		return $status;
	}
}