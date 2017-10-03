<?php
class classListaDocRed
{
	public function __construct() 
	{}
	public function red()
	{
		require_once('WebServices/RED/ServicesListaDocRed.php');
		return ServicesListaDocRed::getInstance();	
	}
}
?>