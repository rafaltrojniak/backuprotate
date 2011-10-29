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
					$this->addBackup($backup);
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
		// TODO Find out duplicated backups and clean them
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
			//TODO Add auto-generation
			$this->rotateAlgo= new RotateAlgo\Grouped($this->config['rotate_opts']);
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
			//TODO Add auto-generation
			$this->cloner=new Cloner\Copier;
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
		// TODO Additional checks if needed
		$this->backups[$backup->getCreation()->getTimestamp()]=$backup;
	}

	private function getBackupPathFromTime(DateTime $date)
	{
		return $this->config['dir'].'/'. $date-> format(DateTime::ISO8601);
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
			//TODO Add auto-generation
			$this->cleanerAlgo=new CleanerAlgo\Count($this->config['clean_opts']);
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
		$cleaner = $this->getCleanerAlgo();
		$toClean = $cleaner->clean($this);
		foreach($toClean as $backup){
			$path=$backup->getPath();
			// TODO Implement as native
			exec("rm -rf ".escapeshellarg($path), $output, $ret);
			if($ret){
				throw new \RuntimeException(
					'Failed to remove '.$path.' with return status ['.$ret.'] and output :\n'.
					implode($output)
				);
			}
		}
		return $toClean;
	}
}
