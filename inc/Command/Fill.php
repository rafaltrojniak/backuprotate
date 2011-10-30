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

	/** 
	 * Name of file containing checksums 
	 */
	private $sumName="checksums";

	/** 
	 * Array of names to ignore during generation 
	 */
	static private $ignore=array(
		"checksums",
	);

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
		$dir = new \DirectoryIterator($this->path);
		$sums=array();
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$name=$fileinfo->getFilename();
				if(in_array($name, self::$ignore)){
					continue;
				}
				$path=$fileinfo->getRealPath();
				$size=filesize($path);
				$sum=sha1_file($path);
				$sums[]=$sum.' '.$size.' '.$name;
			}
		}
		$sumfile=implode("\n",$sums);
		file_put_contents($this->path.'/'.$this->sumName, $sumfile);
	}
}
