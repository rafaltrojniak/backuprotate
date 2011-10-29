<?php

namespace RotateAlgo;

/**
 * Grouping backups by the token constructed from supplied format
 **/
class Grouped implements \RotateAlgo
{

	/** 
	 * Groupping token 
	 */
	private $group;
	
	/** 
	 * Offset of the timestamp used in creating token 
	 */
	private $offset;

	public function __construct($config)
	{
		extract($config);
		$this->group=$group;
		//TODO Check and implement
		if(array_key_exists('offset',$config)) {
			$this->offset=new \DateInterval($offset);
		}
	}

	private function genToken(\Backup $backup)
	{
		$created = clone $backup->getCreation();
		if(!is_null($this->offset)){
			$created->sub($this->offset);
		}
		return $created->format($this->group);
	}
	
	public function pickup(\BackupDir $to, \BackupDir $from)
	{
		$current=array();
		$pickup=array();
		// Generationg current state
		foreach($to->getBackups() as $backup)
		{
			$token = $this->genToken($backup);

			if(!array_key_exists($token, $current) or 
				$backup->getCreation()->getTimestamp()<
				$current[$token]->getCreation()->getTimestamp()){
				$current[$token]=$backup;
			}
		}

		// Generationg current state
		foreach($from->getBackups() as $backup)
		{
			$token = $this->genToken($backup);

			$toCompare=null;

			if(array_key_exists($token, $current)){
				$toCompare=$current[$token];
			}

			if(array_key_exists($token, $pickup)){
				$toCompare=$pickup[$token];
			}

			if(is_null($toCompare) or
				$backup->getCreation()->getTimestamp()<
				$toCompare->getCreation()->getTimestamp()){
				$pickup[$token]=$backup;
			}
		}

		return $pickup;
	}

	public function clean(\BackupDir $dir)
	{
		throw new \Exception('TODO');
	}
}
