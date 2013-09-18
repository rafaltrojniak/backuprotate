<?php
/**
 * file name  : CleanerAlgo/Count.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:28:33
 * copyright  :
 *
 * modifications:
 *
 */

namespace CleanerAlgo;

/**
 * Cleans all backups over supplied count
 **/
class Count implements \CleanerAlgo
{

	/**
	 * Count of backups allowed to preserve
	 */
	public $count;

	/**
	 * Configures new algorithm
	 *
	 * @param Array $config Configuration parameters
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
		if(!array_key_exists('count',$config))
			throw new ConfigurationException('Count not set');
		$this->count=$config['count'];
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
		$backups=$backupDir->getBackups();
		$i=$this->count;
		while($i and count($backups)){
			array_pop($backups);
			$i--;
		}
		return $backups;
	}
}
