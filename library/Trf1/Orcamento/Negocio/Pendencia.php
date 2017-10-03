<?php
/**
 * @deprecated EVITAR USO! Procurar a nova classe: Orcamento_Business_Negocio_Pendencia
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Pendencia
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Pendências do sistema
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
class Trf1_Orcamento_Negocio_Pendencia extends Orcamento_Business_Negocio_Pendencia {
    // OBSOLETO //
}

class Trf1_Teste
{
	/**
	 * Método construtor
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct () {
		//
	}
	
	/**
	 * Verifica se há alguma pendência nos dados do sistema que necessite intervenção
	 *
	 * @param	$ano
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaPendencias ( $ano ) {
		if (! isset ( $ano )) {
			//$ano = new Zend_Db_Expr ( "TO_CHAR(SYSDATE, 'YYYY')" );
			$ano = date ( 'Y' );
		}
		
		$sql = $this->retornaSqlPendencias ( $ano );
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Verifica se há alguma pendência nos dados do sistema que necessite intervenção
	 *
	 * @param	$ano
	 * @return	boolean		SE há ou não pendências para o ano informado
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function haPendencias ( $ano ) {
		$sql = "
SELECT
	CASE SUM(Qtde)
		WHEN 0 THEN 0
		ELSE 1
	END AS QTDE_REGISTROS
FROM
	(
	" . $this->retornaSqlQtde_NE_SEM_RDO ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_NE_INCONSISTENTE ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_RDO_SEM_NE ( $ano ) . "	
	UNION	
	" . $this->retornaSqlQtde_NC_SEM_DESPESA ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_NC_SEM_DESPESA_RESERVA ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_NC_SEM_TIPO_NC ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_NC_INCONSISTENTE ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_NC_INCONSISTENTE_RESERVA ( $ano ) . "
	UNION	
	" . $this->retornaSqlQtde_SOLICITACAO_ABERTA_DESPESA ( $ano ) . "
	UNION
	" . $this->retornaSqlQtde_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano ) . "
	)
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		/* Zend_Debug::dump($sql); */
		return $banco->fetchOne ( $sql );
	}
	
	/**
	 * Monta as diversas querys para a verificação de existência de pendências, ou não, do sistema
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlPendencias ( $ano ) {
		$sql = "
SELECT
	(" . $this->retornaSqlQtde_NE_SEM_RDO ( $ano ) . ")							AS QTDE_NE_SEM_RDO,
	(" . $this->retornaSqlQtde_NE_INCONSISTENTE ( $ano ) . ")	AS QTDE_NE_INCONSISTENTE,
	(" . $this->retornaSqlQtde_RDO_SEM_NE ( $ano ) . ")							AS QTDE_RDO_SEM_NE,
	
	(" . $this->retornaSqlQtde_NC_SEM_DESPESA ( $ano ) . ")				        AS QTDE_NC_SEM_DESPESA,
	(" . $this->retornaSqlQtde_NC_SEM_DESPESA_RESERVA ( $ano ) . ")				AS QTDE_NC_SEM_DESPESA_RESERVA,
	(" . $this->retornaSqlQtde_NC_SEM_TIPO_NC ( $ano ) . ")				        AS QTDE_NC_SEM_TIPO_NC,
	(" . $this->retornaSqlQtde_NC_INCONSISTENTE ( $ano ) . ")                   AS QTDE_NC_INCONSISTENTE,
	(" . $this->retornaSqlQtde_NC_INCONSISTENTE_RESERVA ( $ano ) . ")           AS QTDE_NC_INCONSISTENTE_RESERVA,
	
	(" . $this->retornaSqlQtde_SOLICITACAO_ABERTA_DESPESA ( $ano ) . ")			AS QTDE_SOLICITACAO_DESPESA,
	(" . $this->retornaSqlQtde_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano ) . ")	AS QTDE_SOLICITACAO_MOVIMENTACAO,
	
	(" . $this->retornaSql_ULTIMA_DATA_IMPORT_NE ( $ano ) . ")					AS DATA_ULTIMA_IMPORTACAO_NE,
	(" . $this->retornaSql_ULTIMA_DATA_IMPORT_NC ( $ano ) . ")					AS DATA_ULTIMA_IMPORTACAO_NC,
	(" . $this->retornaSql_ULTIMA_DATA_IMPORT_EXEC ( $ano ) . ")				AS DATA_ULTIMA_IMPORTACAO_EXEC,
	(" . $this->retornaSql_ULTIMA_DATA_IMPORT_ND ( $ano ) . ")					AS DATA_ULTIMA_IMPORTACAO_ND
FROM
	Dual
		";
		
		return $sql;
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de notas de empenho sem identificação de despesa
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NE_SEM_RDO ( $ano ) {
		return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_NE_SEM_RDO ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de notas de empenho sem identificação de despesa
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_NE_SEM_RDO ( $ano ) {
	    return "SELECT NOEM_CD_NOTA_EMPENHO FROM CEO_TB_NOEM_NOTA_EMPENHO WHERE NOEM_NR_DESPESA IS NULL AND SUBSTR(NOEM_CD_NOTA_EMPENHO, 1, 4) = $ano AND NOEM_CD_NE_REFERENCIA IS NULL";
	    
	    // TODO Ver uso da instrução sql abaixo num futuro próximo
	    // $negocio = new Trf1_Orcamento_Negocio_Ne ();
	    //
	    // $sql = $negocio->retornaSqlListagem ( $ano );
	    // return $sql;
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de inconsitências entre os dados da nota de empenho e a respectiva despesa relacionada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NE_INCONSISTENTE ( $ano ) {
	    $negocio = new Trf1_Orcamento_Negocio_Ne ();
	
	    return "SELECT COUNT(*) Qtde FROM (" . $negocio->retornaSqlListagemInconsistencia ( $ano ) . ")";
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de RDOs não utilizadas até então em nenhuma nota de empenho original
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_RDO_SEM_NE($ano) {
	    return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_RDO_SEM_NE ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de RDOs não utilizadas até então em nenhuma nota de empenho original
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_RDO_SEM_NE($ano) {
	    return "SELECT REQV_NR_DESPESA FROM CEO_TB_REQV_REQU_VARIACAO WHERE REQV_NR_DESPESA NOT IN (SELECT DISTINCT(NOEM_NR_DESPESA) FROM CEO_TB_NOEM_NOTA_EMPENHO WHERE NOEM_NR_DESPESA IS NOT NULL AND SUBSTR(NOEM_CD_NOTA_EMPENHO, 1, 4) = $ano AND NOEM_CD_NE_REFERENCIA IS NULL) ";
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de notas de crédito sem identificação de despesa ou tipo de nota de crédito
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NC_SEM_DESPESA ( $ano ) {
	    return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_NC_SEM_DESPESA ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de notas de crédito sem identificação de despesa ou tipo de nota de crédito
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_NC_SEM_DESPESA ( $ano ) {
	    return "SELECT NOCR_CD_NOTA_CREDITO FROM CEO_TB_NOCR_NOTA_CREDITO WHERE NOCR_CD_NOTA_CREDITO LIKE '$ano%' AND ( NOCR_NR_DESPESA IS NULL OR NOCR_NR_DESPESA <= 0 )";
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de notas de crédito sem identificação de despesa ou tipo de nota de crédito
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NC_SEM_DESPESA_RESERVA ( $ano ) {
	    return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_NC_SEM_DESPESA_RESERVA ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de notas de crédito sem identificação de despesa ou tipo de nota de crédito
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_NC_SEM_DESPESA_RESERVA ( $ano ) {
	    return "SELECT NOCR_CD_NOTA_CREDITO FROM CEO_TB_NOCR_NOTA_CREDITO WHERE NOCR_CD_NOTA_CREDITO LIKE '$ano%' AND ( NOCR_NR_DESPESA_RESERVA IS NULL OR NOCR_NR_DESPESA_RESERVA <= 0 )";
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de notas de crédito sem identificação de despesa ou tipo de nota de crédito
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NC_SEM_TIPO_NC ( $ano ) {
	    return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_NC_SEM_TIPO_NC ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de notas de crédito sem identificação de despesa ou tipo de nota de crédito
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_NC_SEM_TIPO_NC ( $ano ) {
	    return "SELECT NOCR_CD_NOTA_CREDITO FROM CEO_TB_NOCR_NOTA_CREDITO WHERE NOCR_CD_NOTA_CREDITO LIKE '$ano%' AND NOCR_CD_TIPO_NC IS NULL";
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de de inconsistências entre os dados da nota de crédito e a respectiva despesa relacionada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NC_INCONSISTENTE ( $ano ) {
	    $negocio = new Trf1_Orcamento_Negocio_Nc ();	     
	    return "SELECT COUNT(*) Qtde FROM (" . $negocio->retornaSqlListagemInconsistencia ( $ano ) . ")";
	}

	/**
	 * Instrução sql que apresenta a quantidade de de inconsistências entre os dados da nota de crédito e a respectiva despesa reserva relacionada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_NC_INCONSISTENTE_RESERVA ( $ano ) {
	    $negocio = new Trf1_Orcamento_Negocio_Nc ();
	     
	    return "SELECT COUNT(*) Qtde FROM (" . $negocio->retornaSqlListagemInconsistenciaReserva ( $ano ) . ")";

	}
	
	/**
	 * Instrução sql que apresenta a quantidade de  de solicitações de novas despesas com status de solicitada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_SOLICITACAO_ABERTA_DESPESA($ano) {
	    return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_SOLICITACAO_ABERTA_DESPESA ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de solicitações de novas despesas com status de solicitada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_SOLICITACAO_ABERTA_DESPESA($ano) {
	    return "SELECT * FROM CEO_TB_SOLD_SOLIC_DESPESA WHERE SOLD_DH_EXCLUSAO_LOGICA IS NULL AND SOLD_CD_TIPO_SOLICITACAO = 1";
	}
	
	/**
	 * Instrução sql que apresenta a quantidade de solicitações de novas movimentações de crédito com status de solicitada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSqlQtde_SOLICITACAO_ABERTA_MOVIMENTACAO($ano) {
	    return "SELECT COUNT(*) Qtde FROM (" . $this->retornaSql_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano ) . ') ';
	}
	
	/**
	 * Instrução sql que apresenta as ocorrências de solicitações de novas movimentações de crédito com status de solicitada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_SOLICITACAO_ABERTA_MOVIMENTACAO($ano) {
	    return "SELECT * FROM CEO_TB_MOVC_MOVIMENTACAO_CRED WHERE MOVC_DH_EXCLUSAO_LOGICA IS NULL AND MOVC_CD_TIPO_SOLICITACAO = 1";
	}
	
	/**
	 * Instrução sql que
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_ULTIMA_DATA_IMPORT_NE() {
	    return "'-'";
	}
	
	/**
	 * Instrução sql que
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_ULTIMA_DATA_IMPORT_EXEC() {
	    return "'-'";
	}
	
	/**
	 * Instrução sql que
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_ULTIMA_DATA_IMPORT_NC() {
	    $data = Trf1_Orcamento_Definicoes::FORMATO_DATA;
	    return "SELECT TO_CHAR(MAX(NOCR_DH_NC), '$data') DATA FROM CEO_TB_NOCR_NOTA_CREDITO ";
	}
	
	/**
	 * Instrução sql que
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_ULTIMA_DATA_IMPORT_ND() {
	    return "'-'";
	}
	
	/**
	 * Monta o conteúdo a ser exibido na view, conforme os parâmetros informados
	 *
	 * @param	string	$texto
	 * @param	numeric	$qtde
	 * @param	string	$linkBase
	 * @param	string	$linkComplemento
	 * @param	boolean	$critico
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaLinhaConteudo($texto, $qtde, $linkBase, $linkComplemento, $critico = true) {
	    $erro = '';
	    $link = '';
	
	    // Baseado
	    if ($qtde > 0) {
	        // Tipifica o erro
	        $erro = ($critico ? " class='alert'" : " class='notice'");
	        $erroDescricao = 'Listar ocorrências'; /*($critico ? "Corrigir ocorrências" : "Verificar ocorrências");*/
	        	
	        // Monta link a ser utilizado na funcionalidade
	        $link = '';
	        if ($linkComplemento) {
	            $link = "<a href='" . $linkBase . $linkComplemento . "' target='_blank'> $erroDescricao </a>";
	        }
	    }
	
	    $retorno = "";
	    $retorno .= "<tr $erro>" . PHP_EOL;
	    $retorno .= "	<td width='80%'> $texto </td>" . PHP_EOL;
	    $retorno .= "	<td width='05%'> $qtde </td>" . PHP_EOL;
	    $retorno .= "	<td width='15%'> $link </td>" . PHP_EOL;
	    $retorno .= "</tr>" . PHP_EOL . PHP_EOL;
	
	    return $retorno;
	}
	
	/**
	 * Lista os anos controlados pelo sistema
	 *
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaAnosSistema() {
	    $sql = "
SELECT
	DESP_AA_DESPESA,
	DESP_AA_DESPESA			ANO
FROM
	CEO_TB_DESP_DESPESA
GROUP BY
	DESP_AA_DESPESA
ORDER BY
	DESP_AA_DESPESA			DESC
				";
	
	    $banco = Zend_Db_Table::getDefaultAdapter ();
	
	    return $banco->fetchPairs ( $sql );
	}
	
	public function retornaMeses() {
	    return array (
	            1 => 'Janeiro',
	            2 => 'Fevereiro',
	            3 => 'Março',
	            4 => 'Abril',
	            5 => 'Maio',
	            6 => 'Junho',
	            7 => 'Julho',
	            8 => 'Agosto',
	            9 => 'Setembro',
	            10 => 'Outubro',
	            11 => 'Novembro',
	            12 => 'Dezembro'
	    );
	}
	
	//
	//
	//
	// Revisar instruções sql abaixo;
	// Se ok, transpor para cima! Sendo QTDE em cima da sql base
	//
	//
	//
	
	/**
	 * Instrução sql que apresenta as ocorrências de inconsitências entre os dados da nota de empenho e a respectiva despesa relacionada
	 *
	 * @param	$ano
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaSql_NE_PTRES_ELEMENTO_INCONSISTENTE ( $ano ) {
		$sql = "
SELECT
	/*
	N.NOEM_CD_FONTE,
	D.DESP_CD_FONTE,
	N.NOEM_CD_PT_RESUMIDO,
	D.DESP_CD_PT_RESUMIDO,
	N.NOEM_CD_ELEMENTO_DESPESA_SUB,
	D.DESP_CD_ELEMENTO_DESPESA_SUB,
	N.NOEM_CD_VINCULACAO,
	D.DESP_CD_VINCULACAO,
	N.NOEM_CD_CATEGORIA,
	D.DESP_CD_CATEGORIA
	*/
	NOEM_CD_NOTA_EMPENHO
FROM
	CEO_TB_NOEM_NOTA_EMPENHO		N
Left JOIN
	CEO_TB_DESP_DESPESA				D ON
		D.DESP_NR_DESPESA = N.NOEM_NR_DESPESA
WHERE
	SUBSTR(N.NOEM_CD_NOTA_EMPENHO, 1, 4) = $ano			AND 
	N.NOEM_NR_DESPESA IS NOT NULL							OR
	(
		/* N.NOEM_CD_FONTE <> D.DESP_CD_FONTE			OR */
		N.NOEM_CD_PT_RESUMIDO <> D.DESP_CD_PT_RESUMIDO	OR
		SUBSTR(N.NOEM_CD_ELEMENTO_DESPESA_SUB, 1, 6)	<>
		SUBSTR(D.DESP_CD_ELEMENTO_DESPESA_SUB, 1, 6)
	)
				";
		
		return $sql;
	}
	
}
