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
		exec("cp -r ".escapeshellarg($toClone->getPath())." ".
			escapeshellarg($destDir), $output, $ret);
		if($ret){
			throw new \RuntimeException(
				'Copying failed with return status ['.$ret.'] and output :\n'.
				$output
			);
		}
	}
}
