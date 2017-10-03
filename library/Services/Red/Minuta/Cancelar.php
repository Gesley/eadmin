<?php
class Services_Red_Minuta_Cancelar extends Services_Red_Abstract implements Services_Red_Interface
{
	public $parametros;
	
	public function __construct($desenvolvimento = false)
	{
		parent::__construct($desenvolvimento);
		$this->urlRed = 'http://' . $this->server . '/REDCentral/cancelarDocumento';
	}
	
	public function getAutorizacao()
	{
		$xml = $this->_getAutorizacao($this->urlRed,$this->parametros->getXml());
		$obj = @simplexml_load_string($xml);
		
		if(isset($obj->Mensagem)){	
			throw new Exception('Erro: ' . $obj->Mensagem['codigo'] . ' - ' . $obj->Mensagem['descricao']);
		}
		//var_dump($xml);
		//exit;
		return $obj;
		return array(
			'tipoArquivo' => $obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[$i]['tipoArquivo'],
			'url'         => $obj->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[$i]['url']
		);
	}
	/**
     *
     * @param Services_Red_Parametros_Interface $parametros
     * @return type 
     */
	public function cancelar (Services_Red_Minuta_Parametros_Interface $parametros)
	{
		$this->parametros = $parametros;
	//echo $this->getXml();
		try {
			$this->getAutorizacao();
			return $this->xmlSaida;
			//return $this->montarRetorno();
		} catch (Exception $e) {
			var_dump($e);
		}
	}
	
	public function montarRetorno()
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
}
?>