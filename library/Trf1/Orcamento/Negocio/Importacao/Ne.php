<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Importacao_Ne
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Importação de dados das notas de empenho (NE)
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * ====================================================================================================
 * TUTORIAL
 * ====================================================================================================
 * Descrever...
 * 
 */
class Trf1_Orcamento_Negocio_Importacao_Ne {
	/* ************************************************************
	 * Variáveis e funções 'básicas'
	 *************************************************************/
	/**
	 * Model das Notas de Crédito
	 */
	protected $_dados = null;
	
	/**
	 * 
	 */
	protected $_tipoImportacao = 'ne';
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbNoemNotaEmpenho ();
	}
	
	/**
	 * Valida os registros importados do tipo NC
	 * 
	 * @param	string		$tipoImportacao
	 * @throws	Zend_Exception
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function validaRegistros() {
		$tipoImportacao = strtoupper ( $this->_tipoImportacao );
		
		try {
			$sql = "BEGIN ";
			
			// Limpa todos os erros para nova importação
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = Null WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao';";
			
			// Chave primária não identificada
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_NAO_IDENTIFICADA . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND INSTR(SUBSTR(IMPD_TX_LINHA, 30, 12), '$tipoImportacao') = 0;";
			
			// Chave primária já existente
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_EXISTE . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 30, 12) || SUBSTR(IMPD_TX_LINHA, 50, 6) IN (SELECT NOCR_CD_NOTA_CREDITO FROM CEO_TB_NOCR_NOTA_CREDITO);";
			
			// Unidade gestora - Operador
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_UG_OPERADOR . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 13, 6) NOT IN (SELECT UNGE_CD_UG FROM CEO_TB_UNGE_UNIDADE_GESTORA);";
			
			// Unidade gestora - Favorecida
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_UG_FAVORECIDA . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 50, 6) NOT IN (SELECT UNGE_CD_UG FROM CEO_TB_UNGE_UNIDADE_GESTORA);";
			
			// Fonte
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_FONTE . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 304, 3) NOT IN (SELECT FONT_CD_FONTE FROM CEO_TB_FONT_FONTE);";
			
			// PTRES
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_PTRES . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 297, 6) NOT IN (SELECT PTRS_CD_PT_RESUMIDO FROM CEO_TB_PTRS_PROGRAMA_TRABALHO);";
			
			// Natureza
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_NATUREZA . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 313, 6) NOT IN (SELECT SUBSTR(EDSB_CD_ELEMENTO_DESPESA_SUB, 1, 6) FROM CEO_TB_EDSB_ELEMENTO_SUB_DESP);";
			
			// Evento
			//$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_EVENTO . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 290, 6) NOT IN (SELECT EVEN_CD_EVENTO FROM CEO_TB_EVEN_EVENTO_NE);";
			
			$sql .= "END; ";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$banco->query ( $sql );
		} catch ( Zend_Exception $e ) {
			$msgErro = "Erro na validações de possíveis erros dos registros a importar.<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
	}
	
	/**
	 * 
	 * @param unknown_type $tipoImportacao
	 * @param unknown_type $referencia
	 * @throws Zend_Exception
	 */
	public function importaDados($referencia) {
		$tipoImportacao = strtoupper ( $this->_tipoImportacao );
		
		try {
			// Definição dos valores para montagem da instrução Sql
			$iniDtTransacao = $referencia ['IT-DA-TRANSACAO'] ['INICIO'];
			$tamDtTransacao = $referencia ['IT-DA-TRANSACAO'] ['TAMANHO'];
			$iniHrTransacao = $referencia ['IT-HO-TRANSACAO'] ['INICIO'];
			$tamHrTransacao = $referencia ['IT-HO-TRANSACAO'] ['TAMANHO'];
			$iniUgOperador = $referencia ['IT-CO-UG-OPERADOR'] ['INICIO'];
			$tamUgOperador = $referencia ['IT-CO-UG-OPERADOR'] ['TAMANHO'];
			$iniNE = $referencia ['GR-UG-GESTAO-AN-NUMERO-NEUQ(1)'] ['INICIO'];
			$tamNE = $referencia ['GR-UG-GESTAO-AN-NUMERO-NEUQ(1)'] ['TAMANHO'];
			$iniReferencia = $referencia ['GR-AN-NU-DOCUMENTO-REFERENCIA'] ['INICIO'];
			$tamReferencia = $referencia ['GR-AN-NU-DOCUMENTO-REFERENCIA'] ['TAMANHO'];
			$iniDtEmissao = $referencia ['IT-DA-EMISSAO'] ['INICIO'];
			$tamDtEmissao = $referencia ['IT-DA-EMISSAO'] ['TAMANHO'];
			$iniObservacao = $referencia ['IT-TX-OBSERVACAO'] ['INICIO'];
			$tamObservacao = $referencia ['IT-TX-OBSERVACAO'] ['TAMANHO'];
			$iniEvento = $referencia ['GR-CODIGO-EVENTO'] ['INICIO'];
			$tamEvento = $referencia ['GR-CODIGO-EVENTO'] ['TAMANHO'];
			$iniEsfera = $referencia ['IT-IN-ESFERA-ORCAMENTARIA'] ['INICIO'];
			$tamEsfera = $referencia ['IT-IN-ESFERA-ORCAMENTARIA'] ['TAMANHO'];
			$iniPTRES = $referencia ['IT-CO-PROGRAMA-TRABALHO-RESUMIDO'] ['INICIO'];
			$tamPTRES = $referencia ['IT-CO-PROGRAMA-TRABALHO-RESUMIDO'] ['TAMANHO'];
			$iniFonte = $referencia ['GR-FONTE-RECURSO'] ['INICIO'];
			$tamFonte = $referencia ['GR-FONTE-RECURSO'] ['TAMANHO'];
			$iniNatureza = $referencia ['GR-NATUREZA-DESPESA'] ['INICIO'];
			$tamNatureza = $referencia ['GR-NATUREZA-DESPESA'] ['TAMANHO'];
			$iniValor = $referencia ['IT-VA-TRANSACAO'] ['INICIO'];
			$tamValor = $referencia ['IT-VA-TRANSACAO'] ['TAMANHO'];
			$iniProcesso = $referencia ['IT-NU-PROCESSO'] ['INICIO'];
			$tamProcesso = $referencia ['IT-NU-PROCESSO'] ['TAMANHO'];
			
			$sql = "BEGIN ";
			
			// Realiza a efetiva importação...
			$sql .= "
INSERT INTO CEO_TB_NOEM_NOTA_EMPENHO (
	NOEM_CD_NOTA_EMPENHO,
	NOEM_CD_NE_REFERENCIA,
	NOEM_CD_UG_OPERADOR,
	NOEM_CD_UG_FAVORECIDO,
	NOEM_DH_NE,
	NOEM_DT_EMISSAO,
	NOEM_DS_OBSERVACAO,
	NOEM_CD_ESFERA,
	NOEM_CD_FONTE,
	NOEM_CD_PT_RESUMIDO,
	NOEM_CD_ELEMENTO_DESPESA_SUB,
	NOEM_CD_VINCULACAO,
	NOEM_CD_CATEGORIA,
	NOEM_CD_EVENTO,
	NOEM_VL_NE,
	NOEM_VL_NE_ACERTADO,
	NOEM_NR_DESPESA,
	NOEM_NU_TIPO_NE
)
SELECT
	SUBSTR(IMPD_TX_LINHA, $iniNE + 11, 12) ||
	SUBSTR(IMPD_TX_LINHA, $iniUgOperador, $tamUgOperador)										AS NOEM_CD_NOTA_EMPENHO,
	SUBSTR(IMPD_TX_LINHA, $iniReferencia + 11, 12) ||
	SUBSTR(IMPD_TX_LINHA, $iniUgOperador, $tamUgOperador)										AS NOEM_CD_NE_REFERENCIA,
	SUBSTR(IMPD_TX_LINHA, $iniUgOperador, $tamUgOperador)										AS NOEM_CD_UG_OPERADOR,
	SUBSTR(IMPD_TX_LINHA, $iniUgOperador, $tamUgOperador)										AS NOEM_CD_UG_FAVORECIDO,
	TO_DATE(SUBSTR(IMPD_TX_LINHA, $iniDtTransacao, $tamDtTransacao) ||
	SUBSTR(IMPD_TX_LINHA, $iniHrTransacao, $tamHrTransacao), 'YYYYMMDDHH24MI')					AS NOEM_DH_NE,
	TO_DATE(SUBSTR(IMPD_TX_LINHA, $iniDtEmissao, $tamDtEmissao), 'YYMMDD')						AS NOEM_DT_EMISSAO,
	TRIM(SUBSTR(IMPD_TX_LINHA, $iniObservacao, $tamObservacao))									AS NOEM_DS_OBSERVACAO,
	SUBSTR(IMPD_TX_LINHA, $iniEsfera, $tamEsfera)												AS NOEM_CD_ESFERA,
	SUBSTR(IMPD_TX_LINHA, $iniFonte + 1, 3)														AS NOEM_CD_FONTE,
	SUBSTR(IMPD_TX_LINHA, $iniPTRES, $tamPTRES)													AS NOEM_CD_PT_RESUMIDO,
	SUBSTR(IMPD_TX_LINHA, $iniNatureza, $tamNatureza) || '00'									AS NOEM_CD_ELEMENTO_DESPESA_SUB,
	400																							AS NOEM_CD_VINCULACAO,
	'C'																							AS NOEM_CD_CATEGORIA,
	SUBSTR(IMPD_TX_LINHA, $iniEvento, $tamEvento)												AS NOEM_CD_EVENTO,
	SUBSTR(IMPD_TX_LINHA, $iniValor, $tamValor) / 100											AS NOEM_VL_NE,
	/* NOEM_VL_NE_ACERTADO, */
	Null																						AS NOEM_NR_DESPESA,
	Null																						AS NOEM_NU_TIPO_NE
	
	/*
	SUBSTR(IMPD_TX_LINHA, $iniNE + 11, 12) ||
	SUBSTR(IMPD_TX_LINHA, $iniUgOperador, $tamUgOperador)										AS NOCR_CD_NOTA_CREDITO,
	SUBSTR(IMPD_TX_LINHA, $iniUgOperador, $tamUgOperador)										AS NOCR_CD_UG_OPERADOR,
	SUBSTR(IMPD_TX_LINHA, $iniUgFavorecida, $tamUgFavorecida)									AS NOCR_CD_UG_FAVORECIDO,
	TO_DATE(SUBSTR(IMPD_TX_LINHA, $iniDtTransacao, $tamDtTransacao) ||
	SUBSTR(IMPD_TX_LINHA, $iniHrTransacao, $tamHrTransacao), 'YYYYMMDDHH24MI')					AS NOCR_DH_NC,
	TO_DATE(SUBSTR(IMPD_TX_LINHA, $iniDtEmissao, $tamDtEmissao), 'YYMMDD')						AS NOCR_DT_EMISSAO,
	TRIM(SUBSTR(IMPD_TX_LINHA, $iniObservacao, $tamObservacao))									AS NOCR_DS_OBSERVACAO,
	SUBSTR(IMPD_TX_LINHA, $iniFonte + 1, 3)														AS NOCR_CD_FONTE,
	SUBSTR(IMPD_TX_LINHA, $iniPTRES, $tamPTRES)													AS NOCR_CD_PT_RESUMIDO,
	SUBSTR(IMPD_TX_LINHA, $iniNatureza, $tamNatureza) || '00'									AS NOCR_CD_ELEMENTO_DESPESA_SUB,
	400																							AS NOCR_CD_VINCULACAO,
	'C'																							AS NOCR_CD_CATEGORIA,
	SUBSTR(IMPD_TX_LINHA, $iniEvento, $tamEvento)												AS NOCR_CD_EVENTO,
	SUBSTR(IMPD_TX_LINHA, $iniValor, $tamValor) / 100											AS NOCR_VL_NC,
	0																							AS NOCR_VL_NC_ACERTADO,
	CEO_FU_RetornaDadosdaNC(TRIM(SUBSTR(IMPD_TX_LINHA, $iniObservacao, $tamObservacao)), 1)		AS NOCR_CD_TIPO_NC,
	CEO_FU_RetornaDadosdaNC(TRIM(SUBSTR(IMPD_TX_LINHA, $iniObservacao, $tamObservacao)), 2)		AS NOCR_NR_DESPESA,
	CEO_FU_RetornaDadosdaNC(TRIM(SUBSTR(IMPD_TX_LINHA, $iniObservacao, $tamObservacao)), 3)		AS NOCR_NR_DESPESA_RESERVA
	*/
FROM
	CEO_TB_IMPD_IMPORTACAO_DADOS
WHERE
	IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND
	IMPD_NR_ERRO IS NULL;
	*/
					";
			
			
			// Ajusta o valor acertado (campo: NOCR_VL_NC_ACERTADO) conforme o evento (campo: NOCR_CD_EVENTO)
			$sql .= "
UPDATE
	CEO_TB_NOCR_NOTA_CREDITO
SET
	NOCR_VL_NC_ACERTADO = NOCR_VL_NC * (-1)
WHERE
	NOCR_CD_EVENTO IN (
		SELECT EVEN_CD_EVENTO FROM CEO_TB_EVEN_EVENTO_NE WHERE EVEN_IC_SINAL_EVENTO = 1
    );
					";
			
			// Exclui os registros importados com sucesso
			$sql .= "
DELETE
FROM
	CEO_TB_IMPD_IMPORTACAO_DADOS
WHERE
	IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND
	IMPD_NR_ERRO IS NULL;
					";
			
			$sql .= "END; ";
			
			// Acerta a string contendo a instrução Sql
			$sql = str_replace ( PHP_EOL, ' ', $sql );
			$sql = str_replace ( "\t", ' ', $sql ); // Tabulação
			$sql = str_replace ( '  ', ' ', $sql );
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$banco->query ( $sql );
		} catch ( Exception $e ) {
			$msgErro = "Erro ao inserir registros validados da importação.<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
	}

}