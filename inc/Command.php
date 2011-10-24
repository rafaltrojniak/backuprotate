<?php

/** 
 * Command that will be used in commandline 
 * 
 * @author Rafał Trójniak rafal@trojniak.net
 * @version 
 */
interface Command
{
	
	function run($config);
}
