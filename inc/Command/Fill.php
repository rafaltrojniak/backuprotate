<?php

namespace Command;

/**
 * Fill checksums on backup
 **/
class Fill implements \Command
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
		$fileInfo= new \SplFileInfo($this->path);
		$backup = \Backup::create($fileInfo);
		return !$backup->fill();
	}
}
