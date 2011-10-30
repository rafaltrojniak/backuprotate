<?php

namespace Command;

/**
 * Fill checksums on backup
 **/
class Werify implements \Command
{

	function run(\BackupStore $store)
	{
		$pickup = $store->getPickup();
		$backups=$pickup->getBackups();

		foreach($backups as $backup)
		{
			if(!$backup->werify()){
				$pickup->forgetBackup($backup);
			}
		}
	}
}

