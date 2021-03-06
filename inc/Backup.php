<?php
/**
 * Class represent single backup made in particular moment of time
 * Backup may consist of many files and directories.
 *
 * Backup can containt sumfile that contains sizes, checksums and names
 * of files in backup. This file can be used for verification.
 **/
class Backup implements IteratorAggregate
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
	 * Counters
	 */
	private $counters;

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

		$this->verification=array(
			true=>null,
			false=>null,
		);
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
	 * Returns sumfile path
	 *
	 * @return string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getSumfilePath()
	{
		return $this->path.'/'.self::SUMFILE;
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
		$fromCache=$this->verification[$sizeOnly];
		if(!is_null($fromCache)){
			return $fromCache;
		}

		$this->verification[$sizeOnly] = false;

		$iterator=$this->getIterator();

		if(!$iterator instanceof BackupFileIterator){
			return $iterator;
		}

		foreach($iterator as $info)
		{
			$filePath=$info->getPath();
			if(!is_readable($filePath)){
				return 'Canot read file "'.
					addslashes($filePath).'"';
			}
			if($info->getSize()!=filesize($filePath)){
				return 'File size does not match for "'.
					addslashes($filePath).'"';
			}
			if(!$sizeOnly and $info->getSum()!=sha1_file($filePath)){
				return 'SHA1 sum does not match for "'.
					addslashes($filePath).'"';
			}
		}

		// Caching only checksums
		$this->verification[$sizeOnly] = true;

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
		$sumfilePath=$this->getSumfilePath();
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

		// Saving checksums
		$this->verification[true] = true;
		$this->verification[false] = true;

		return true;
	}

	/**
	 * Creates backup from full path
	 *
	 * @param SplFileInfo $fileinfo
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
		$stack = new \SplStack();
		$stack -> push ( $this->getPath());

		$pushed=null;

		while(!$stack->isEmpty()){
			$pushed=false;
			$dirPath=$stack->pop();

			$dir = new \DirectoryIterator($dirPath);
			foreach ($dir as $file) {

				// Skipping '.' and '..'
				if(in_array($file->getFilename(), array('..','.'))){
					continue;
				}

				if($file->isDir()){
					// Push current
					$stack -> push ( $dirPath );
					// Push new one
					$stack -> push ( $file->getPathName() );

					$pushed=true;

					break;
				}

				$path=$file->getPathName();
				if(!unlink($path)){
					throw new \RuntimeException( 'Failed to remove "'.$path.'"');
				}

			}

			if(!$pushed and !rmdir($dirPath)){
				throw new \RuntimeException(
					'Failed to remove directory "'.$path.'"' );
			}

		}

	}

	/**
	 * Transforms directory from old (space-separated) checksum file to CSV
	 *
	 * @return boolean
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function transform()
	{

		$iterator=$this->getIterator();

		if(!$iterator instanceof BackupFileIterator){
			throw new \RuntimeException($iterator);
		}

		$newfilePath=$this->getSumfilePath();
		$newfile = fopen($newfilePath, "w");
		if($newfile===false){
			throw new \RuntimeException(
				"Failed opening  newfile :  $newfilePath");
		}

		foreach($iterator as $info){

			// Creating new
			$fields=array(
				$info->getSum(),
				$info->getSize(),
				$info->getName());
			if(fputcsv($newfile, $fields, "," )===false){
				throw new \RuntimeException(
					"Failed writing to newfile :  $newfilePath");
			}

		}

		if(fclose($newfile )===false){
			throw new \RuntimeException(
				"Failed writing to newfile :  $newfilePath");
		}
		return true;
	}

	/**
	 * Return sum size of the files
	 *
	 * @return float
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getSumSize()
	{

		$this->cacheCounters();

		return $this->counters['size'];
	}

	/**
	 * Return count of the files
	 *
	 * @return float
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getFileCount()
	{

		$this->cacheCounters();

		return $this->counters['count'];
	}

	/**
	 * Reads sumfile and calculates summary size and file count
	 *
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function cacheCounters()
	{
		if(is_null($this->counters)){
			$size=0;
			$count=0;

			$iterator=$this->getIterator();

			if(!$iterator instanceof BackupFileIterator){
				return $iterator;
			}

			foreach($iterator as $info)
			{
				$size+=$info->getSize();
				$count++;
			}

			$this->counters=array(
				'size'=>$size,
				'count'=>$count,
			);
		}
	}

	/**
	 * Returns date of backup as string
	 *
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getDateAsString()
	{
		return $this->creation->format(\DateTime::ISO8601);
	}

	public function getIterator()
	{
		$sumFilePath=$this->getSumfilePath();
		if(!is_readable($sumFilePath)){
			return 'File is not readable "'.addslashes($sumFilePath).'"';
		}
		$file = new SplFileObject($sumFilePath, 'r');
		return new BackupFileIterator($file, $this);
	}
}
