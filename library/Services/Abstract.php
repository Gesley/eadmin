<?php
abstract class Services_Abstract 
{
	/**
	 * Retorna um objeto SoapClient
	 *
	 * @param string $strTipo
	 * @return objeto SoapClient
	 */
	public function gerarCliente($wsdl) 
	{
		$ctx = stream_context_create(array(
		    'http' => array(
		        'timeout' => 1
		        )
		    )
		);
		//file_get_contents("http://example.com/", 0, $ctx); 
		try {
			if (! file_get_contents ( $wsdl, 0, $ctx )) {
				throw new SoapFault ( 'Server', 'WSDL nâo encontrado em:' . $wsdl );
			}
			
			return new SoapClient ( $wsdl, array ('style' => "mime", 'style' => SOAP_RPC, 'use' => SOAP_ENCODED, 'location' => $wsdl ) );
			
		} catch ( SoapFault $soapFault ) {
			return null;
		}
	}
	
	public function converterXmlArray($strXml) 
	{
		$ret = array ();
		
		if ($strXml == '<NewDataSet />') {
			return $ret;
		}
		
		$objXml = new SimpleXMLElement ( $strXml );
		/*
		$ret ['NumeroSeguranca'] = ( string ) $objXml->Table [0]->NumeroSeguranca;
		$ret ['Uf'] = ( string ) $objXml->Table [0]->Uf;
		$ret ['Organizacao'] = ( string ) $objXml->Table [0]->Organizacao;
		$ret [] = ( string ) $objXml->Table [0]->Nome;
		$ret [] = ( string ) $objXml->Table [0]->NomePai;
		$ret [] = ( string ) $objXml->Table [0]->NomeMae;
		$ret [] = ( string ) $objXml->Table [0]->Inscricao;
		$ret [] = ( string ) $objXml->Table [0]->Cpf;
		$ret [] = ( string ) $objXml->Table [0]->TipoInscricao;
		$ret [] = ( string ) $objXml->Table [0]->Situacao;
		$ret [] = ( string ) $objXml->Table [0]->Logradouro;
		$ret [] = ( string ) $objXml->Table [0]->Bairro;
		$ret [] = ( string ) $objXml->Table [0]->Cidade;
		$ret [] = ( string ) $objXml->Table [0]->Cep;
		$ret [] = ( string ) $objXml->Table [0]->DDD;
		$ret [] = ( string ) $objXml->Table [0]->Telefone;
		*/
		$ret = get_object_vars($objXml->Table[0]);
		return array_map('utf8_decode',$ret);
	}
	
	public function cjf_converterXmlCnpjArray($strXml)
	{
		$values = explode ( ";", utf8_decode($strXml) );
		$keys = array(
			'cnpj','ide_matriz_filial','razao_social','nome_fantasia','tipo_logradouro_pj',
			'logradouro_pj','num_logradouro_pj','complemento_pj','bairro_pj','cep_pj','cod_municipio_pj',
			'municipio_pj','sigla_uf_pj','ddd_telefone_pj_1','num_telefone_pj_1','ddd_telefone_pj_2',
			'num_telefone_pj_2','ddd_telefone_pj_fax','num_telefone_pj_fax','correio_eletronico_pj',
			'ind_socio','cnae_fiscal','des_cnae_fiscal','cod_natureza_juridica','des_natureza_juridica',
			'data_abertura_pj','data_situacao_cnpj','cod_situacao_cadastral','des_situacao_cadastral',
			'nire','cpf_responsavel','nome_responsavel','tipo_logradouro_responsavel','logradouro_responsavel',
			'num_logradouro_responsavel','complemento_responsavel','bairro_responsavel','cep_responsavel',
			'cod_municipio_responsavel','municipio_responsavel','sigla_uf_responsavel','ddd_telefone_responsavel',
			'correio_eletronico_responsavel','cod_qualificacao_responsavel','des_qualificacao_responsavel','situacao_atualizacao');
		
		return array_combine($keys, $values);
		/*
		 * 'ide_matriz_filial_desc'
		 * */
	}
	
	public function cjf_converterXmlCpfArray($strXml)
	{
		$strXml = str_replace('null','aaa', $strXml);
		var_dump($strXml);
		$ret [] = explode ( ";", utf8_decode($strXml ));
			
		//return $ret;
		
		//$values = array_map('trim',$ret[0]);
		$values = array_map('trim',$ret[0]);
		$qtd = count($values);
		if($qtd > 15){
			if ($qtd == 16) {
				$values[9] .= ' - ' . $values[10];
				unset($values[10]);
			} else {
				$values[7] .= ', ' . $values[8] . ', '  . $values[9];
				unset($values[8]);
				unset($values[9]);
			}
		}
		
		
		$keys = array(
			'cpf','nome','data_nascimento','sexo','nome_mae','num_titulo_eleitor','tipo_logradouro',
			'logradouro','num_logradouro','complemento','bairro','municipio','sigla_uf','cep','situacao_cadastral');
		
		return array_combine($keys, $values);
		
		/**
		 * 'ide_matriz_filial_desc'
		 */
		
		
	}
	
	public function _cjf_converterXmlCpfArray($strXml)
	{
		if (is_array ( $obj->item )) {
			foreach ( $obj->item as $dados ) {
				$ret [] = explode ( ";", utf8_decode($dados) );
			}
		} else {
			$ret [] = explode ( ";", utf8_decode($obj->item ));
		}
			
		$values = array_map('trim',$ret);
		$keys = array(
			'cpf','nome','data_nascimento','sexo','nome_mae','num_titulo_eleitor','tipo_logradouro',
			'logradouro','num_logradouro','complemento','bairro','municipio','sigla_uf','cep','situacao_cadastral');
		
		return array_combine($keys, $values);
		/*
		 * 'ide_matriz_filial_desc'
		 * */
	}
	
	public function removerAcento($str)
	{
	 	
		$from = '��������������������������';
	    $to   = 'AAAAEEIOOOUUCaaaaeeiooouuc';

		return strtr($str, $from, $to);
	}
}
?>