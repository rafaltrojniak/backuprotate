<?php

namespace Command;

/**
 * 
 **/
class ListBackup implements \Command
{
	
	private $backup;
	function __construct($backup)
	{
		$this->backup=$backup;
	}

	function run($config)
	{
		$dir=new \BackupDir($config['pickupDir']);
		var_dump($dir->getList());
	}
}
