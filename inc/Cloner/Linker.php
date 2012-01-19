<?php
/**
 * file name  : Cloner/Linker.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:32:47
 * copyright  :
 *
 * modifications:
 *
 */

namespace Cloner;

/**
 * Makes Hard links for the files from pickup
 **/
class Linker implements \Cloner
{

	/**
	 * Empty constructor
	 *
	 * @param config $config
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
	}

	/**
	 * Clones backup by hardlinking files from one directory
	 * to another
	 *
	 * @param \Backup $toClone
	 * @param $destDir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function cloneBackup( \Backup $toClone,  $destDir)
	{
		//TODO Implement in native
		exec("cp -lr ".escapeshellarg($toClone->getPath())." ".
			escapeshellarg($destDir), $output, $ret);
		if($ret){
			throw new \RuntimeException(
				'Copying failed with return status ['.$ret.'] and output :\n'.
				$output
			);
		}
	}
}
