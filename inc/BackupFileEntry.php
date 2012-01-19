<?php
/**
 * file name  : inc/BackupFileEntry.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : czw, 19 sty 2012, 22:50:17
 * copyright  :
 *
 * modifications:
 *
 */

/**
 * Class represents entry in sumfile
 *
 * @author Rafał Trójniak rafal@trojniak.net
 * @version
 */
class BackupFileEntry{

	/**
	 * Relative file name
	 */
	private $name;

	/**
	 * File size according to sumfile
	 */
	private $size;

	/**
	 * File checksum according to sumfile
	 */
	private $sum;

	/**
	 * Backup that contains file
	 */
	private $backup;

	/**
	 * Constructs entry for sumfile
	 *
	 * @param string $name
	 * @param int $size
	 * @param string $sum
	 * @param Backup $backup
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($name, $size, $sum, \Backup $backup)
	{
		if(strlen($sum)!=40){
			throw new RuntimeException("Sum $sum is not 40 characters long");
		}

		$this->name=$name;
		$this->size=(int)$size;
		$this->sum=$sum;
		$this->backup=$backup;
	}

	/**
	 * Returns relative file name
	 *
	 * @return string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns file absolute path
	 *
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getPath()
	{
		return $this->backup->getPath().
			DIRECTORY_SEPARATOR.
			$this->name;
	}

	/**
	 * Returns file size
	 *
	 * @return int
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Returns checksum for file
	 *
	 * @return string
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getSum()
	{
		return $this->sum;
	}

	/**
	 * Returns fileinfo for the file
	 *
	 * @return SplFileInfo
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function getFileInfo()
	{
		return new SplFileInfo($this->getPath());
	}
}
