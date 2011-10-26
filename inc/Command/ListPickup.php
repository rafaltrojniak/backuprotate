<?php

namespace Command;

/**
 * 
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
		$pickup=$store->getPickup();
		foreach($pickup->getBackups() as $backup){
			echo $backup->getCreation()->format('Y-m-d H:i T')."\n";
		}
	}
}

