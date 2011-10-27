<?php
/**
 * Clonning method Strategy
 **/
interface Cloner
{
	
	/** 
	 * Clones backup to new backupdir 
	 * 
	 * @param Backup $toClone Backup to clone
	 * @param string $destDir Destination directory for backup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function cloneBackup(
		Backup $toClone,  $destDir);
	
}
