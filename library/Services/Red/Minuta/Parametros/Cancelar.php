<?php

class Services_Red_Minuta_Parametros_Cancelar implements Services_Red_Minuta_Parametros_Interface {

    public $login;
    public $ip;
    public $sistema;
    public $nomeMaquina;
    public $numeroDocumento;

    public function __construct() {
        $this->login = 'TR224PS';
        $this->ip = '172.16.5.62';
        $this->sistema = 'JURIS';
        $this->nomeMaquina = 'DISAD010-TRF1';
    }

    public function getXml() {
        $doc = new DOMDocument();
        $root = $doc->createElement('root');
        $doc->appendChild($root);

        $solicitanteCancelamento = $doc->createElement('solicitanteCancelamento');
        $root->appendChild($solicitanteCancelamento);

        $login = $doc->createAttribute('login');
        $solicitanteCancelamento->appendChild($login);
        $loginTexto = $doc->createTextNode($this->login);
        $login->appendChild($loginTexto);

        $ip = $doc->createAttribute('ip');
        $solicitanteCancelamento->appendChild($ip);
        $ipTexto = $doc->createTextNode($this->ip);
        $ip->appendChild($ipTexto);

        $sistema = $doc->createAttribute('sistema');
        $solicitanteCancelamento->appendChild($sistema);
        $sistemaTexto = $doc->createTextNode($this->sistema);
        $sistema->appendChild($sistemaTexto);

        $nomeMaquina = $doc->createAttribute('nomeMaquina');
        $solicitanteCancelamento->appendChild($nomeMaquina);
        $nomeMaquinaTexto = $doc->createTextNode($this->nomeMaquina);
        $nomeMaquina->appendChild($nomeMaquinaTexto);

        $numeroDocumento = $doc->createAttribute('numeroDocumento');
        $solicitanteCancelamento->appendChild($numeroDocumento);
        $numeroDocumentoTexto = $doc->createTextNode($this->numeroDocumento);
        $numeroDocumento->appendChild($numeroDocumentoTexto);

        return $doc->saveXML($root);
    }

}

?>