<?php
//error_reportING(E_ALL);
//set_time_limit(0);
//ini_set("memory_limit","1024M");
ini_set("soap.wsdl_cache_enabled", "0");
require_once('Abstract.php');
//require_once('Services/Oab.php');

class Services_Oab extends Services_Abstract 
{
	public static $WSDL = 'http://www5.oab.org.br/ConsultaNacionalws/ConsultaNacionalWs.asmx?WSDL';

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
	public static function consultarDadosCPF($cpf, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL );
			}
			
			$respostaWS = $wsCliente->RetornarDadosAdvogadoByCPF ( array('cpf' => $cpf) );
			//var_dump($respostaWS);
		} catch ( SoapFault $soapFault ) {
			return false;
		}
		return self::converterXmlArray ( $respostaWS->RetornarDadosAdvogadoByCPFResult );
	}
	
	public static function consultarDadosPorParams($numInsc, $uf, $nome, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL );
			}
			
			$respostaWS = $wsCliente->RetornarDadosAdvogadoByParams(array('numInsc'=>$numInsc,'uf'=>$uf,'nome'=>$nome));
			//var_dump($respostaWS);
		} catch ( SoapFault $soapFault ) {
			return false;
		}
		return self::converterXmlArray ( $respostaWS->RetornarDadosAdvogadoByParamsResult );
	}
	
	
	//$result2 = $client->RetornarDadosAdvogadoByParams(array('numInsc'=>'1234567','uf'=>'AC','nome'=>'ADVOGADO'));

}
/*
*/

var_dump(Services_Oab::$WSDL);
echo 'teste';
//exit();
$cliente = null; Services_Oab::gerarCliente ( Services_Oab::$WSDL );
echo '<pre>';
var_dump($cliente);

var_dump ( Services_Oab::consultarDadosCPF ( '11619058120', $cliente ) );
//var_dump ( Services_Oab::consultarDadosCPF ( '93799098100', $cliente ) );



/*
var_dump ( Services_Cjf::consultarDadosCPF ( '00462495671', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCPF ( '69745811149', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCNPJ ( '03658507000125', 'trf1', 'eproc', 'consultanet' ) );

var_dump ( Services_Cjf::consultarDadosOABPorCPF ( '57204179315', 'trf1', 'eproc', 'consultanet' ) );
*/
//var_dump(Services_Cjf::consultarDadosOABPorCPF('11619058120', 'trf1', 'eproc', 'consultanet', $cliente));
/*

var_dump(Services_Cjf::consultarDadosOABPorCPF('01722263504', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
*/
?>