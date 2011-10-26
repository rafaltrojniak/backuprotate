<?php

namespace Command;

/**
 * 
 **/
class ListBackup implements \Command
{
	
	function __construct()
	{
	}

	function run(\BackupStore $store)
	{
		$dirs=$store->getDirs();
		foreach($dirs as $id=>$dir){
			echo "[$id]:\n";
			foreach($dir->getBackups() as $backup){
				echo "\t".$backup->getCreated()->format(DateTime::ISO8601)."\n";

			}
		}
	}
}