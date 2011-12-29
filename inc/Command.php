<?php

/**
 * Command that will be used in commandline
 *
 * @author Rafał Trójniak rafal@trojniak.net
 * @version
 */
interface Command
{

	/**
	 * Runs the command
	 *
	 * @param \BackupStore $store
	 * @return int return state - if not boolen true, breakes command chain and returns status
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store);
}
