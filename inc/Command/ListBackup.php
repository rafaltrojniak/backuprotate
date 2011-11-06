<?php

namespace Command;

/**
 * 
 **/
class ListBackup implements \Command
{

	private $bucket;
	
	function __construct($bucket)
	{
		$this->bucket=$bucket;
	}

	function run(\BackupStore $store)
	{
		if($this->bucket!==false){
			$dir=$store->getDir($this->bucket);
			$this->listBackup($dir);
		}else{
			echo "== List backups\n";
			$dirs=$store->getDirs();
			foreach($dirs as $id=>$dir){
				echo "[$id]:\n";
				$this->listBackup($dir);
			}
		}
	}

	public function listBackup(\BackupDir $dir)
	{
		foreach($dir->getBackups() as $backup){
			echo $backup->getCreation()->format(\DateTime::ISO8601)."\n";

		}
	}
}
