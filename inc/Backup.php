<?php
/**
 * 
 **/
class Backup
{

	/** 
	 * Path of the backup 
	 */
	private $path;
	
	/** 
	 * Creation date of the backup 
	 */
	private $creation;

	/** 
	 * Name of the checksum file 
	 */
	const SUMFILE = "checksums";

	/** 
	 * Array of names to ignore during generation 
	 */
	static private $ignoreFiles=array(
		"checksums",
	);

	
	/** 
	 * Create backup 
	 * 
	 * @param \DateTime $creation Creation datetime
	 * @param string $path  Path where backup is
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	function __construct(\DateTime $creation, $path)
	{
		$this->creation=$creation;
		$this->path=$path;
	}

	/** 
	 * Returns Date of the backup 
	 * 
	 * @return \DateTime
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getCreation()
	{
		return $this->creation;
	}

	/** 
	 * Returns path to the backup 
	 * 
	 * @return string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getPath()
	{
		return $this->path;
	}

	/** 
	 * Werifies checksums of the files in the backup 
	 * 
	 * @return Boolean true if everything is OK
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function werify()
	{
		$sumFilePath=$this->path.'/'.self::SUMFILE;
		if(!is_readable($sumFilePath)){
			echo 'Canot find sumfile for "'.addslashes($sumFilePath)."\"\n";
			return false;
		}
		$sums=file($sumFilePath);
		foreach($sums as $sumLine)
		{
			$tokens= explode(" ",trim($sumLine,"\n"));
			$hash=array_shift($tokens);
			$size=array_shift($tokens);
			$name=implode($tokens);
			$filePath=$this->path.'/'.$name;
			if(!is_readable($filePath)){
				//TODO Better error printing
				echo 'Canot read file "'.addslashes($filePath)."\"\n";
				return false;
			}
			if($size!=filesize($filePath)){
				//TODO Better error printing
				echo 'File size does not match for "'.addslashes($filePath)."\"\n";
				return false;
			}
			if($hash!=sha1_file($filePath)){
				//TODO Better error printing
				echo 'SHA1 sum does not match for "'.addslashes($filePath)."\"\n";
				return false;
			}
		}
		return true;
	}

	/** 
	 * Filles checksums of the files in the backups 
	 * 
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function fill()
	{
		$dir = new \DirectoryIterator($this->path);
		$sums=array();
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$name=$fileinfo->getFilename();
				if(in_array($name, self::$ignoreFiles)){
					continue;
				}
				$path=$fileinfo->getRealPath();
				$size=filesize($path);
				$sum=sha1_file($path);
				$sums[]=$sum.' '.$size.' '.$name;
			}
		}
		$sumfile=implode("\n",$sums);
		file_put_contents($this->path.'/'.self::SUMFILE, $sumfile);
	}

	/** 
	 * Creates backup from full path 
	 * 
	 * @param $fillPath 
	 * @return Backup
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	static public function create(\SplFileInfo $fileinfo)
	{
		$name=$fileinfo->getFilename();
		$date=\DateTime::createFromFormat(\DateTime::ISO8601,$name);
		return new Backup(
			$date,
			$fileinfo->getRealPath()
		);
	}
}
