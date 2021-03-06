<?php

/**
 * One backup dir abstract
 *
 * @author Rafał Trójniak rafal@trojniak.net
 * @version
 */
class BackupDir
{

	/**
	 * Supplied BackupStore
	 */
	private $store;

	/**
	 * Path to that backupdir
	 */
	private $config;

	/**
	 * List of backups inside that directory
	 */
	private $backups;

	/**
	 * Algorithm used for rotation
	 */
	private $rotateAlgo;

	/**
	 * Algorithm used for cleanning backups over limits
	 */
	private $cleanerAlgo;

	/**
	 * Cloner object for the queue
	 */
	private $cloner;

	/**
	 * Resource used for locking
	 */
	private $lockResource;

	/**
	 * Builds from the config
	 *
	 * @param array $config Config from $config['backups']
	 * @param \BackupStore $store Store that backupdir is associated with
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function __construct($config, \BackupStore $store)
	{
		$this->config=$config;
		$this->store=$store;
	}

	/**
	 * Returns directory path
	 *
	 * @return string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getPath()
	{
		return $this->config['dir'];
	}

	/**
	 * Returns backup list
	 *
	 * @return array
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getBackups()
	{
		$this->lock();

		if(is_null($this->backups))
		{
			$this->backups=array();
			$dir = new DirectoryIterator( $this->getPath());
			foreach ($dir as $fileinfo)
			{

				// Removing '..'
				if ($fileinfo->isDot() or !$fileinfo->isDir()) {
					continue;
				}
				// Checking dotfile
				$name=$fileinfo->getFileName();
				if( $name[0]=='.') {
					continue;
				}

				$backup = Backup::create($fileinfo);
				$this->addBackup($backup);
			}
			ksort($this->backups);
		}
		return $this->backups;
	}

	/**
	 * Picks up new backups from supplied pickup dir
	 *
	 * @param BackupDir $pickup
	 * @return array list of picked backups
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function pickup(BackupDir $pickup)
	{
		$this->lock();

		$rotateAlgo= $this->getRotateAlgo();

		$toPickup=$rotateAlgo->pickup($this, $pickup);
		$cloner=$this->getCloner();
		foreach($toPickup as $backup)
		{
			$newPath=$this->getBackupPathFromTime($backup->getCreation());
			$cloner->cloneBackup($backup, $newPath );
			$newBackup=new Backup( $backup->getCreation(), $newPath);
			$this->addBackup($newBackup);
		}
		return $toPickup;
	}

	/**
	 * Gets pickup algorithm for the rotation
	 *
	 * @return RotateAlgo
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getRotateAlgo()
	{
		if(is_null($this->rotateAlgo)){
			$class='RotateAlgo\\'.ucfirst($this->config['rotate']);
			$opts=$this->config['rotate_opts'];
			$this->rotateAlgo= new $class($opts);
		}
		return $this->rotateAlgo;
	}

	/**
	 * Returns cloner
	 *
	 * @return Cloner Cloner configured for that backupdir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getCloner()
	{
		if(is_null($this->cloner)){
			// Default is copier
			$class="Copier";
			if(array_key_exists('copier',$this->config)){
				$class=ucfirst($this->config['copier']);
			}
			$class='Cloner\\'.$class;
			$options=null;
			if(array_key_exists('copier_opts',$this->config)){
				$options=$this->config['copier_opts'];
			}
			$this->cloner=new $class($options);
		}
		return $this->cloner;
	}

	/**
	 * Adds backup to the list
	 *
	 * @param Backup $backup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function addBackup(Backup $backup)
	{
		$this->backups[$backup->getCreation()->getTimestamp()]=$backup;
		ksort($this->backups);
	}

	/**
	 * Retufns backup path from supplied time
	 *
	 * @param DateTime $date
	 * @return string path for backup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function getBackupPathFromTime(DateTime $date)
	{
		return $this->getPath().'/'. $date-> format(DateTime::ISO8601);
	}

	/**
	 * Returns Cleaner algorithm for that backupdir
	 *
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getCleanerAlgo()
	{
		if(is_null($this->cleanerAlgo)){
			$class='CleanerAlgo\\'.ucfirst($this->config['clean']);
			$opts=$this->config['clean_opts'];
			$this->cleanerAlgo=new $class($opts);
		}
		return $this->cleanerAlgo;
	}

	/**
	 * Cleans backups calculated by clean algorithm
	 *
	 * @return Array List of Cleaned backups
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function clean()
	{
		$this->lock();

		$cleaner = $this->getCleanerAlgo();
		$toClean = $cleaner->clean($this);
		foreach($toClean as $backup){
			$backup->delete();
		}
		return $toClean;
	}

	/**
	 * Forgets about the backup
	 * This deletes it from the list, but not from the disk
	 *
	 * @param Backup $backup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function forgetBackup(Backup $backup)
	{
		unset($this->backups[$backup->getCreation()->getTimestamp()]);
	}

	/**
	 * Returns newest backup from the directory
	 *
	 * @return Backup|null
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getNewestBackup()
	{
		if(!count($this->getBackups()))
		{
			return null;
		}
		$keys=array_keys($this->backups);
		arsort($keys);
		$first=array_shift($keys);
		return $this->backups[$first];
	}

	/**
	 * Exclusive locks usage of the backupdir
	 *
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function lock()
	{
		if(is_null($this->lockResource)){
			$this->lockResource = fopen($this->getPath(),'r');
		}
		flock($this->lockResource,LOCK_EX);
	}

	/**
	 * Returns configs for checks
	 *
	 * @return array configuration data for that backupdir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getCheckConfigs()
	{
		$config=$this->store->getChecksConfig();
		if(array_key_exists('checks',$this->config)){
			$ownConfig=$this->config['checks'];
			// Merges recursively global config with local
			$mergeRec=function ($base, $local) use (&$mergeRec)
			{
				foreach($local as $key=>$val)
				{
					if(is_int($key)){
						$base[]=$val;
					}else{
						if(is_array($val) and array_key_exists($key, $base)){
							$base[$key]=$mergeRec($base[$key],$val);
						}else{
							$base[$key]=$val;
						}
					}
				}
				return $base;
			};
			$config=$mergeRec($config, $ownConfig);
		}

		return $config;
	}
}
