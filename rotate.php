<?php

# Version determination
$gitdir=dirname(__FILE__).'/.git';
$version='unknown';
if(is_dir($gitdir)){
	$version=exec('git --git-dir='.escapeshellarg($gitdir).' describe --tags', $output, $ret);
	if($ret){
		echo "Failed to detect version\n";
		foreach($output as $line){
			echo "git:".$line."\n";
		}
	}
}
# Printing baner
echo "Backuprotate version $version pid ".posix_getpid().
	" By Rafał Trójniak <backuprotate@trojniak.net> \n";

$config=require('config.php');

function getListFromDir($directory)
{

	return $list;
}

$commands=array(
	// Array(Short,Long,Description)
	array('h','help','Help info'),
	array('l::','list::','List backups for specific bucket, or fol all if not supplied','bucket'),
	array('p','pickup','List pickups'),
	array('r','rotate','Rotate backups'),
	array('c','clean','Clean backups'),
	array('f:','fill:','Fill checksums files in pickup','directory'),
	array('t:','transform:','Transform old sumfile to new one (CSV)','directory'),
	array('v::','verify::','Verify pickup backups','directory'),
	array('s','size-only','Verify only based on size'),
	array('d','del-pickup','Clean picup directory as configured'),
	array('b','build','Build backup bucket directories'),
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
$flags=array(
	'sizeOnly'=>false,
);

spl_autoload_register();
spl_autoload_register(function($name){
	$file='inc/'.str_replace('\\','/',$name).'.php';
	require_once($file);
});

$store=new BackupStore($config);

/* Parsing command-line options */
$options=getopt( $shortOpt,$longOpt);
foreach($options as $option=>$val){
	if(is_array($val)){
		$val=array_pop($val);
	}
	switch($option){
	case 'list':
	case 'l':
		$commandQueue[]=new Command\ListBackup($val);
		break;
	case 'pickup':
	case 'p':
		$commandQueue[]=new Command\ListPickup();
		break;
	case 'clean':
	case 'c':
		$commandQueue[]=new Command\Clean();
		break;
	case 'rotate':
	case 'r':
		$commandQueue[]=new Command\Rotate();
		break;
	case 'fill':
	case 'f':
		$commandQueue[]=new Command\Fill($val);
		break;
	case 'transform':
	case 't':
		$commandQueue[]=new Command\Transform($val);
		break;
	case 'del-pickup':
	case 'd':
		$commandQueue[]=new Command\DelPickup();
		break;
	case 'size-only':
	case 's':
		$flags['sizeOnly']=true;
		break;
	case 'verify':
	case 'v':
		$commandQueue[]=new Command\Verify($flags['sizeOnly'], $val);
		break;
	default:
		fprintf(STDERR,'Option '.addslashes($option)." is unsupported yet\n");
	case 'build':
	case 'b':
		$commandQueue[]=new Command\Build();
		break;
	case 'help':
	case 'h':
		fprintf(STDERR,"Options :\n");
		foreach($commands  as $com){
			list($short,$long,$description)=$com;
			$options=array();
			$param=null;
			if(count($com)>=4){
				$param=$com[3];
				if(strpos($short,'::')!==false)
				{
					$param="[$param]";
				}
				$param='='.$param;
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
	exit(1);
}

try{
	foreach($commandQueue as $com)
	{
		$ret=$com->run($store);
		if($ret!==true){
			exit($ret);
		}
	}
}catch(\Exception $e){
	echo 'Got exception ('.get_class($e).'):'.$e->getMessage()."\n";
	echo $e->getTraceAsString();
	exit(-1);
}
