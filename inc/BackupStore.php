<?php
/**
 * Stores objects for all backupdirs
 **/
class BackupStore
{

	/**
	 * Config array
	 */
	private $config;

	/**
	 * Keeps objects for dirs
	 */
	private $dirs;

	/**
	 * Pickup dir
	 */
	private $pickup;

	/**
	 * Creates store from supplied configuration
	 *
	 * @param array $config Configuration of the script
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function __construct($config)
	{
		$this->config=$config;
	}

	/**
	 * Get single backupdir by name
	 *
	 * @param string $name
	 * @return BackupDir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getDir($name)
	{
		$dirs=$this->getDirs();
		if(array_key_exists($name,$dirs)){
			return $dirs[$name];
		}
		return null;
	}

	/**
	 * Get list of backupDirs
	 *
	 * @return Array
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getDirs()
	{
		if(is_null($this->dirs))
		{
			$this->dirs=array();
			foreach($this->config['backups'] as $id=>$cfg)
			{
				$this->dirs[$id]=new BackupDir($cfg);
			}
		}
		return $this->dirs;
	}

	/**
	 * Return pickup backup
	 *
	 * @return BackupDir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getPickup()
	{
		if(is_null($this->pickup)){
			$this->pickup=new BackupDir( $this->config['pickup'] );
		}
		return $this->pickup;
	}

	/**
	 * Forgets backup from all backupdirs
	 *
	 * @param \Backup $backup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function forgetBackup(\Backup $backup)
	{
		foreach($this->getDirs() as $dir)
		{
			$dir->forgetBackup($backup);
		}
		$this->getPickup()->forgetBackup($backup);
	}

	/**
	 * Returns configuration of the checks
	 *
	 * @return array
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getChecksConfig()
	{
		if(!array_key_exists('checks',$this->config))
			return array();
		return $this->config['checks'];
	}
}
