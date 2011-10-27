<?php

namespace Command;

/**
 * Rotates backups
 **/
class Rotate implements \Command
{
	
	function run(\BackupStore $store)
	{
		$dirs=$store->getDirs();
		$pickup=$store->getPickup();
		foreach($dirs as $id=>$dir){
			echo "[$id]:\n";
			$pick=$dir->pickup($pickup);
			foreach($pick as $backup){
				echo "\t+\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";

			}
		}
	}
}
