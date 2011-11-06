<?php

namespace Command;

/**
 * Cleans pickups according to configuration
 **/
class DelPickup implements \Command
{
	
	function run(\BackupStore $store)
	{
		echo "== Delete old pickup\n";
		$pickup=$store->getPickup();
		try{
			$toClean=$pickup->clean();
			foreach($toClean as $backup){
				echo "\t-\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";

			}
		}catch(\RuntimeException $e){
			echo 'Got exception:'.$e->getMessage()."\n";
		}
	}
}
