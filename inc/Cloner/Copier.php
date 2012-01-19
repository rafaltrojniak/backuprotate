<?php
/**
 * file name  : Cloner/Copier.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 26 gru 2011, 22:31:12
 * copyright  :
 *
 * modifications:
 *
 */

namespace Cloner;

/**
 * Copies files
 **/
class Copier implements \Cloner
{

	/**
	 * Empty constructor
	 *
	 * @param array $config
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
	}

	/**
	 *  Clones backup by recursive copying directory
	 *
	 * @param \Backup $toClone
	 * @param $destDir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function cloneBackup( \Backup $toClone,  $destDir)
	{
		//TODO Implement in native
		exec("cp -r ".escapeshellarg($toClone->getPath())." ".
			escapeshellarg($destDir), $output, $ret);
		if($ret){
			throw new \RuntimeException(
				"Copying failed with return status [".$ret."] and output :\n".
				implode($output)
			);
		}
	}
}
