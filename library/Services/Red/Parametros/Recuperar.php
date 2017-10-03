<?php
class Services_Red_Parametros_Recuperar implements Services_Red_Parametros_Interface
{
	public $numeroDocumento;
	public $login;
	public $ip;
	public $sistema;
	public $nomeMaquina;
	 
	public function __construct()
	{
		$this->login       = 'TR224PS';
		$this->ip          = '172.16.5.62';
		$this->sistema     = 'JURIS';
		$this->nomeMaquina = 'DISAD010-TRF1';
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
	}
}
?>