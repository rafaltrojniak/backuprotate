<?php
/**
 * file name  : inc/Cloner/GPGCrypter.php
 * @authors    : Rafał Trójniak rafal@trojniak.net
 * created    : czw, 19 sty 2012, 19:19:41
 * copyright  :
 *
 * modifications:
 *
 */

namespace Cloner;

/**
 * Encrypts files using GPG
 **/
class GPGCrypter implements \Cloner
{

	/**
	 * Configuration for action
	 */
	private $config;

	/**
	 * Encryption adapter
	 */
	private $crypter;

	/**
	 * Extension added to encrypted files
	 */
	private $extension = '.asc';

	/**
	 * Constructs object and consumes configuration
	 *
	 * @param array $config
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function __construct($config)
	{
		require_once('Crypt/GPG.php');
		$this->config=$config;

		$this->crypter=$this->getCrypter();

		$this->setKey();
	}

	private function getCrypter()
	{
		if(array_key_exists('directOpen', $this->config) and
			$this->config['directOpen'])
		{
			return new \GPGCrypter;
		}else{
			return new \Crypt_GPG ;
		}
	}

	/**
	 * Sets encryption key based on supplied config
	 *
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	private function setKey()
	{
		if(!array_key_exists('enc_key', $this->config)){
			throw new \LogicException('No enc_key provided');
		}

		$this->crypter
			->addEncryptKey($this->config['enc_key']);
	}

	/**
	 *  Clones backup by recursive copying directory
	 *
	 * @param \Backup $toClone
	 * @param string $destDir Destination directory
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function cloneBackup( \Backup $toClone,  $destDir)
	{
		mkdir($destDir);
		// Crypt files
		foreach($toClone as $item){
			$newName=
				$destDir.
				DIRECTORY_SEPARATOR.
				$item->getName().
				$this->extension;
			$this->cryptFile($item->getPath(), $newName);
		}
		// Crypt old sumfile
			$newName=
				$destDir.
				DIRECTORY_SEPARATOR.
				\Backup::SUMFILE.
				$this->extension;
		$this->cryptFile($toClone->getSumfilePath(),$newName);

		$newBackup=\Backup::create(new \SplFileInfo($destDir));
		$newBackup->fill();
	}

	/**
	 * Encrypts single file
	 *
	 * @param $src_file Source file name
	 * @param $dst_file Destinationf file name
	 * @return
	 * @author : Rafał Trójniak rafal@trojniak.net
	 */
	public function cryptFile($src_file, $dst_file)
	{

		$directory=dirname($dst_file);
		if(!is_dir($directory)){
			mkdir($directory);
		}
		//Crypt single file
		$this->crypter->encryptFile(
			$src_file,
			$dst_file,
			false);
	}

}
