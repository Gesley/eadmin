<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Importacao_Nc
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Importação de dados das notas de crédito (NC)
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
class Trf1_Orcamento_Negocio_Importacao_Nc {
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
	protected $_tipoImportacao = 'nc';
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbNocrNotaCredito ();
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
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_NAO_IDENTIFICADA . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND INSTR(SUBSTR(IMPD_TX_LINHA, 30, 12), '$tipoImportacao') = 0;";
			
			// Chave primária já existente
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_EXISTE . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 30, 12) || SUBSTR(IMPD_TX_LINHA, 50, 6) IN (SELECT NOCR_CD_NOTA_CREDITO FROM CEO_TB_NOCR_NOTA_CREDITO);";
			
			// Unidade gestora - Operador
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_UG_OPERADOR . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 13, 6) NOT IN (SELECT UNGE_CD_UG FROM CEO_TB_UNGE_UNIDADE_GESTORA);";
			
			// Unidade gestora - Favorecida
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_UG_FAVORECIDA . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 50, 6) NOT IN (SELECT UNGE_CD_UG FROM CEO_TB_UNGE_UNIDADE_GESTORA);";
			
			// Fonte
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_FONTE . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 304, 3) NOT IN (SELECT FONT_CD_FONTE FROM CEO_TB_FONT_FONTE);";
			
			// PTRES
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_PTRES . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 297, 6) NOT IN (SELECT PTRS_CD_PT_RESUMIDO FROM CEO_TB_PTRS_PROGRAMA_TRABALHO);";
			
			// Natureza
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_NATUREZA . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 313, 6) NOT IN (SELECT SUBSTR(EDSB_CD_ELEMENTO_DESPESA_SUB, 1, 6) FROM CEO_TB_EDSB_ELEMENTO_SUB_DESP);";
			
			// Evento
			$sql .= "UPDATE CEO_TB_IMPD_IMPORTACAO_DADOS SET IMPD_NR_ERRO = " . Trf1_Orcamento_Negocio_Importacao::IMPORTACAO_ERRO_NC_EVENTO . " WHERE IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND SUBSTR(IMPD_TX_LINHA, 290, 6) NOT IN (SELECT EVEN_CD_EVENTO FROM CEO_TB_EVEN_EVENTO_NE);";
			
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
			$iniNC = $referencia ['GR-UG-GESTAO-AN-NUMERO-NCUQ'] ['INICIO'];
			$tamNC = $referencia ['GR-UG-GESTAO-AN-NUMERO-NCUQ'] ['TAMANHO'];
			$iniDtEmissao = $referencia ['IT-DA-EMISSAO'] ['INICIO'];
			$tamDtEmissao = $referencia ['IT-DA-EMISSAO'] ['TAMANHO'];
			$iniUgFavorecida = $referencia ['IT-CO-UG-FAVORECIDA'] ['INICIO'];
			$tamUgFavorecida = $referencia ['IT-CO-UG-FAVORECIDA'] ['TAMANHO'];
			$iniObservacao = $referencia ['IT-TX-OBSERVACAO'] ['INICIO'];
			$tamObservacao = $referencia ['IT-TX-OBSERVACAO'] ['TAMANHO'];
			$iniEvento = $referencia ['GR-CODIGO-EVENTO(1)'] ['INICIO'];
			$tamEvento = $referencia ['GR-CODIGO-EVENTO(1)'] ['TAMANHO'];
			$iniEsfera = $referencia ['IT-IN-ESFERA-ORCAMENTARIA(1)'] ['INICIO'];
			$tamEsfera = $referencia ['IT-IN-ESFERA-ORCAMENTARIA(1)'] ['TAMANHO'];
			$iniPTRES = $referencia ['IT-CO-PROGRAMA-TRABALHO-RESUMIDO(1)'] ['INICIO'];
			$tamPTRES = $referencia ['IT-CO-PROGRAMA-TRABALHO-RESUMIDO(1)'] ['TAMANHO'];
			$iniFonte = $referencia ['GR-FONTE-RECURSO(1)'] ['INICIO'];
			$tamFonte = $referencia ['GR-FONTE-RECURSO(1)'] ['TAMANHO'];
			$iniNatureza = $referencia ['GR-NATUREZA-DESPESA(1)'] ['INICIO'];
			$tamNatureza = $referencia ['GR-NATUREZA-DESPESA(1)'] ['TAMANHO'];
			$iniValor = $referencia ['IT-VA-TRANSACAO(1)'] ['INICIO'];
			$tamValor = $referencia ['IT-VA-TRANSACAO(1)'] ['TAMANHO'];
			$iniMes = $referencia ['IT-ME-LANCAMENTO'] ['INICIO'];
			$tamMes = $referencia ['IT-ME-LANCAMENTO'] ['TAMANHO'];
			
			$sql = "BEGIN ";
			
			// Realiza a efetiva importação...
			$sql .= "
INSERT INTO CEO_TB_NOCR_NOTA_CREDITO (
	NOCR_CD_NOTA_CREDITO,
	NOCR_CD_UG_OPERADOR,
	NOCR_CD_UG_FAVORECIDO,
	NOCR_DH_NC,
	NOCR_DT_EMISSAO,
	NOCR_DS_OBSERVACAO,
	NOCR_CD_FONTE,
	NOCR_CD_PT_RESUMIDO,
	NOCR_CD_ELEMENTO_DESPESA_SUB,
	NOCR_CD_VINCULACAO,
	NOCR_CD_CATEGORIA,
	NOCR_CD_EVENTO,
	NOCR_VL_NC,
	NOCR_VL_NC_ACERTADO,
	NOCR_CD_TIPO_NC,
	NOCR_NR_DESPESA,
	NOCR_NR_DESPESA_RESERVA
)
SELECT
	SUBSTR(IMPD_TX_LINHA, $iniNC + 11, 12) ||
	SUBSTR(IMPD_TX_LINHA, $iniUgFavorecida, $tamUgFavorecida)									AS NOCR_CD_NOTA_CREDITO,
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
FROM
	CEO_TB_IMPD_IMPORTACAO_DADOS
WHERE
	IMPD_DS_CLASSE_ARQUIVO = '$tipoImportacao' AND
	IMPD_NR_ERRO IS NULL;
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