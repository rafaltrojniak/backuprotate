<?php

spl_autoload_register();
spl_autoload_register(function($name){
	$file='inc/'.str_replace('\\','/',$name).'.php';
	require_once($file);
});


$config=require('config.php');

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
	array('n:','nagios-check:','Runs checks and outputs in nagios format','bucket:checks,'),
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

$commandQueue=new \SplQueue();
$flags=array(
	'sizeOnly'=>false,
);

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
		$commandQueue->enqueue(new Command\ListBackup($val));
		break;
	case 'pickup':
	case 'p':
		$commandQueue->enqueue(new Command\ListPickup());
		break;
	case 'clean':
	case 'c':
		$commandQueue->enqueue(new Command\Clean());
		break;
	case 'rotate':
	case 'r':
		$commandQueue->enqueue(new Command\Rotate());
		break;
	case 'fill':
	case 'f':
		$commandQueue->enqueue(new Command\Fill($val));
		break;
	case 'transform':
	case 't':
		$commandQueue->enqueue(new Command\Transform($val));
		break;
	case 'del-pickup':
	case 'd':
		$commandQueue->enqueue(new Command\DelPickup());
		break;
	case 'size-only':
	case 's':
		$flags['sizeOnly']=true;
		break;
	case 'verify':
	case 'v':
		$commandQueue->enqueue(new Command\Verify($flags['sizeOnly'], $val));
		break;
	default:
		fprintf(STDERR,'Option '.addslashes($option)." is unsupported yet\n");
	case 'build':
	case 'b':
		$commandQueue->enqueue(new Command\Build());
		break;
	case 'nagios-check':
	case 'n':
		$commandQueue->enqueue(new Command\NagiosCheck($val));
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

if(! $commandQueue->top() instanceof Command\NagiosCheck)
{
	$commandQueue->unshift(new Command\Banner());
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
