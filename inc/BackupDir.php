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
	private $path;
	
	/** 
	 * List of backups inside that directory 
	 */
	private $backups;

	function __construct($path)
	{
		$this->path=$path;
	}

	public function getList()
	{
		if(is_null($this->backups))
		{
			$this->backups=array();
			$dir = new DirectoryIterator($this->path);
			foreach ($dir as $fileinfo) {
				if (!$fileinfo->isDot()) {
					$name=$fileinfo->getFilename();
					$date=DateTime::createFromFormat(DateTime::ISO8601,$name);
					$this->backups[]=new Backup(
						$date,
						$this->path.'/'.$name
					);
				}
			}
		}
		return $this->backups;
	}

}
