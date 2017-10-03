<?php

class Services_Red_Incluir extends Services_Red_Abstract implements Services_Red_Interface {

    public $parametros;
    public $metadados;

    public function __construct($desenvolvimento = false) 
    {
        parent::__construct($desenvolvimento);
        $this->urlRed = 'http://' . $this->server . '/REDCentral/autorizacaoinclusao';
    }

    public function getAutorizacao() 
    {
        $xml = $this->_getAutorizacao($this->urlRed, $this->parametros->getXml());
        $obj = simplexml_load_string($xml);
        
        if (isset($obj->Mensagem)) {
            throw new Exception('Erro: ' . $obj->Mensagem['codigo'] . ' - ' . $obj->Mensagem['descricao']);
        }
        return (string) $obj->retornoSolicitacaoInclusao['url'];
    }

    public function incluir(Services_Red_Parametros_Interface $parametros, Services_Red_Metadados_Interface $metadados, $file) 
    {
        $this->parametros = $parametros;
        $this->metadados = $metadados;

        try {
            $url = $this->getAutorizacao();
            $this->xmlSaida = $this->_upload($url, $this->metadados->getXml(), $file, $this->parametros->login, $this->parametros->ip);
            unlink(realpath($file));
            return $this->montarRetorno();
        } catch (Exception $e) {
            unlink(realpath($file));
            return $e->getMessage();
        }
    }

    public function incluirComAssinatura(Services_Red_Parametros_Interface $parametros, Services_Red_Metadados_Interface $metadados, $file, $assinatura) 
    {
        $this->parametros = $parametros;
        $this->metadados = $metadados;

        try {
            $url = $this->getAutorizacao();
            $this->xmlSaida = $this->_upload($url, $this->metadados->getXml(), $file, $this->parametros->login, $this->parametros->ip, $assinatura);
            unlink(realpath($assinatura));
            unlink(realpath($file));
            return $this->montarRetorno();
        } catch (Exception $e) {
            unlink(realpath($assinatura));
            unlink(realpath($file));
            return $e->getMessage();
        }
    }

    public function montarRetorno() 
    {
        $obj = simplexml_load_string($this->xmlSaida);
        if (isset($obj->Mensagem)) {
            throw new Exception('Erro: ' . $obj->Mensagem['codigo'] . '|' . $obj->Mensagem['descricao'] . '|' . $obj->Mensagem['idDocumento']);
        }

        return array(
            'numeroDocumento' => (string) $obj->retornoConclusaoInclusao['numeroDocumento'],
            'hashConteudo' => (string) $obj->retornoConclusaoInclusao['hashConteudo'],
            'mensagem' => 'Codigo: ' . $obj->retornoConclusaoInclusao->mensagem['codigo'] . ' - ' . $obj->retornoConclusaoInclusao->mensagem['descricao'],
        );
    }

}