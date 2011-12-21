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
	 * Verification cache 
	 */
	private $verification;

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
	 * @param boolean $sizeOnly Flag if only size should be checked
	 * @return Boolean|string true if everything is OK, String containing message if not
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function verify($sizeOnly=false)
	{
		if(!is_null($this->verification)){
			return $this->verification;
		}

		// Caching only checksums
		if(!$sizeOnly){
			$this->verification = false;
		}

		$sumFilePath=$this->path.'/'.self::SUMFILE;
		if(!is_readable($sumFilePath)){
			return 'Canot find sumfile for "'.addslashes($sumFilePath).'"';
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
				return 'Canot read file "'.
					addslashes($filePath).'"';
			}
			if($size!=filesize($filePath)){
				return 'File size does not match for "'.
					addslashes($filePath).'"';
			}
			if(!$sizeOnly and $hash!=sha1_file($filePath)){
				return 'SHA1 sum does not match for "'.
					addslashes($filePath).'"';
			}
		}

		// Caching only checksums
		if(!$sizeOnly){
			$this->verification = true;
		}
		return true;
	}

	/** 
	 * Filles checksums of the files in the backups 
	 * 
	 * @author : Rafał Trójniak rafal@trojniak.net
	 * @return  Boolean True if successed
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
				$path=$fileinfo->getPathName();
				$size=filesize($path);
				$sum=sha1_file($path);
				$sums[]=$sum.' '.$size.' '.$name;
			}
		}
		$sumfile=implode("\n",$sums);
		return file_put_contents($this->path.'/'.self::SUMFILE, $sumfile)!==false;
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
		if(!$fileinfo->isDir()){
			throw new \RuntimeException('Supplied argument "'.addslashes($fileinfo->getPathName()).'"'.
				' is not a directory');
		}
		$name=$fileinfo->getFilename();
		$date=\DateTime::createFromFormat(\DateTime::ISO8601,$name);
		if(! $date instanceof  \DateTime){
			throw new \RuntimeException('Cannot parse date from file '.
				$fileinfo->getPathName()
				.' with format '.\DateTime::ISO8601.' and input '.$name);
               }

		return new Backup(
			$date,
			$fileinfo->getPathName()
		);
	}

	/** 
	 * Deletes backup 
	 * 
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function delete()
	{
		$dir = new \DirectoryIterator($this->path);
		foreach ($dir as $fileinfo) {
			if($fileinfo->isDir()){
				continue;
			}
			$path=$fileinfo->getPathName();
			if(!unlink($path)){
				throw new \RuntimeException( 'Failed to remove "'.$path.'"');
			}
		}
		
		if(!rmdir($this->path)){
			throw new \RuntimeException( 'Failed to remove directory "'.$path.'"' );
		}
	}
}
