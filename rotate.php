<?php
$config=require('config.php');

function getListFromDir($directory)
{

	return $list;
}

$commands=array(
	// Array(Short,Long,Description)
	array('h','help','Help info'),
	array('l','list','List backups'),
	array('p','pickup','List pickups'),
	array('b:','backups:','Backup group (default to pickup)','group'),
);

$shortOpt=null;
$longOpt=array();
foreach($commands as $com){
	list($short,$long,$description)=$com;
	$shortOpt.=$short;
	if(!is_null($long)){
		$longOpt[]=$long;
	}
}

$commandQueue=array();
$backup='pickup';

spl_autoload_register();
spl_autoload_register(function($name){
	$file='inc/'.str_replace('\\','/',$name).'.php';
	require_once($file);
});

$store=new BackupStore($config);

/* Parsing command-line options */
$options=getopt( $shortOpt,$longOpt);
foreach($options as $option=>$val){
	switch($option){
	case 'b':
	case 'backup':
		$backup=$val;
		break;
	case 'list':
	case 'l':
		$commandQueue[]=new Command\ListBackup();
		break;
	case 'pickup':
	case 'p':
		$commandQueue[]=new Command\ListPickup();
		break;
	default:
		fprintf(STDERR,'Option '.addslashes($option)." is unsupported yet\n");
	case 'help':
	case 'h':
		fprintf(STDERR,"Options :\n");
		foreach($commands  as $com){
			list($short,$long,$description)=$com;
			$options=array();
			$param=null;
			if(count($com)>=4){
				$param='='.$com[3];
				$short=trim($short,':');
				$long=trim($long,':');
			}
			if(!is_null($short)){
				$options[]='-'.$short;
			}
			if(!is_null($long)){
				$options[]='--'.$long;
			}
			fprintf(STDERR,"\t".implode('|',$options)."$param\t$description\n");
		}
		exit();
		break;
	}
}

if(!count($commandQueue)){
	echo "Command queue is empty\n";
}

foreach($commandQueue as $com)
{
	$com->run($store);
}
