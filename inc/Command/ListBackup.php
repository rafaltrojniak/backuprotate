<?php
/**
 * file name  : Command/ListBackup.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:24:42
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Command lists content of pickup directory
 **/
class ListBackup implements \Command
{

	/**
	 * Bucket for printing, null if all should be printed
	 */
	private $bucket;

	/**
	 *  Construct command based on supplied bucket
	 *
	 * @param $bucket
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function __construct($bucket)
	{
		$this->bucket=$bucket;
	}

	/**
	 * Prints bucket contents
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		if($this->bucket!==false){
			$dir=$store->getDir($this->bucket);
			if(is_null($dir)){
				throw new \RuntimeException('Bucket "'.addslashes($this->bucket).'" not found');
			}
			$this->listBackup($dir);
		}else{
			echo "== List backups\n";
			$dirs=$store->getDirs();
			foreach($dirs as $id=>$dir){
				echo "[$id]:\n";
				$this->listBackup($dir);
			}
		}
		return true;
	}

	/**
	 * Prints content of single backupdir
	 *
	 * @param \BackupDir $dir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function listBackup(\BackupDir $dir)
	{
		foreach($dir->getBackups() as $backup){
			echo $backup->getCreation()->format(\DateTime::ISO8601)."\n";

		}
	}
}
