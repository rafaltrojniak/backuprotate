<?php
/**
 * file name  : Command/Build.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:26:23
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Builds backup directories
 **/
class Build implements \Command
{

	/**
	 * Builds directories for all buckets and pickupdir
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== Build\n";
		$dirs=$store->getDirs();
		$dirs['pickup']=$store->getPickup();
		$ret=0;

		foreach($dirs as $id=>$dir){
			echo "[$id]:";
			$path=$dir->getPath();
			if(!is_dir($path)){
				echo "\tC";
				if(!mkdir($path, 0777, true)){
					echo "! - failed to create";
					$ret++;
				}
			}
			echo "\n";
		}

		return $ret;
	}
}
