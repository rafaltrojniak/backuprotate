<?php
/**
 * file name  : inc/Command/Help.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : nie, 5 lut 2012, 01:06:57
 * copyright  :
 *
 * modifications:
 *
 */

namespace Command;

/**
 * Prints help info
 **/
class Help implements \Command
{

	/**
	 * Configuration
	 */
	private $config;

	/**
	 * Initialization of the command
	 *
	 * @param array $config Agrument config
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
		$this->config=$config;
	}

	/**
	 * Prints help info
	 *
	 * @param \BackupStore $store
	 * @return boolean return status
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function run(\BackupStore $store)
	{
		fprintf(STDERR,"Options :\n");
		foreach($this->config as $com){
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
		return false;
	}
}
