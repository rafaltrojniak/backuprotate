<?php
/**
 * Rotating algorithm interface
 **/
interface RotateAlgo
{

	/**
	 * Calculates backups to pickup
	 *
	 * @param \BackupDir $to Backup dir to fill
	 * @param \BackupDir $from  Backup dir to pickup from
	 * @return Array array of Backup for pickup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function pickup(\BackupDir $to, \BackupDir $from);

	/**
	 * Generates list of backups to clean
	 *
	 * @param \BackupDir $dir directory to clean
	 * @return Array array of Dir
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function clean(\BackupDir $dir);
}
