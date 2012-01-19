<?php
/**
 * GPG Crypter implementation using native gpg and system call
 **/
class GPGCrypter
{

	/**
	 * Key used to the encryption
	 */
	private $key;


	/**
	 * Sets GPG key used to encrypt files
	 *
	 * @param string $key
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function addEncryptKey($key)
	{
		$this->key=$key;
	}

	/**
	 *  Encrypts file using direct execution of gpg using exec command
	 *
	 * @param string $filename Name of the file (direct absolute path)
	 * @param string $encryptedFile Name of destination file (direct absolute path)
	 * @param boolean $armor If this should be armored ( Defaults to true)
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function encryptFile(
			$filename,
			$encryptedFile = null,
			$armor = true
	)
	{
		if($filename[0]!='/'){
			throw new \InvalidArgumentException("Argument filename should be absolte path, $filename given");
		}
		if($encryptedFile[0]!='/'){
			throw new \InvalidArgumentException("Argument encryptedFile should be absolte path, $encryptedFile given");
		}

		$command=
			"gpg". ($armor?' --armor':'').
			" -r ".$this->key.
			" -o ".escapeshellarg($encryptedFile).
			" -e ".escapeshellarg($filename).
			" 2>&1";

		exec($command, $output, $ret);
		if($ret){
			$output=implode("\n",$output);
			throw new \RuntimeException("GPG returned status $ret, command='$command' output:\n$output");
		}
	}
}
