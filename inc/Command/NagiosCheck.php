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
		$states=array(0=>true);
		$output=array();

		if(in_array('size', $this->checks)){
			list($ret,$comment) = $this->checkSize();
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

		return max(array_keys($states));
	}

	private function checkSize()
	{
		throw new \Exception('TODO');
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
