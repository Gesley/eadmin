<?php
set_time_limit(0);
ini_set("soap.wsdl_cache_enabled", "0");
require_once('WebServices/RED/Abstract.php');
class ServicesListaDocRed extends Services_Abstract 
{
	//public static $WSDL = 'http://172.16.3.205:80/RED-REDRemoto/IEjbMetadadosDocumento?wsdl';//205 167
	//public static $WSDL = 'http://172.16.5.62:8080/RED-REDRemoto/IEjbMetadadosDocumento?wsdl'; 
	public static $WSDL = 'http://prd.trf1.gov.br/RED-REDRemoto/IEjbMetadadosDocumento?wsdl';
	//public static $IPSR = '172.16.3.205';
	public static $IPSR = '172.16.5.176';
	public static $USER = 'TR224PS';
	
	
	
	private function __construct() 
	{}
	
	public static function getInstance()
    {
        static $instance;
        if ( ! isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }
	
	/**
	 * Consulta dados pelo CPF
	 *
	 * @param string $cpf
	 * @param string $orgao
	 * @param string $sistema
	 * @param string $usuario
	 * @param SoapClient $wsCliente
	 * @return array
	 */
	public static function consultarDadosNumIdProc($NumPasta, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL );
			}
			$params = array ( "numeroPasta" => $NumPasta, "usuario" => self::$USER , "ipSolicitante" => self::$IPSR );
			$respostaWS = $wsCliente->recuperaMetadadosDocumentosProcesso ( $params );
			//var_dump($wsCliente);
			//var_dump($respostaWS);
			return object2array($respostaWS);
		} 
		/**/
		catch ( SoapFault $soapFault ) {
			echo $soapFault->getMessage();			
			return array("ERRO" => $soapFault->getMessage() );
		}
		/** /
		#######
		catch (Exception $exception){ 
        	echo 'Erro ao acessar web service ('.$exception->faultstring.')'; 
         	exit;
		} 
		/**/    		
		#######
		//self::converterXmlArray ( $respostaWS->RetornarDadosAdvogadoByCPFResult );
	}
	//$result2 = $client->RetornarDadosAdvogadoByParams(array('numInsc'=>'1234567','uf'=>'AC','nome'=>'ADVOGADO'));
}
function object2array($object) {
	if (is_object($object) || is_array($object)) {
		foreach ($object as $key => $value) {
			$arraySaida[$key] = object2array($value);
		}
	}else {
		$arraySaida = $object;
	}
	return $arraySaida;
}	
function array2object($arrGiven){
	//create empty class
	$objResult=new stdClass();
	foreach ($arrLinklist as $key => $value){
		//recursive call for multidimensional arrays
		if(is_array($value)) $value=array2object($value);
		$objResult->{$key}=$value;
	}
	return $objResult;
}
?>