<?php
/**
 * Cleaner algorithm interface
 **/
interface CleanerAlgo
{
	
	/** 
	 * Runs cleaner algorithm on the backupdir 
	 * 
	 * @param Backupdir $backupdir 
	 * @return Array List of backupdirs for cleanning
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function clean(Backupdir $backupdir);
}
