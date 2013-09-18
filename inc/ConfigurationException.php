<?php
/**
 * This exception is thrown, when there is configuration error
 **/
class ConfigurationException
	extends LogicException
{

	function __construct($message=null,$code=0)
	{
		parent:__construct($message,$code);
	}
}
?>
