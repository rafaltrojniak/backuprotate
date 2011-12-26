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
	 * @return int return state - if not zero, breakes command chain and returns
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store);
}
