<?php
/**
 * 
 **/
class Backup
{

	private $path;
	private $creation;
	
	/** 
	 * Create backup 
	 * 
	 * @param \DateTime $creation Creation datetime
	 * @param string $path  Path where backup is
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function __construct(\DateTime $creation, $path)
	{
		$this->creation=$creation;
		$this->path=$path;
	}

	/** 
	 * Returns Date of the backup 
	 * 
	 * @return \DateTime
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getCreation()
	{
		return $this->creation;
	}

	/** 
	 * Returns path to the backup 
	 * 
	 * @return string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getPath()
	{
		return $this->path;
	}
}
