<?php

class Services_Red_Recuperar extends Services_Red_Abstract implements Services_Red_Interface {

    public $parametros;

    public function __construct($desenvolvimento = false) {
        parent::__construct($desenvolvimento);
        $this->urlRed = 'http://' . $this->server . '/REDCentral/autorizacaoRecuperacaoDocumento';
    }

    public function getAutorizacao($comAssinatura = false) {
        //$xml = $this->_getAutorizacao($this->urlRed,$this->getXmlAutorizacao());
        $xml = $this->_getAutorizacao($this->urlRed, $this->parametros->getXml());
        $obj = simplexml_load_string($xml);

        if (isset($obj->Mensagem)) {
            throw new Exception('Erro: ' . $obj->Mensagem['codigo'] . ' - ' . $obj->Mensagem['descricao']);
        }
        if ($comAssinatura) {
            return array(
                'URL_ARQUIVO' => (string) $obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[0]['url'],
                'URL_ASSINATURA' => (string) $obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[1]['url']
            );
        } else {
            return array(
                'tipoArquivo' => (string) $obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML['tipoArquivo'],
                'url' => (string) $obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML['url']
            );
        }
    }

    public function recuperar(Services_Red_Parametros_Interface $parametros, $comAssinatura = false) {
        $this->parametros = $parametros;

        try {
            return $this->getAutorizacao($comAssinatura);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function montarRetorno() {
        return null;
    }

}

?>