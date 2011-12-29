<?php
/**
 * file name  : Command/Fill.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:24:33
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Fill checksums on backup
 **/
class Fill implements \Command
{

	/**
	 * Path name of the directory
	 */
	private $path;

	/**
	 * Constructs command based on path for filling
	 *
	 * @param $path
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($path)
	{
		$this->path=$path;
	}

	/**
	 * Runs filling on the backups
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== Fill\n";
		$fileInfo= new \SplFileInfo($this->path);
		$backup = \Backup::create($fileInfo);
		return $backup->fill();
	}
}
