<?php
class RED 
{
	public $numeroDocumento	    = '153750100238';
	//public $numeroDocumento	    = '41600100232';
	public $statusDoc			= 0;
	public $statusDocEntrada	= 0;
	public $login				= 'TR224PS';
	public $ip					= '172.16.3.179';
	//public $ip					= '172.16.5.62';
	public $server				= 'prd.trf1.gov.br';
	//public $server				= '172.16.5.62';
	//public $server				= '172.16.3.179'; //desenvolvimento
	public $sistema			    = 'JURIS';
	public $txTerro 			= '';
	public $erro 				= false;
	public $xmlSaida 			= '';
	public $xmlCaminhoEntrada	= ''; 
	public $urlSaida 			= array();
	public $mensagem			= array(); 
	public $existeArquivo		= true; 
	public $nomeMaquina         = '';
	public $urlRed 			    = '';//http://' . $this->server . '/REDCentral/autorizacaoRecuperacaoDocumento';

	public function __construct()
	{
		$this->urlRed = 'http://' . $this->server . '/REDCentral/autorizacaoRecuperacaoDocumento';
		$this->setNomeMaquina();
	}
	
	public function setNomeMaquina () 
	{
		if($_SERVER['SERVER_NAME'] =="wwwdsv.trf1.gov.br"){
			$this->nomeMaquina = "SRVWEBDSV-TRF1";
		} else {
			$this->nomeMaquina = "SRVEPROC-TRF1";
		}
	}
	
	public function getXml()
	{
		$doc = new DOMDocument();
		$root = $doc->createElement('root');
		$doc->appendChild($root);

		$solicitanteArquivo = $doc->createElement('solicitanteArquivo');
		$root->appendChild($solicitanteArquivo); 
		
		$numeroDocumento = $doc->createAttribute('numeroDocumento');
		$solicitanteArquivo->appendChild($numeroDocumento);
		$numeroDocumentoTexto = $doc->createTextNode($this->numeroDocumento);
    	$numeroDocumento->appendChild($numeroDocumentoTexto); 
    	
		$login = $doc->createAttribute('login');
		$solicitanteArquivo->appendChild($login);
		$loginTexto = $doc->createTextNode($this->login);
    	$login->appendChild($loginTexto);

		$ip = $doc->createAttribute('ip');
		$solicitanteArquivo->appendChild($ip);
		$ipTexto = $doc->createTextNode($this->ip);
    	$ip->appendChild($ipTexto);

		$sistema = $doc->createAttribute('sistema');
		$solicitanteArquivo->appendChild($sistema);
		$sistemaTexto = $doc->createTextNode($this->sistema);
    	$sistema->appendChild($sistemaTexto);

		$nomeMaquina = $doc->createAttribute('nomeMaquina');
		$solicitanteArquivo->appendChild($nomeMaquina);
		$nomeMaquinaTexto = $doc->createTextNode($this->nomeMaquina);
    	$nomeMaquina->appendChild($nomeMaquinaTexto);        	
    	
		return $doc->saveXML($root); 
		//return $doc->saveXML(); 
	}	
	
	public function getDocs () 
	{
		$cuHandle = curl_init(); // initialize curl handle
		
		curl_setopt($cuHandle, CURLOPT_VERBOSE, 1); // set url to post to
		curl_setopt($cuHandle, CURLOPT_URL, $this->urlRed); // set url to post to
		curl_setopt($cuHandle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cuHandle, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($cuHandle, CURLOPT_HEADER, 0);
		curl_setopt($cuHandle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
		curl_setopt($cuHandle, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		curl_setopt($cuHandle, CURLOPT_HTTPHEADER, 0);//Array("Content-Type: text/xml"));
		curl_setopt($cuHandle, CURLOPT_TIMEOUT, 40); // times out after 4s
		curl_setopt($cuHandle, CURLOPT_POSTFIELDS, $this->getXml()); // add POST fields
		curl_setopt($cuHandle, CURLOPT_POST, 1);
		
		$this->xmlSaida = curl_exec($cuHandle); // run the whole process
		$httpCode = curl_getinfo($cuHandle, CURLINFO_HTTP_CODE);
		/*
		
		print "<pre>\n";
		print_r(curl_getinfo($cuHandle));  // get error info
		echo "\n\ncURL error number:" .curl_errno($cuHandle); // print error info
		echo "\n\ncURL error:" . curl_error($cuHandle); 
		print "</pre>\n";
		echo $this->getXml();
		echo 'httpcode: ',$httpCode;
		*/
		curl_close($cuHandle);
			
		if(in_array($httpCode,array(404,0))) {
			$this->mensagem();
			throw new Exception('Página de serviço não encontrada.');
		}
		
		return $this->urlRetorno();
	}
	
	public function enviarArquivo ()
	{
		$fp = fopen($localfile, 'r');
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 1); // set url to post to
		curl_setopt($ch, CURLOPT_URL, $this->urlRed);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_UPLOAD, 1);
 		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
 		
		curl_setopt($ch, CURLOPT_POST, 1 );
		//print_r($this->postParams);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$this->getXml());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_MAX_SEND_SPEED_LARGE,5000);
        //curl_setopt($ch,CURLOPT_PROGRESSFUNCTION,'progress');
       // curl_setopt($ch,CURLOPT_NOPROGRESS,false);
        echo $postResult = curl_exec($ch);

        if (curl_errno($ch)) {
             print curl_error($ch);
             print "<br>Unable to upload file.";
             exit();
        }
        curl_close($ch);
	}
	
	public function mensagem()
	{
		$xmlRedMensagem = @simplexml_load_string($this->xmlSaida);
		if(@isset($xmlRedMensagem->Mensagem)){
			for($i=0; $i < sizeof($xmlRedMensagem->Mensagem); $i++) {
				$TamArray = sizeof($this->mensagem);
				$this->mensagem[$TamArray]['codigo'] 	= $xmlRedMensagem->Mensagem[$i]['codigo'];
				$this->mensagem[$TamArray]['descricao'] = $xmlRedMensagem->Mensagem[$i]['descricao'];
			}
			$this->existeArquivo = false;
		}
	}	
	
	public function urlRetorno()
	{
		echo $this->xmlSaida;
		$xmlRedUrl = simplexml_load_string($this->xmlSaida);
		for($i=0; $i < sizeof($xmlRedUrl->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML); $i++) {
			$TamArray = sizeof($this->urlSaida);
			$this->urlSaida[$TamArray]['tipoArquivo'] 	= $xmlRedUrl->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[$i]['tipoArquivo'];
			$this->urlSaida[$TamArray]['url'] 			= $xmlRedUrl->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[$i]['url'];
		}
		
		return $this->urlSaida;
	}	
			
	public function error($msg)
	{
		die('<B>Document error: </B>'.$msg);
	}
	
	function openHttpsUrl($url, $refer = "", $usecookie = false) 
	{
		$ch = @curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
		
		if ($usecookie) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $usecookie);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $usecookie);   
		}
		
		if ($refer != "") {
			curl_setopt($ch, CURLOPT_REFERER, $refer );
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$result =curl_exec ($ch);
		curl_close ($ch);
		
		return $result;
	}
	
}
/*
	 
	
	*/
	//phpinfo();
	$red = new RED();
	try {
		echo '<pre>';
		var_dump($red->getDocs());	
	} catch (Exception $e) {
		echo 'erro';
		var_dump($e);
	}
	
?>