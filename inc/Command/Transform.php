<?php
/**
 * file name  : Command/Transform.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:26:45
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Transform checksum file from old to new one
 **/
class Transform implements \Command
{

	/**
	 * Path name of the directory
	 */
	private $path;

	/**
	 * Constructs command based on supplied path for pickupdir
	 *
	 * @param $path
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($path)
	{
		$this->path=$path;
	}

	/**
	 * Runs transformation commnad on backup
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== Transform\n";
		$fileInfo= new \SplFileInfo($this->path);
		$backup = \Backup::create($fileInfo);
		return $backup->transform();
	}
}
