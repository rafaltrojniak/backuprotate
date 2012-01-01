<?php
/**
 * file name  : Command/Verify.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:23:54
 * copyright  :
 *
 * modifications:
 *
 */

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

	/**
	 * Backup for verification
	 */
	private $backup;

	/**
	 * Construct commands, based on sizeOnly flag
	 *
	 * @param $sizeOnly
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($sizeOnly, $backup)
	{
		$this->sizeOnly=$sizeOnly;
		$this->backup=$backup;
	}

	/**
	 * Verify backup using sumfile
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== Verify\n";
		if(is_string($this->backup)){
			$fileinfo = new \SplFileInfo($this->backup);
			$backup=\Backup::create($fileinfo);
			$backups=array($backup);
		}else{
			$pickup = $store->getPickup();
			$backups=$pickup->getBackups();
		}
		return $this->runVerify($backups,$store);
	}

	/**
	 * Runs verify on array of backups
	 *
	 * @param $backups
	 * @return boolean|int
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function runVerify($backups, $store)
	{
		$status=true;

		foreach($backups as $id=>$backup)
		{
			$ret=$backup->verify($this->sizeOnly);
			if($ret!==true){
				echo "\t!\t".$backup->getCreation()->format(\DateTime::ISO8601)."\t:$ret\n";
				$store->forgetBackup($backup);
				$status=-1;
			}else{
					echo "\t\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";
			}
		}
		return $status;
	}
}

