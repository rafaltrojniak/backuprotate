<?php

namespace Command;

/**
 * Fill checksums on backup
 **/
class Werify implements \Command
{

	function run(\BackupStore $store)
	{
		echo "== Werify\n";
		$pickup = $store->getPickup();
		$backups=$pickup->getBackups();

		foreach($backups as $id=>$backup)
		{
			$ret=$backup->werify();
			if($ret!==true){
				$pickup->forgetBackup($backup);
					echo "\t!\t".$backup->getCreation()->format(\DateTime::ISO8601)."\t:$ret\n";
			}else{
					echo "\t\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";
			}
		}
	}
}

