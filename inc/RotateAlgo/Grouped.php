<?php
/**
 * file name  : inc/RotateAlgo/Grouped.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:23:25
 * copyright  :
 *
 * modifications:
 *
 */
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

	/**
	 * Constructs algorithm from supplied arguments
	 *
	 * @param array $config
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
		extract($config);
		$this->group=$group;
		//TODO Check and implement
		if(array_key_exists('offset',$config)) {
			$this->offset=new \DateInterval($offset);
		}
	}

	/**
	 * Generates token for backup
	 *
	 * @param \Backup $backup
	 * @return srting token generated based on grouping string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function genToken(\Backup $backup)
	{
		$created = clone $backup->getCreation();
		if(!is_null($this->offset)){
			$created->sub($this->offset);
		}
		return $created->format($this->group);
	}

	/**
	 * Runs pickup command
	 *
	 * @param \BackupDir $to
	 * @param \BackupDir $from
	 * @return  array Array of backups to pickup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
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
				if($backup->verify(true)===true){
					$pickup[$token]=$backup;
				}
			}
		}

		return $pickup;
	}

	/**
	 * cleans backups that duplicates in group
	 *
	 * @param \BackupDir $dir
	 * @return array Array of backps to clean
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function clean(\BackupDir $dir)
	{
		// TODO
		throw new \Exception('TODO');
	}
}
