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

}
