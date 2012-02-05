<?php
/**
 * file name  : inc/BackupFileIterator.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : czw, 19 sty 2012, 22:06:46
 * copyright  :
 *
 * modifications:
 *
 */

/**
 * Lets Iterate over backup contents based on sumfile contents
 *
 * @author Rafał Trójniak rafal@trojniak.net
 */
class BackupFileIterator implements Iterator
{

	/**
	 * File descriptor (for sumfile)
	 */
	private	$file;

	/**
	 * Number of the row in the file
	 */
	private $position;

	/**
	 * Current row
	 */
	private $row;

	/**
	 * Backup object
	 */
	private $backup;

	/**
	 * Constructs iterator based on filedescriptor for the sumfile
	 *
	 * @param SplFileObject $file
	 * @param Backup $backup
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct(SplFileObject $file, Backup $backup)
	{
		$this->file=$file;
		$this->backup=$backup;
	}

	/**
	 *  Gets current file path relative to backup base
	 *
	 * @return BackupFileEntry
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function current ( )
	{
		if(is_null($this->position)){
			$this->readRow();
		}
		return $this->row;
	}

	/**
	 * Returns number of file in sumfile
	 *
	 * @return int|null Null if pointer is not valid
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function key ( )
	{
		return $this->position;
	}

	/**
	 * Moves pointer to the next entry
	 *
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function next ( )
	{
		$this->readRow();
	}

	/**
	 *  Reset the pointer to the beginning of the file
	 *
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function rewind ( )
	{
		$this->file->fseek(0);
		$this->position=null;
	}

	/**
	 *  Checks if current position is valid
	 *
	 * @return boolean
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function valid ( )
	{
		if(is_null($this->position)){
			$this->readRow();
		}
		return !is_null($this->position);
	}

	/**
	 * Reads row from the file and stores state
	 *
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function readRow()
	{
		$row=$this->file->fgetcsv();

		// Check for end of file
		if( $row === false or is_null($row)){

			// Determine the error
			if( $this->file->eof() ) {
				$this->position=null;
				$this->row=null;
				return;
			}

			throw new \RuntimeException('Unknown error');
		}

		// Not an array
		if( !is_array($row) ){
			throw new \RuntimeException(
				'Got '.gettype($row).' on '. $this->file->ftell().' in file '.$this->file->getPath());
		}

		// Last line with signle null field
		if(count($row)==1 and is_null($row[0])){
			$this->position=null;
			$this->row=null;
			return;
		}

		// Wrong number of entries in the row
		if( count($row)!=3){
			throw new \RuntimeException(
				'Failed to read correct number of rows '.count($row).' on '.
				$this->file->ftell().' in file '.$this->file->getPath());
		}

		// Check the format
		if( is_null($row[0]) or is_null($row[1]) or is_null($row[2])){
			throw new \RuntimeException('Some of the fields is null');
		}

		// Creating entry
		list($size, $hash, $name ) = $row;
		$this->row=new \BackupFileEntry($name,$size,$hash, $this->backup);

		// Moving pointer
		if(is_null($this->position)){
			$this->position=0;
		}else{
			$this->position++;
		}
	}

}
