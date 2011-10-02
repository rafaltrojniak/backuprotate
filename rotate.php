<?php
$config=require('config.php');

function getListFromDir($directory)
{
	$dir = new DirectoryIterator($directory);
	$list=array();
	foreach ($dir as $fileinfo) {
		if (!$fileinfo->isDot()) {
			$name=$fileinfo->getFilename();
			$list[$name]=array(
				'date'=>DateTime::createFromFormat(DateTime::ISO8601,$name)
			);
		}
	}

	return $list;
}

var_dump(getListFromDir($config['pickupDir']));
