<?php
abstract class Services_Red_Abstract implements Services_Red_Interface
{
	const SERVER_PRD  = 'prd.trf1.gov.br';
        //const SERVER_DSV  = '172.16.3.179';
        //const SERVER_DSV  = '172.16.3.152:8280';
//        const SERVER_DSV  = '172.16.3.152'; HML HML HML HML 
        const SERVER_HML  = 'jeehml.trf1.jus.br';
        const SERVER_DSV  = '172.16.3.179';
        const SERVER_TST  = '172.16.3.179';
	
	const NOME_MAQUINA_PRD   = "SRVWEB1-TRF1";
	const NOME_MAQUINA_DSV   = "SRVWEBDSV-TRF1";
	const NOME_MAQUINA_EPROC = "SRVEPROC-TRF1";
	
	public $server   = 'prd.trf1.gov.br'; //producao
	public $urlRed   = '';
	public $xmlSaida = '';
	public $debug    = false;
	public $temp     = '';
	
	public function __construct($desenvolvimento = false)
	{
            if(defined('APPLICATION_ENV')){
                if (APPLICATION_ENV == 'development') {
                    $this->server = self::SERVER_DSV;
                }else if(APPLICATION_ENV == 'production'){
                    $this->server = self::SERVER_PRD;
                }else if(APPLICATION_ENV == 'staging'){
                    $this->server = self::SERVER_HML;
                }else if(APPLICATION_ENV == 'testing'){
                    $this->server = self::SERVER_TST;
                }
            }else{
                throw new Exception('A constante APPLICATION_ENV não foi definida.');
            }
            $desenvolvimento = '';/*evitar warnning*/
            /**
             * Para aplicações fora do ZendFrameWork
             */
            /*
             if ($desenvolvimento) {

                    $this->server = self::SERVER_DSV;
            }
             */ 
	}
	
	public function _getAutorizacao ($url,$xml) 
	{
		$options = array(
	 		CURLOPT_VERBOSE        => 1,
	 		CURLOPT_URL            => $url,
	 		CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_HEADER         => 0,
			CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)",
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => 40,
			CURLOPT_POST           => 1,
			CURLOPT_POSTFIELDS     => $xml,
 		);
 		
		$ch = curl_init(); // initialize curl handle
		curl_setopt_array($ch, $options);
		$retorno = curl_exec($ch); // run the whole process
		
		if ($this->debug) {
			$this->_debug($ch,$xml,$retorno);
		}
		
		if(curl_errno($ch)) {
			//$this->mensagem();
			throw new Exception('P�gina de servi�o n�o encontrada.');
		}
		curl_close($ch);
		return $retorno;
	}
	
	public function getNomeMaquina () 
	{
		if($_SERVER['SERVER_NAME'] =="wwwdsv.trf1.gov.br"){
			return self::NOME_MAQUINA_DSV;
		} else {
			return self::NOME_MAQUINA_EPROC;
		}
	}
	
	public function _upload ($url,$xml,$file,$login,$ip, $assinatura = '')
	{
		/*
		 * Criando um arquivo tempor�rio com extens�o .xml exigido para enviar
		 */	
		//$tmpfname = tempnam(TEMP_DIR, "XMLRED");
		$tmpfname = $this->temp . DIRECTORY_SEPARATOR . "XMLRED" . md5($file) . '.xml';
		//$tmpfname = substr($tmpfname,0,strpos($tmpfname,'.')) . '.xml';
		//echo $tmpfname;
		//exit;
		$handle = fopen($tmpfname, "w");
		fwrite($handle, $xml);
		fclose($handle);
		
		$data = array(
 			'ip'    => $ip,
 			'login' => $login,
 			'xml'   => "@" . $tmpfname, 
 			'file'  => "@" . $file
 		);
                
                //se tiver caminho do arquivo da assinatura
                if(!empty($assinatura)){
                    $data['assinatura'] = '@' . $assinatura;
                }
                
 		$options = array(
	 		CURLOPT_VERBOSE        => 1,
	 		CURLOPT_URL            => $url,
	 		CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSLVERSION     => 3,
			CURLOPT_HEADER         => 0,
			CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)",
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POST           => 1,
			CURLOPT_POSTFIELDS     => $data,
 		);
 		
 		$ch = curl_init();
 		curl_setopt_array($ch, $options);
        $retorno = curl_exec($ch);
        
		if ($this->debug) {
			$this->_debug($ch,$xml,$retorno);
		}
		
		unlink($tmpfname);
		
        if (curl_errno($ch)) {
            //$this->mensagem();
            curl_close($ch);
			throw new Exception('P�gina de servi�o n�o encontrada.');
        }
        
        curl_close($ch);
        
        return $retorno;
	}
	
	public function openHttpsUrl($url, $refer = "", $usecookie = false) 
	{
		$options = array(
	 		CURLOPT_URL            => $url,
	 		CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSLVERSION     => 3,
			CURLOPT_HEADER         => 0,
			CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)",
			CURLOPT_RETURNTRANSFER => 1
 		);
	
		if ($usecookie) {
			$options[][CURLOPT_COOKIEJAR]  = $usecookie;
			$options[][CURLOPT_COOKIEFILE] = $usecookie;
		}
		
		if ($refer != "") {
			$options[CURLOPT_REFERER] = $refer;
		}
		
		$ch = @curl_init();
		curl_setopt_array($ch, $options);
		$result = curl_exec ($ch);
		curl_close ($ch);
		
		return $result;
	}
	
	protected function _debug($ch, $xml = null, $retorno = null)
	{
		echo '<br>--------------------------------------------------<br>';
		echo '<h2>Debug</h2>';
		echo "<pre>\n";
		print_r(curl_getinfo($ch));  // get error info
		echo "\n\ncURL error:" . curl_error($ch); 
		
		if ($xml) {
			echo '<br>--------------------------------------------------<br>';
			echo '<h2>Xml</h2>';
			echo htmlentities($xml);
			echo '<br><br>';
		}
		
		if ($retorno) {
			echo '<br>--------------------------------------------------<br>';
			echo '<h2>Retorno</h2>';
			echo "<pre>\n";
			echo htmlentities($retorno);
			echo '<br><br>';
		}
		
		
	}
}
?>