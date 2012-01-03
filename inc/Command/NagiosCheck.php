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
	 * Transofrm the size with units (kmgt) to float
	 *
	 * @param string $str Size with unit
	 * @return float
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function transformSize($str)
	{
		if(is_string($str)){
			$transform=(float)substr($str,0,-1);
			switch(strtolower(substr($str,-1))){
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
		foreach($sizeKeys as $key){
			if(array_key_exists($key,$config)){
				$config[$key]=$this->transformSize($config[$key]);
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

			if(array_key_exists('min_warn',$config)){
				$checkCount++;
				$checkTypes['min_warn']=true;
				if( $size<$config['min_warn']) {
					$ret=1;
					$message="WARN:Size ($size) is smaller than ".$config['min_warn'];
				}
			}

			if(array_key_exists('max_warn',$config)){
				$checkCount++;
				$checkTypes['max_warn']=true;
				if( $size>$config['max_warn']) {
					$ret=1;
					$message="WARN:Size ($size) is larger than ".$config['max_warn'];
				}
			}

			if(array_key_exists('min_crit',$config)){
				$checkCount++;
				$checkTypes['min_crit']=true;
				if( $size<$config['min_crit']) {
					$ret=2;
					$message="CRIT:Size ($size) is smaller than ".$config['min_crit'];
				}
			}

			if(array_key_exists('max_crit',$config)){
				$checkCount++;
				$checkTypes['max_crit']=true;
				if($size>$config['max_crit']) {
					$ret=2;
					$message="CRIT:Size ($size) is larger than ".$config['max_crit'];
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
