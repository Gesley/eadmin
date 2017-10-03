<?php
class Services_Red_Listar extends Services_Abstract
{
	public static  $wsdl    = 'http://prd.trf1.gov.br/RED-REDRemoto/IEjbMetadadosDocumento?wsdl';
	public static  $ip      = '172.16.5.176';
	public static  $usuario = 'TR224PS';
	
	const SERVER_PRD  = 'prd.trf1.gov.br';
	const SERVER_DSV  = '172.16.3.152:8280'; 
	//const SERVER_DSV    = '172.16.4.153'; 
	public static $server;
	
	private function __construct() 
	{}
	
	public static function getInstance($desenvolvimento = false)
    {
    	if ($desenvolvimento) {
			self::$server = self::SERVER_DSV;
		} else {
			self::$server = self::SERVER_PRD;
		}
		
		self::$wsdl = 'http://' . self::$server . '/RED-REDRemoto/IEjbMetadadosDocumento?wsdl';
		
        static $instance;
        if ( ! isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

	public static function consultarDadosNumIdProc($numeroPasta, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$wsdl );
			}
			
			$params = array ( 
				"numeroPasta"   => $numeroPasta, 
				"usuario"       => self::$usuario, 
				"ipSolicitante" => self::$ip 
			);
			
			$respostaWS = $wsCliente->recuperaMetadadosDocumentosProcesso ( $params );
			//var_dump($wsCliente);
			//var_dump($respostaWS);
			//return (isset($respostaWS->return)) ? $respostaWS->return : false;
			if (isset($respostaWS->return)) {
				if (count($respostaWS->return) == 1) {
					return array($respostaWS->return);
				} else {
					return $respostaWS->return;
				}
			} else {
				return false;
			}
			//return self::object2array($respostaWS);
		} catch ( SoapFault $soapFault ) {
			echo $soapFault->getMessage();			
			return array("ERRO" => $soapFault->getMessage() );
		}
	}
	
	public static function object2array($object) 
	{
		$retorno = array();
		if (is_object($object) || is_array($object)) {
			foreach ($object as $key => $value) {
				$retorno[$key] = self::object2array($value);
			}
		}else {
			$retorno = $object;
		}
		return $retorno;
	}	
}
?>