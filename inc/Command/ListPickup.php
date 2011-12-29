<?php
/**
 * file name  : Command/ListPickup.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:27:33
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * command lists pickupdir contents
 **/
class ListPickup implements \Command
{

	/**
	 * Run the command
	 *
	 * @param \BackupStore $store
	 * @return int Status of the command (to shell)
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== List pickups\n";
		$pickup=$store->getPickup();
		foreach($pickup->getBackups() as $backup){
			echo $backup->getCreation()->format('Y-m-d H:i T')."\n";
		}
		return true;
	}
}

