<?php
/**
 * file name  : Command/Rotate.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:23:45
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Rotates backups
 **/
class Rotate implements \Command
{

	/**
	 * Runs rotating (rotates backup from pickup to backupdirs)
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== List rotating\n";
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
