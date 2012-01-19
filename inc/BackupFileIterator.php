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
		if(
			$this->file->eof()
			or ( // Last empty line
				is_array($row)
				and count($row)==1
				and is_null($row[0])
				and $this->file->getSize()==$this->file->ftell()
			)
		){
			$this->position=null;
			$this->row=null;
			return;
		}

		// Failed to read CSV
		if($row===false) {
			throw new \RuntimeException(
				'Failed to read from '.
				$this->file->ftell().' in file '.$this->file->getPath());
		}

		// Wrong number of entries in the row
		if(is_array($row) and count($row)!=3){
			throw new \RuntimeException(
				'Failed to read correct number of rows on '.
				$this->file->ftell().' in file '.$this->file->getPath());
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
