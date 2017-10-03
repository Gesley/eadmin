<?php
class Services_Red_Minuta_Recuperar extends Services_Red_Abstract implements Services_Red_Interface
{
	public $parametros;

	public function __construct($desenvolvimento = false)
	{
		parent::__construct($desenvolvimento);
		$this->urlRed = 'http://' . $this->server . '/REDCentral/autorizacaoRecuperacaoDocumento';
        /*
        if ($_SERVER['SERVER_ADDR'] == '172.16.3.106') {
            throw new Exception ('Endereco IP inv�lido.');
        }
         */
	}
	
	public function getAutorizacao ()
	{
		//$xml = $this->_getAutorizacao($this->urlRed,$this->getXmlAutorizacao());
		$xml = $this->_getAutorizacao($this->urlRed,$this->parametros->getXml());
		$obj = simplexml_load_string($xml);
		
		if (isset($obj->Mensagem)) {
			throw new Exception('Erro: ' . $obj->Mensagem['codigo'] . ' - ' . $obj->Mensagem['descricao']);
		}
		
		return array(
			'tipoArquivo' => (string)$obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML['tipoArquivo'],
			'url'         => (string)$obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML['url']
		);
	}
	/**
     *
     * @param Services_Red_Parametros_Interface $parametros
     * @return type 
     */
	public function recuperar (Services_Red_Minuta_Parametros_Interface $parametros)
	{
		$this->parametros = $parametros;
		
		try {
			return $this->getAutorizacao();
		} catch (Exception $e) {
            /*
            if ($this->temp) {
                $this->_log($this->temp . '/log.txt',$parametros);
            }
             */
			throw $e;
		}
	} 
	
	public function montarRetorno()
	{
		return null;
	}	
}
?>