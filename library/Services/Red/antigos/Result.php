<?php
class RED_Result
{
	const ERROR   =  0;
    const SUCCESS =  1;
    protected $_code;
    protected $_xml;

	
	public function __construct($code,$xml)
	{
		$this->_code = $code;
		$this->_xml  = $xml;
	}
	
	public function toArray()
	{
		switch ($this->_code) {
			case self::ERROR : return $this->retornaErro(); break;
			case self::SUCCESS : return $this->retornaUrl(); break;
			default: return $this->retornaUrl(); break;
		}
	}
	
	public function retornaErro()
	{
		$retorno = array();
		$xmlRedMensagem = simplexml_load_string($this->xml);
		
		if(isset($xmlRedMensagem->Mensagem)){
			for($i=0; $i < sizeof($xmlRedMensagem->Mensagem); $i++) {
				$retorno[] = array(
					'codigo'    => $xmlRedMensagem->Mensagem[$i]['codigo'],
					'descricao' => $xmlRedMensagem->Mensagem[$i]['descricao']
				);
			}
		}
		
		return $retorno;
	}	
	
	public function retornaUrl()
	{
		$retorno = array();
		$xmlRedUrl = simplexml_load_string($this->xml);
		
		for($i=0; $i < sizeof($xmlRedUrl->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML); $i++) {
			$retorno[] = array(
				'tipoArquivo' => $xmlRedMensagem->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[$i]['tipoArquivo'],
				'url'         => $xmlRedMensagem->retornoListaURLArquivoDocumentoXML->retornoURLArquivoDocumentoXML[$i]['url']
			);
		}
		
		return $retorno;
	}

	public function isValid()
    {
        return ($this->_code > 0) ? true : false;
    }
}
?>