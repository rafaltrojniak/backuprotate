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
	const SUMFILE = "checksums.csv";

	/** 
	 * Verification cache 
	 */
	private $verification;

	/** 
	 * Array of names to ignore during generation 
	 */
	static private $ignoreFilenames=array(
		"checksums",
		"checksums.csv",
		".",
		"..",
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
		return basename($this->path);
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
		// Creating logfile
		$sumfilePath=$this->path.'/'.self::SUMFILE;
		$sumfile = fopen($sumfilePath, "w");
		if($sumfile===false){
			throw new \RuntimeException("Failed opening  sumfile :  $sumfilePath");
		}

		$orgPathLen=strlen($this->path)+1;

		$stack = new \SplStack();
		$stack -> push ( $this->path );

		while(!$stack->isEmpty()){

			$iterator = new \DirectoryIterator( $stack -> pop());

			foreach($iterator as $key){

				// Skipping ignoreFilenames
				$name=$key->getFilename();
				if(in_array($name, self::$ignoreFilenames)){
					continue;
				}

				if($key->isDir()){
					// Push back files
					$stack->push( $key->getPathName() );

				}elseif($key->isFile()){

					$path=$key->getPathName();
					$relPath=(substr($path,$orgPathLen));

					$fields = array();

					$fields[] = $key->getSize();

					$sum=sha1_file($path);
					if($sum===false){
						throw new \RuntimeException("Error while calculating sum of file $path");
					}

					$fields[] = $sum;
					$fields[] = $relPath;

					if(fputcsv($sumfile, $fields, "," )===false){
						throw new \RuntimeException("Failed writing to sumfile :  $sumfilePath");
					}

				}

			}

		}

		if(fclose($sumfile )===false){
			throw new \RuntimeException("Failed writing to sumfile :  $sumfilePath");
		}

		return true;
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
