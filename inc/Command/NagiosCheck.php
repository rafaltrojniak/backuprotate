<?php
/**
 * file name  : inc/Command/NagiosCheck.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : pon, 2 sty 2012, 00:19:35
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Runs nagios checks
 **/
class NagiosCheck implements \Command
{

	/**
	 * Array of checks to be done
	 */
	private $checks;

	/**
	 * Name of bucket to check
	 */
	private $bucket;

	/**
	 * Standard constructor
	 *
	 * @param $flags
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($flags)
	{
		$this->parseArguments($flags);
	}

	/**
	 * Parses arguments and fills checks table
	 *
	 * @param $args
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function parseArguments($args)
	{
		if(empty($args))
			throw new \RuntimeException('No flags passed');
		list($this->bucket,$tests) = explode(':',$args);
		$this->checks=explode(',',$tests);
	}

	/**
	 * Runs nagios checks based on flags
	 *
	 * @param \BackupStore $store
	 * @return int returnstate
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		$dir = $store->getDir($this->bucket);
		if(is_null($dir)){
			throw new \RuntimeException("Cannot find dir with name ".$this->bucket);
		}

		$states=array(0=>true);
		$output=array();

		$config=$store->getChecksConfig();

		if(in_array('size', $this->checks)){
			if(!array_key_exists('size',$config)){
				throw new \RuntimeException("No configuration for Size check");
			}
			list($ret,$comment,$perf) = $this->checkSize($config['size'],$dir);
			$states[$ret]=true;
			$output['size']=array($ret, $comment, $perf);
		}
		if(in_array('count', $this->checks)){
			list($ret,$comment,$perf) = $this->checkCount($config['count'],$dir);
			$states[$ret]=true;
			$output['count']=array($ret, $comment, $perf);
		}
		if(in_array('oldest', $this->checks)){
			list($ret,$comment,$perf) = $this->checkOldest();
			$states[$ret]=true;
			$output[]=$comment;
		}
		if(in_array('newest', $this->checks)){
			list($ret,$comment,$perf) = $this->checkNewest();
			$states[$ret]=true;
			$output[]=$comment;
		}

		$ret=max(array_keys($states));

		$message=self::retState($ret).':';

		foreach($output as $plugin=>$vals){
			$message.=$plugin."[".self::retState($vals[0]).':'.$vals[1]."] ";
		}

		$message.="|";

		// Adding performance
		foreach($output as $plugin=>$vals){
			$message.=$plugin."[".implode(':',$vals[2])."] ";
		}

		echo $message;

		return max(array_keys($states));
	}

	/**
	 * Transofrm the string with units (kmgt) to float
	 *
	 * @param string $str Size with unit
	 * @param int $step Step for the unit
	 * @return float
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	static public function transformUnit($str, $step=1024)
	{
		if(is_string($str)){
			$transform=(float)substr($str,0,-1);
			switch(strtolower(substr($str,-1))){
				case 't';
					$transform *= $step;
				case 'g';
					$transform *= $step;
				case 'm';
					$transform *= $step;
				case 'k':
					$transform *= $step;
					break;
				default:
					$transform=(float)$str;
			}
			return $transform;
		}elseif(is_numeric($str)){
			return (float)$str;
		}elseif(is_float($str) or is_int($str)){
			return (float)$str;
		}
		throw new \RuntimeException('Cannot parse string "'.addslashes($str).'" to size');
	}

	/**
	 * Runs standard warn and critical levels check on the directory
	 *
	 * @param array $levels Array of levels to check :  min_crit, min_warn, max_warn, max_crit
	 * @param string $format Format of comment ( "%ret%:Size (%val%) is %relation% %level%"
	 * @param float $value Value to check
	 * @return array array( ret, message)
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function checkWarnCrit($levels, $format, $value)
	{
		$checkCount=0;
		$checkTypes=array();
		$ret=0;
		$message="OK";
		if(array_key_exists('min_warn',$levels)){
			$checkCount++;
			$checkTypes['min_warn']=true;
			if( $value<$levels['min_warn']) {
				$ret=1;
				$message=self::formatMessage($format, $value, 'min_warn', $levels['min_warn']);
			}
		}

		if(array_key_exists('max_warn',$levels)){
			$checkCount++;
			$checkTypes['max_warn']=true;
			if( $value>$levels['max_warn']) {
				$ret=1;
				$message=self::formatMessage($format, $value, 'max_warn', $levels['max_warn']);
			}
		}

		if(array_key_exists('min_crit',$levels)){
			$checkCount++;
			$checkTypes['min_crit']=true;
			if( $value<$levels['min_crit']) {
				$ret=2;
				$message=self::formatMessage($format, $value, 'min_crit', $levels['min_crit']);
			}
		}

		if(array_key_exists('max_crit',$levels)){
			$checkCount++;
			$checkTypes['max_crit']=true;
			if($value>$levels['max_crit']) {
				$ret=2;
				$message=self::formatMessage($format, $value, 'max_crit', $levels['max_crit']);
			}
		}

		return array($ret, $message, $checkCount, $checkTypes);
	}

	/**
	 * Maps return state to state name
	 *
	 * @param $ret
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	static public function retState($ret)
	{
		$ret_map=array(
			0 => 'OK',
			1 => 'WARN',
			2 => 'CRIT',
			3 => 'UNKN',
		);
		if(array_key_exists($ret,$ret_map)){
			return $ret_map[$ret];
		}else{
			throw new \RuntimeException("Rerturn state $ret unknonwn");
		}
	}

	/**
	 * Formats message for nagios output
	 *
	 * @param string $format Format string to use
	 * @param float $value Actual value
	 * @param string $check Check name
	 * @param float $level Limit value
	 * @return string Formated string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	static public function formatMessage($format, $value, $check, $level)
	{
		return str_replace(
			array(
				'%val%',
				'%check%',
				'%level%',
			),
			array(
				$value,
				$check,
				$level,
			),
			$format
		);
	}

	/**
	 * Checks size of the backups in the directory
	 *
	 * @param array $config
	 * @param \BackupDir $dir
	 * @return array consisting of return state and message
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function checkSize($config, \BackupDir $dir)
	{
		return $this->checkByParam($config, $dir, 'getSumSize', 1024);
	}

	/**
	 * Checks count of files in backups
	 *
	 * @param array $config
	 * @param \BackupDir $dir
	 * @return array consisting of return state and message
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function checkCount($config, \BackupDir $dir)
	{
		return $this->checkByParam($config, $dir, 'getFileCount', 1000);
	}

	/**
	 * Runs checks on params generated for each backup
	 *
	 * @param array $config Config for tests
	 * @param \BackupDir $dir Backupdir to test
	 * @param $paramGetter Param getter
	 * @param $paramStep  Param unit transformation step
	 * @return array consisting of return state and message
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function checkByParam($config, \BackupDir $dir, $paramGetter, $paramStep)
	{
		// Transform size to bytes
		$configKeys=array( "min_crit", "min_warn", "max_warn", "max_crit");
		foreach($configKeys as $key){
			if(array_key_exists($key,$config)){
				$config[$key]=self::transformUnit($config[$key],$paramStep);
			}
		}

		// Generate sizes
		$vals=array();
		$backups=$dir->getBackups();
		foreach($backups as $key=>$backup){
			$vals[ $backup->getDateAsString() ] = $backup->$paramGetter();
		}

		// Runs checks
		$format = "%val% (%check% %level%)" ;

		$checkTypes=array();
		$checkCount=0;
		$states=array();
		foreach($vals as $key=>$val){

			list($state, $message, $count, $types)=$this->checkWarnCrit($config, $format, $val);

			if(!array_key_exists($state, $states)){
				$states[$state]=array();
			}
			$states[$state][$key]=$message;

			$checkCount+=$count;
			$checkTypes+=$types;
		}

		// Generates report
		$retState=max(array_keys($states));

		$message=count($states[$retState])." tests in current state";

		if(!$checkCount){
			$retState=3;
			$message="No checks were done";
		}

		// Summing performance data
		$perf=array();

		$perf[]="Checks:$checkCount/".count($checkTypes);

		foreach($states as $state=>$arr){
			$perf[]=self::retState($state)."=".count($arr);
		}

		foreach(array(3,2,1) as $cursor){
			if(array_key_exists($cursor, $states)){
				foreach($states[$cursor] as $key=>$msg){
					$perf[]=$key.' '.$msg;
				}
			}
		}

		return array($retState, $message, $perf);
	}

	private function checkOldest()
	{
		throw new \Exception('TODO');
	}

	private function checkNewest()
	{
		throw new \Exception('TODO');
	}
}
