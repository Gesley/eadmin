<?php
/*
session_start();
set_time_limit(0); 
ini_set('default_charset','UTF-8'); 

ini_set('display_errors', '1');
error_reporting(E_ALL);
defined('APPLICATION_ROOT')
    || define('APPLICATION_ROOT', '/Teste/wilton/webservice');
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)));
ini_set('include_path', 
	ini_get('include_path') 
	. PATH_SEPARATOR . APPLICATION_PATH . "\\webservice\\");    
//echo APPLICATION_PATH;

set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PATH),
    realpath(APPLICATION_PATH . '/library'),
    get_include_path(),
)));
*/
require_once('Abstract.php');
class Services_Cjf extends Services_Abstract
{
	
	public static $WSDL_CPF = 'http://172.31.3.220:7778/wsConsultaCPF/wsConsultaCPFSoapHttpPort?WSDL';
	public static $WSDL_CNPJ = 'http://172.31.3.220:7778/wsConsultaCNPJ/wsConsultaCNPJSoapHttpPort?WSDL';
	public static $WSDL_CPF_POR_NOME = 'http://172.31.3.220:7778/wsConsultaCPFNome/wsConsultaCPFNomeSoapHttpPort?WSDL';
	/*
	public static $WSDL_CPF = 'http://187.115.83.180/wsConsultaCPF/wsConsultaCPFSoapHttpPort?WSDL';
	public static $WSDL_CNPJ = 'http://187.115.83.180/wsConsultaCNPJ/wsConsultaCNPJSoapHttpPort?WSDL';
	public static $WSDL_CPF_POR_NOME = 'http://187.115.83.180/wsConsultaCPFNome/wsConsultaCPFNomeSoapHttpPort?WSDL';
	*/
	/*
	public static $WSDL_OAB_POR_CPF = 'http://172.31.3.220:7778/wsConsultaCPF/wsConsultaCPFSoapHttpPort?WSDL';
	public static $WSDL_OAB_POR_NOME_ = 'http://172.31.3.220:7778/wsConsultaCPF/wsConsultaCPFSoapHttpPort?WSDL';
	*/

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
	public static function consultarDadosCPF($cpf, $orgao, $sistema, $usuario, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL_CPF );
			}
			
			$respostaWS = $wsCliente->getDadosCPFSecurity ( array ("pNumCPF" => $cpf, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema ) );
			
			var_dump($respostaWS->return);
		} catch ( SoapFault $soapFault ) {
			return false;
			//var_dump($soapFault);
		}
		/*
		$retorno = explode ( ";", $respostaWS->return );
		return $retorno;
		*/
		//var_dump( $respostaWS->return);
		return self::cjf_converterXmlCpfArray( $respostaWS->return );
	}
	
	/**
	 * Consulta dados pelo CNPJ
	 *
	 * @param string $cnpj
	 * @param string $orgao
	 * @param string $sistema
	 * @param string $usuario
	 * @param SoapClient $wsCliente
	 * @return array
	 */
	public static function consultarDadosCNPJ($cnpj, $orgao, $sistema, $usuario, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL_CNPJ );
			}
			
			$respostaWS = $wsCliente->getDadosCNPJSecurity ( array ("pNumCNPJ" => $cnpj, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema ) );
		} catch ( SoapFault $soapFault ) {
			return false;
		}
		//return self::converterXmlArray ( $respostaWS->return );
		return self::cjf_converterXmlCnpjArray( $respostaWS->return );
		//$retorno = explode ( ";", utf8_decode($respostaWS->return) );
		//return $retorno;
	}
	
	public static function consultarDadosCPFPorNome($nome, $orgao, $sistema, $usuario, $uf = '', $tipo = 1, $wsCliente = null) 
	{
		try {
			$nome = strtoupper ( Services_Cjf::removerAcento ( trim ( $nome ) ) );
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL_CPF_POR_NOME );
			}
			
			$respostaWS = $wsCliente->getDadosCPFSecurity ( array ("pNomeCPF" => $nome, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema, "pSigla_UF" => $uf, "tipo_pesquisa" => $tipo ) );
			//return $respostaWS->return;
			return self::cjf_converterXmlCpfArray( $respostaWS->return );
			
		} catch ( SoapFault $soapFault ) {
			//throw new InfraException($soapFault->__toString());
			return false;
		}
		return $ret;
	}
	
	/**
	 * Consulta dados da OAB pelo CPF
	 *
	 * @param string $cpf
	 * @param string $orgao
	 * @param string $sistema
	 * @param string $usuario
	 * @return array
	 */
	public static function consultarDadosOABPorCPF($cpf, $orgao, $sistema, $usuario, $wsCliente = null) 
	{
		try {
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL_OAB_POR_CPF );
			}
			
			$respostaWS = $wsCliente->getDadosOAB1 ( array ("pNumCpf" => $cpf, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema ) );
			return self::converterXmlArray ( $respostaWS->return );
		} catch ( SoapFault $soapFault ) {
			//var_dump($soapFault);
			return false;
		}
	}
	
	/**
	 * Consulta dados da OAB pelo n�mero de inscri��o e a UF,
	 * ou pelo n�mero de inscri��o e o nome do profissional
	 *
	 * @param string $oab
	 * @param string $uf
	 * @param string $nome
	 * @param string $orgao
	 * @param string $sistema
	 * @param string $usuario
	 * @return array
	 */
	public static function consultarDadosOABPorNomeOab($oab, $uf = '', $nome = '', $orgao, $sistema, $usuario, $wsCliente = null) 
	{
		try {
			if (($uf == '') && ($nome == '')) {
				throw new InfraException ( 'Deve ser informado a UF do n�mero da OAB, ou o Nome, para a consulta.' );
			}
			
			if ($wsCliente == null) {
				$wsCliente = self::gerarCliente ( self::$WSDL_OAB_POR_NOME );
			}
			
			$respostaWS = $wsCliente->getDadosOAB2 ( array ("pNumInsc" => $oab, "pUf" => $uf, "pNome" => $nome, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema ) );
			return self::converterXmlArray ( $respostaWS->return );
		} catch ( SoapFault $soapFault ) {
			return false;
		}
	}
}
/*
var_dump(Services_Cjf::consultarDadosCPFPorNome('Federal Toldos', 'trf1', 'eproc', 'consultanet','DF',1, NULL));

var_dump ( Services_Cjf::consultarDadosCPF ( '98124307172', 'trf1', 'eproc', 'consultanet', NULL ) );
*/
/*
$cliente = null;//Services_Cjf::gerarCliente ( Services_Cjf::$WSDL_CPF );
echo '<pre>';
var_dump ( Services_Cjf::consultarDadosCPF ( '70931941172', 'trf1', 'eproc', 'consultanet', $cliente ) );

var_dump ( Services_Cjf::consultarDadosCPF ( '00515374199', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCPF ( '69745811149', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCNPJ ( '03658507000125', 'trf1', 'eproc', 'consultanet' ) );

var_dump ( Services_Cjf::consultarDadosOABPorCPF ( '57204179315', 'trf1', 'eproc', 'consultanet' ) );
*/
/*
var_dump(Services_Cjf::consultarDadosOABPorCPF('85604631191', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('01722263504', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
*/
echo '<pre>';
//var_dump ( Services_Cjf::consultarDadosCPF ( '70931941172', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCPF ( '93799098100', 'trf1', 'eproc', 'consultanet', $cliente ) );
//var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
?>