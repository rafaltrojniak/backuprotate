<?php
/**
 * file name  : Command/Clean.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:25:02
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Cleans backups calculated by cleaners
 **/
class Clean implements \Command
{

	/**
	 * Runs cleanning command on all pickupdirs
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		echo "== Clean\n";
		$dirs=$store->getDirs();
		foreach($dirs as $id=>$dir){
			echo "[$id]:\n";
			$toClean=$dir->clean();
			foreach($toClean as $backup){
				echo "\t-\t".$backup->getCreation()->format(\DateTime::ISO8601)."\n";

			}
		}
		return true;
	}
}
