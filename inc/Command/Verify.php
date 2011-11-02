<?php

namespace Command;

/**
 * Fill checksums on backup
 **/
class Verify implements \Command
{

	/** 
	 * Flag if only size should be checked 
	 */
	private $sizeOnly;

	public function __construct($sizeOnly)
	{
		$this->sizeOnly=$sizeOnly;
	}

	function run(\BackupStore $store)
	{
		echo "== Verify\n";
		$pickup = $store->getPickup();
		$backups=$pickup->getBackups();

		foreach($backups as $id=>$backup)
		{
			$ret=$backup->verify($this->sizeOnly);
			if($ret!==true){
				$pickup->forgetBackup($backup);
					echo "\t!\t".$backup->getCreation()->format(\DateTime::ISO8601)."\t:$ret\n";
			}else{
					echo "\t\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";
			}
		}
	}
}

