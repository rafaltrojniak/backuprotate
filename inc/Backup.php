<?php
/**
 * 
 **/
class Backup
{

	private $path;
	private $creation;
	
	function __construct(\DateTime $creation, $path)
	{
		$this->creation=$creation;
		$this->path=$path;
	}
}
