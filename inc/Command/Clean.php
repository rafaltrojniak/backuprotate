<?php

namespace Command;

/**
 * Cleans backups calculated by cleaners
 **/
class Clean implements \Command
{
	
	function run(\BackupStore $store)
	{
		$dirs=$store->getDirs();
		foreach($dirs as $id=>$dir){
			echo "[$id]:\n";
			$toClean=$dir->clean();
			foreach($toClean as $backup){
				echo "\t-\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";

			}
		}
	}
}
