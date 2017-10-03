<?php
class App_Minuta_Parametros_Incluir extends Services_Red_Minuta_Parametros_Incluir
{

	public function __construct()
	{
         
                $userNs = new Zend_Session_Namespace('userNs');  
		$this->login = $userNs->matricula;
		$this->ip    = Services_Red::IP_MAQUINA_EADMIN;
	}
	
}
?>