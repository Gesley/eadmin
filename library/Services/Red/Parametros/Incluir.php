<?php
class Services_Red_Parametros_Incluir implements Services_Red_Parametros_Interface
{
        public $login;
        public $ip;
        public $sistema;
        public $nomeMaquina;
        public $numeroDocumento;

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

            $solicitante = $doc->createElement('Solicitante');
            $root->appendChild($solicitante);

            $login = $doc->createAttribute('login');
            $solicitante->appendChild($login);
            $loginTexto = $doc->createTextNode($this->login);
            $login->appendChild($loginTexto);

            $ip = $doc->createAttribute('ip');
            $solicitante->appendChild($ip);
            $ipTexto = $doc->createTextNode($this->ip);
            $ip->appendChild($ipTexto);

            $sistema = $doc->createAttribute('sistema');
            $solicitante->appendChild($sistema);
            $sistemaTexto = $doc->createTextNode($this->sistema);
            $sistema->appendChild($sistemaTexto);

            $nomeMaquina = $doc->createAttribute('nomeMaquina');
            $solicitante->appendChild($nomeMaquina);
            $nomeMaquinaTexto = $doc->createTextNode($this->nomeMaquina);
            $nomeMaquina->appendChild($nomeMaquinaTexto);

            $numeroDocumento = $doc->createAttribute('numeroDocumento');
            $solicitante->appendChild($numeroDocumento);
            $numeroDocumentoTexto = $doc->createTextNode($this->numeroDocumento);
            $numeroDocumento->appendChild($numeroDocumentoTexto);
		
		return $doc->saveXML($root);  
	}
}
?>