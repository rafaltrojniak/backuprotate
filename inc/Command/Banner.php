<?php
/**
 * file name  : inc/Command/Banner.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : śro, 4 sty 2012, 13:24:26
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Prints banner
 **/
class Banner implements \Command
{

	/**
	 * Prints banner with information about version, pid and some credits
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		# Version determination
		$version='unknown';

		$gitdir=dirname(__FILE__).'/../../.git';
		if(is_dir($gitdir)){
			$version=exec('git --git-dir='.escapeshellarg($gitdir).' describe --tags', $output, $ret);
			if($ret){
				echo "Failed to detect version\n";
				foreach($output as $line){
					echo "git:".$line."\n";
				}
			}
		}

		# Printing baner
		echo "Backuprotate version $version pid ".posix_getpid().
			" By Rafał Trójniak <backuprotate@trojniak.net> \n";
		return true;

	}
}
