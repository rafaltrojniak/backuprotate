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
	private $algo;

	/** 
	 * Builds from the config 
	 * 
	 * @param array $config Config from $config['backups']
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function __construct($config)
	{
		$this->config=$config;
	}

	/** 
	 * Returns backup list 
	 * 
	 * @return array
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getBackups()
	{
		if(is_null($this->backups))
		{
			$this->backups=array();
			$path=$this->config['dir'];
			$dir = new DirectoryIterator($path);
			foreach ($dir as $fileinfo) {
				if (!$fileinfo->isDot()) {
					$name=$fileinfo->getFilename();
					$date=DateTime::createFromFormat(DateTime::ISO8601,$name);
					$backup=new Backup(
						$date,
						$path.'/'.$name
					);
					$this->backups[$backup->getCreation()->getTimestamp()]=$backup;
				}
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
		$algo = $this->getRotateAlgo();

		$toPickup=$algo->pickup($this, $pickup);
		// TODO Pickup the files
		//throw new \Exception('TODO');
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
		if(is_null($this->algo)){
			//TODO Add auto-generation
			$this->algo = new RotateAlgo\Grouped($this->config['rotate_opts']);
		}
		return $this->algo;
	}

}
