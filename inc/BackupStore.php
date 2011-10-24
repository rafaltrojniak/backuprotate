<?php
/**
 * Stores objects for all backups
 **/
class BackupStore
{

	private $config;

	/** 
	 * Keeps objects for dirs 
	 */
	private $dirs=array();
	
	function __construct($config)
	{
		$this->config=$config;
	}

	public function getDir($name)
	{
		if(array_key_exists($name,$this->dirs)){
			return $this->dirs[$name];
		}
		// TODO
		/*
		if(array_key_exists($name,$this->config['backups'])){
			$config=$this->config['backups'][$name];
			$backup = new Backup($config['fff
		}
		 */
	}
}
