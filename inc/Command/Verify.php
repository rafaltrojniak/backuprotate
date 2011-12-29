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
	 * Construct commands, based on sizeOnly flag
	 *
	 * @param $sizeOnly
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($sizeOnly)
	{
		$this->sizeOnly=$sizeOnly;
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
		$pickup = $store->getPickup();
		$backups=$pickup->getBackups();
		$status=true;

		foreach($backups as $id=>$backup)
		{
			$ret=$backup->verify($this->sizeOnly);
			if($ret!==true){
				$pickup->forgetBackup($backup);
				echo "\t!\t".$backup->getCreation()->format(\DateTime::ISO8601)."\t:$ret\n";
				$status=-1;
			}else{
					echo "\t\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";
			}
		}
		return $status;
	}
}

