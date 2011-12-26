<?php
namespace Cloner;
/**
 * Copies files
 **/
class Copier implements \Cloner
{
	
	public function cloneBackup(
		\Backup $toClone,  $destDir)
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
