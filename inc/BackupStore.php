<?php
/**
 * Stores objects for all backups
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
		$this->getDirs();
		if(array_key_exists($name,$this->dirs)){
			return $this->dirs[$name];
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
			$this->pickup=new BackupDir(
			array(
				'group'=>'%',
				'offset'=>0,
				'count'=>null,
				'dir'=>$this->config['pickupDir']
			));
		}
		return $this->pickup;
	}
}
