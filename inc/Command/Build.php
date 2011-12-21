<?php

namespace Command;

/**
 * Builds backup directories
 **/
class Build implements \Command
{
	
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
