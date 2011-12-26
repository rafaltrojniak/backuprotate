<?php

namespace Command;

/**
 * Transform checksum file from old to new one
 **/
class Transform implements \Command
{

	/**
	 * Path name of the directory
	 */
	private $path;

	public function __construct($path)
	{
		$this->path=$path;
	}

	function run(\BackupStore $store)
	{
		echo "== Transform\n";
		$fileInfo= new \SplFileInfo($this->path);
		$backup = \Backup::create($fileInfo);
		return !$backup->transform();
	}
}
