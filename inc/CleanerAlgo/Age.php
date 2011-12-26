<?php
/**
 * file name  : CleanerAlgo/Age.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:27:51
 * copyright  :
 *
 * modifications:
 *
 */

namespace CleanerAlgo;

/**
 * Cleans all backups that are over supplied age
 * (current age is counted from the newest backup)
 **/
class Age implements \CleanerAlgo
{

	/**
	 * Age limit
	 */
	public $age;

	/**
	 * Configures new algorithm
	 *
	 * @param Array $config Configuration parameters
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
		$this->age= new \DateInterval($config['age']);
	}

	/**
	 * Runs cleaner algorithm on the backupdir
	 *
	 * @param Backupdir $backupDir
	 * @return Array List of backupdirs for cleanning
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function clean(\Backupdir $backupDir)
	{
		$newest=$backupDir->getNewestBackup();
		if(is_null($newest)){
			return array();
		}
		// Get minimal date from the backup
		$limit = clone $newest->getCreation();
		$limit->sub($this->age);

		$toClean=array();
		foreach($backupDir->getBackups() as $cur){
			$stamp=$cur->getCreation();
			if( $stamp->getTimestamp()< $limit->getTimestamp()) {
				$toClean[]=$cur;
			}
		}
		return $toClean;
	}
}
