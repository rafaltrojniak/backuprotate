<?php
namespace Cloner;
/**
 * Makes Hard links for the files from pickup
 **/
class Linker implements \Cloner
{
	
	public function cloneBackup(
		\Backup $toClone,  $destDir)
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
