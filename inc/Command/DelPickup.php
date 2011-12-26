<?php
/**
 * file name  : Command/DelPickup.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:24:03
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Cleans pickups according to configuration
 **/
class DelPickup implements \Command
{

	/**
	 * Cleans pickup according by rules
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
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
