<?php
/**
 * Classe negocial - Lotações
 * 
 * @category	TRF1
 * @package		Trf1_Guardiao_Lotacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
class Trf1_Guardiao_Negocio_Lotacao {
	/**
	 * Retorna as seção/lotações filhas de uma dada seção/lotação
	 *
	 * @param	string		$secaoPai
	 * @param	int			$lotacaoPai
	 * @return	array							Contendo os apenas códidos das lotações filhas
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getLotacoesFilhas($secaoPai, $lotacaoPai) {
		$sql = "
SELECT
	LOTA_SIGLA_SECAO,
	LOTA_COD_LOTACAO
	
	/*
	LOTA_SIGLA_SECAO,
	LOTA_COD_LOTACAO,
	LOTA_DSC_LOTACAO,
	LOTA_SIGLA_LOTACAO
	*/
FROM
	(
	SELECT
		LOTA_SIGLA_SECAO,
		LOTA_COD_LOTACAO,
		LOTA_LOTA_COD_LOTACAO_PAI,
		LOTA_DSC_LOTACAO,
		LOTA_SIGLA_LOTACAO
	FROM
		RH_CENTRAL_LOTACAO
	WHERE
		LOTA_SIGLA_SECAO			= '$secaoPai'	AND
		LOTA_DAT_FIM				IS NULL
	)

CONNECT BY PRIOR
	LOTA_COD_LOTACAO				= LOTA_LOTA_COD_LOTACAO_PAI

START WITH
	LOTA_COD_LOTACAO				= $lotacaoPai
		";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchAll ( $sql );
	}
	
	/**
	 * Retorna uma string (para uso em query) das seção/lotações filhas de uma dada seção/lotação
	 *
	 * @param	string		$secaoPai
	 * @param	int			$lotacaoPai
	 * @return	string							Contendo os apenas códidos das lotações filhas
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 * @example Sql = "SELECT ... WHERE COD_LOTACAO IN (" . getLotacoesFilhasSTR(CODIGO) . ") "
	 * O retorno ficaria: SELECT ... WHERE COD_LOTACAO IN (101, 102, 103)
	 */
	public function getLotacoesFilhasSTR($secaoPai, $lotacaoPai) {
		return implode ( ', ', $this->getLotacoesFilhas ( $secaoPai, $lotacaoPai ) );
	}
	
	/**
	 * Apresenta as lotações filhas a partir de uma Lotacao pai escolhida utilizando Ajax
	 *
	 * @param    string       $descricao      Descriçao da lotacao pesquisada
	 * @param    string       $siglaSecao     Sigla da lotaçao 
	 * @param    int          $codigo         Código da lotação
	 * @return   array
	 * @author   Dayane Freire
	 */
	public function getLotacoesFilhasAjax($descricao, $siglaSecao, $codigo) {
		
		$sql = "
SELECT 
       LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),'-',' ') ||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS label,
       LOTA_COD_LOTACAO AS COD,
       LOTA_SIGLA_SECAO AS SIGLA
FROM    
  (
    SELECT  
            LOTA_SIGLA_SECAO, 
            LOTA_COD_LOTACAO, 
            LOTA_LOTA_COD_LOTACAO_PAI, 
            LOTA_TIPO_LOTACAO, 
            LOTA_DSC_LOTACAO, 
            LOTA_SIGLA_LOTACAO            
    FROM (                           
            SELECT LOTA_SIGLA_SECAO, 
                   LOTA_COD_LOTACAO, 
                   LOTA_LOTA_COD_LOTACAO_PAI, 
                   LOTA_TIPO_LOTACAO, 
                   LOTA_DSC_LOTACAO, 
                   LOTA_SIGLA_LOTACAO
            FROM 
                    RH_CENTRAL_LOTACAO
            WHERE   
                    LOTA_SIGLA_SECAO   = '$siglaSecao'   AND  
                    LOTA_DAT_FIM       IS Null
        )
    CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
    START WITH LOTA_COD_LOTACAO = $codigo
)
WHERE 
    UPPER(LOTA_SIGLA_LOTACAO||' - '||LOTA_COD_LOTACAO||' - '||LOTA_DSC_LOTACAO)    Like UPPER('%$descricao%')
ORDER BY 1 DESC
                           ";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		return $banco->fetchAll ( $sql );
	}
	
public function getTodasLotacoesAjax($descricao = null, $codigo = null, $sigla = null) {

	if($codigo != null){
	 $codigo = "LOTA_COD_LOTACAO = $codigo  AND LOTA_SIGLA_SECAO = '$sigla' ";	
	}else{
		$codigo = "	UPPER(LOTA_SIGLA_LOTACAO||' - '||LOTA_COD_LOTACAO||' - '||LOTA_DSC_LOTACAO)    Like UPPER('%$descricao%')";
	}
	
$sql = "SELECT DISTINCT
			LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),'-',' ') ||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS label,
			LOTA_COD_LOTACAO AS COD,
			LOTA_SIGLA_SECAO AS SIGLA
		FROM    
            RH_CENTRAL_LOTACAO
        WHERE   
            LOTA_DAT_FIM       IS Null
		 AND
		 $codigo
		ORDER BY 1 DESC
                           ";
		$banco = Zend_Db_Table::getDefaultAdapter();
		return $banco->fetchAll($sql);
	}

}
