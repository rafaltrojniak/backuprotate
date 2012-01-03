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
			list($ret,$comment) = $this->checkSize($config['size'],$dir);
			$states[$ret]=true;
			$output[]=$comment;
		}
		if(in_array('count', $this->checks)){
			list($ret,$comment) = $this->checkCount();
			$states[$ret]=true;
			$output[]=$comment;
		}
		if(in_array('oldest', $this->checks)){
			list($ret,$comment) = $this->checkOldest();
			$states[$ret]=true;
			$output[]=$comment;
		}
		if(in_array('newest', $this->checks)){
			list($ret,$comment) = $this->checkNewest();
			$states[$ret]=true;
			$output[]=$comment;
		}

		echo implode(';',$output);

		return max(array_keys($states));
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
		// TODO Refactor
		// TODO Split in to smaller pices
		// Transform size to bytes
		$sizeKeys=array( "min_crit", "min_warn", "max_warn", "max_crit");
		$parsed=array();
		foreach($sizeKeys as $key){
			if(array_key_exists($key,$config)){
				$val=$config[$key];
				if(is_string($val)){
					$transform=(float)substr($val,0,-1);
					switch(strtolower(substr($val,-1))){
						case 't';
							$transform *= 1024;
						case 'g';
							$transform *= 1024;
						case 'm';
							$transform *= 1024;
						case 'k':
							$transform *= 1024;
							break;
						default:
							$transform=(float)$val;
					}
				}
				$parsed[$key]=$transform;
			}
		}

		// Sum up the sum of bytes

		// Compare the sizes and generates state/message
		$ret=0;
		$message='OK';

		$checkCount=0;
		$checkTypes=array();

		foreach($dir->getBackups() as $backup){

			$size=$backup->getSumSize();

			if(array_key_exists('min_warn',$parsed)){
				$checkCount++;
				$checkTypes['min_warn']=true;
				if( $size<$parsed['min_warn']) {
					$ret=1;
					$message="WARN:Size ($size) is smaller than ".$parsed['min_warn'];
				}
			}

			if(array_key_exists('max_warn',$parsed)){
				$checkCount++;
				$checkTypes['max_warn']=true;
				if( $size>$parsed['max_warn']) {
					$ret=1;
					$message="WARN:Size ($size) is larger than ".$parsed['max_warn'];
				}
			}

			if(array_key_exists('min_crit',$parsed)){
				$checkCount++;
				$checkTypes['min_crit']=true;
				if( $size<$parsed['min_crit']) {
					$ret=2;
					$message="CRIT:Size ($size) is smaller than ".$parsed['min_crit'];
				}
			}

			if(array_key_exists('max_crit',$parsed)){
				$checkCount++;
				$checkTypes['max_crit']=true;
				if($size>$parsed['max_crit']) {
					$ret=2;
					$message="CRIT:Size ($size) is larger than ".$parsed['max_crit'];
				}
			}
		}

		if(!$checkCount){
			$ret=3;
			$message="UNKNOWN:No checks were done";
		}
		$message.=":Checks:$checkCount/".count($checkTypes);
		return array($ret,$message);
	}

	private function checkCount()
	{
		throw new \Exception('TODO');
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
