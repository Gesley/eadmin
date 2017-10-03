<?php
	set_time_limit(0);
	ini_set("memory_limit","1024M");
	//ini_set("soap.wsdl_cache_enabled", "1");
	//error_reporting(E_ALL|E_STRICT);
	//ini_set('display_errors', true);
	date_default_timezone_set("America/Sao_Paulo");
	
	
	define('DS', DIRECTORY_SEPARATOR);
	define('PS', PATH_SEPARATOR);
	//define('BASEPATH', getcwd() . DS);
	
	
	define('BASEPATH', dirname(dirname(__FILE__)) . DS);
	
	// directory setup and class loading
	set_include_path(
		 'd:' . DS . 'library' . DS
		 . PS . BASEPATH . get_include_path() . DS
		 . PS . '.');
	include('zend/exception.php');
	include('zend/soap/client.php');
	include('zend/soap/client/common.php');
	include('zend/soap/client/dotnet.php');
	include('zend/soap/client/exception.php');
	include('zend/debug.php');
	
	/*
	
	
	
	try {
		$client =  new Zend_Soap_Client_DotNet(
			'http://prd.trf1.gov.br:80/aplicacoesapoio/calendarioWS?wsdl',
			array(
				'soapVersion' => SOAP_1_2,
				//'trace' => true,
			
		)); 
		Zend_Debug::dump($client, 'bla',true);
		$result = $client->getFunctions(); 
		echo '<pre>';
		var_dump($result);
		//$result = $client->RetornarDadosAdvogadosCFOAB(array('CPF'=>'57204179315')); 
	} catch(Zend_Soap_Client_Exception $e) {
		echo 'request';
		Zend_Debug::dump($client->getLastRequest());
		//var_dump($client->getLastResponse(); 
		echo 'message';
		echo $e->getMessage();
	}  
	
	*/
	
	try {
		$wsdl = 'http://www5.oab.org.br/TesteWS/Teste.asmx?WSDL';
		//$wsdl = 'http://www.webservicex.com/globalweather.asmx?WSD';
		/*
		$client =  new Zend_Soap_Client_DotNet(
			$wsdl,
			array(
				'soapVersion' => SOAP_1_2,
				//'trace' => true,
			)
		);
		 
		//Zend_Debug::dump($client, 'bla',true);
		//$client =  new Zend_Soap_Client('http://www5.oab.org.br/ConsultaNacionalws/ConsultaNacionalWs.asmx?WSDL',array('soapVersion' => SOAP_1_2)); 
		
		*/
		$client = new SOAPClient(
						$wsdl,
						array(
							'trace'        => true,
							'exceptions'   => 1,
			                'soap_version' => SOAP_1_2,
							'style'        => "mime",
							'style'        => SOAP_RPC,
							'use'          => SOAP_ENCODED,
							'location'     => $wsdl,

					)
		);
		
		$result = $client->__getFunctions(); 
		
				// assign the name attribute of Team to "toronto"
		//$wrapper->CPF->cpf = new SoapVar("57204179315", XSD_STRING);
		//$wrapper->CPF->cpf = new SoapVar("57204179315", SOAP_ENC_OBJECT, 'CPF');
		
		// call the getMascot method
		 // $respostaWS = $wsCliente->getDadosCNPJSecurity(array("pNumCNPJ" => $cnpj, "pNomeOrgao" => $orgao, "pLoginUsuario" => $usuario, "pNomeAplicacao" => $sistema));
		$result = $client->Soma(array("numero1" => 2, "numero2" => 2));
		//$result = $client->GetWeather(array('CityName'=>'Brasilia ','CountryName'=>'Brazil'));
		echo '<pre>';
		var_dump($client->__getTypes());
		
		// print out the Mascot
		//print($response->out->name);

		
		//$result = $client->RetornarDadosAdvogadosCFOAB($params); 
		//$result = $client->getUTCTime();
		//result =  $client->__call("RetornarDadosAdvogadosCFOAB", (array('CPF'=>'57204179315'));
	} catch(SoapFault  $fault) {
		echo 'request';
		//var_dump($client->__getLastRequest());
		//var_dump($client->__getLastResponse()); 
		echo 'message';
		Zend_Debug::dump ( $fault );

	}  
	
	
	echo '<pre>';
	var_dump($result);
