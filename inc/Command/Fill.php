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
		if(!is_dir($this->path)){
			throw new \RuntimeException('Supplied argument "'.addslashes($this->path).'"'.
				' is not a directory');
		}
		$fileInfo= new \SplFileInfo($this->path);
		$backup = \Backup::create($fileInfo);
		$backup->fill();
	}
}
