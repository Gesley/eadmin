<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_SadTbCxgsGrupoServico extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_CXGS_GRUPO_SERVICO';
    protected $_primary = 'CXGS_ID_CAIXA_ENTRADA';
    
    /**
     * @abstract VALIDA A REPLICAÇÃO DE UMA CAIXA DO MESMO TIPO NO TRIBUNAL OU EM UMA SEÇÃO OU EM UMA SUBSEÇÃO.
     * @param type $sgsecao
     * @param type $codlotacao
     * @param type $tipolotacao
     * @param type $tipocaixa
     * @return type 
     * 
     */
    public function getValidaReplicacaoCaixaPorTrfSecaoSubsecao($sgsecao, $codlotacao, $tipolotacao, $tipocaixa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         /**
         * TIPO 9 TRF1
         * TIPO 1 SEÇÃO
         * TIPO 2 SUBSEÇÃO
         */
        switch ($tipolotacao) {
            case 9:/* TIPO 9 TRF1*/
                 $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA, TPCX.TPCX_ID_TIPO_CAIXA, TPCX.TPCX_DS_CAIXA_ENTRADA
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                         SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                         SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                         SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                         RH_CENTRAL_LOTACAO RHLOTA
                                     WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                     AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                     AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                     AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                     AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                     AND (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                            (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                                AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        START WITH LOTA_COD_LOTACAO = $codlotacao
                                                    )
                                            )
                                    AND TPCX.TPCX_ID_TIPO_CAIXA = $tipocaixa
                                    AND CXEN.CXEN_DT_EXCLUSAO IS NULL");
            break;
            case 1: /* TIPO 1 SEÇÃO*/
                 $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA, TPCX.TPCX_ID_TIPO_CAIXA, TPCX.TPCX_DS_CAIXA_ENTRADA
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                         SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                         SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                         SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                         RH_CENTRAL_LOTACAO RHLOTA
                                     WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                     AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                     AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                     AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                     AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                     AND (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                            (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                                AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        START WITH LOTA_COD_LOTACAO = $codlotacao
                                                    )
                                            )
                                    AND TPCX.TPCX_ID_TIPO_CAIXA = $tipocaixa
                                    AND CXEN.CXEN_DT_EXCLUSAO IS NULL");
            break;
            case 2: /* TIPO 2 SUBSEÇÃO*/
                 $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA, TPCX.TPCX_ID_TIPO_CAIXA, TPCX.TPCX_DS_CAIXA_ENTRADA
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                         SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                         SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                         SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                         RH_CENTRAL_LOTACAO RHLOTA
                                     WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                     AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                     AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                     AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                     AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                     AND (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                            (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                                AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        AND LOTA_TIPO_LOTACAO NOT IN (2) /*SUBSEÇÃO*/
                                                        START WITH LOTA_COD_LOTACAO = $codlotacao
                                                    )
                                            )
                                    AND TPCX.TPCX_ID_TIPO_CAIXA = $tipocaixa
                                    AND CXEN.CXEN_DT_EXCLUSAO IS NULL");
            break;
            default:
            break;
        }
        return $stmt->fetch();
    }
    
    public function getCaixasGrupoServico($order)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                    CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                    TPCX.TPCX_DS_CAIXA_ENTRADA, 
                                    RHLOTA.LOTA_SIGLA_SECAO, 
                                    RHLOTA.LOTA_COD_LOTACAO, 
                                    RHLOTA.LOTA_DSC_LOTACAO, 
                                    RHLOTA.LOTA_SIGLA_LOTACAO,
                                    SGRS.SGRS_ID_GRUPO,
                                    SGRS.SGRS_DS_GRUPO    
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                     SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                 SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                     SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                     RH_CENTRAL_LOTACAO RHLOTA
                             WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                             AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                             AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                             AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                             AND   SGRS.SGRS_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                             ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getCaixasGrupoServicoPorTrfSecaoSubsecao($sgsecao, $codlotacao, $tipolotacao, $tipocaixa, $order)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         /**
         * TIPO 9 TRF1
         * TIPO 1 SEÇÃO
         * TIPO 2 SUBSEÇÃO
         */
        switch ($tipolotacao) {
            case 9:/* TIPO 9 TRF1*/
                 $stmt = $db->query("SELECT  CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA, 
                                            RHLOTA.LOTA_SIGLA_SECAO, 
                                            RHLOTA.LOTA_COD_LOTACAO, 
                                            RHLOTA.LOTA_DSC_LOTACAO, 
                                            RHLOTA.LOTA_SIGLA_LOTACAO,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO    
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                             SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                             SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                             SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                             RH_CENTRAL_LOTACAO RHLOTA
                                     WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                     AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                     AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                     AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                     AND   SGRS.SGRS_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                     AND   (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                                                                (	
                                                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                                                          FROM    
                                                                                          (
                                                                                            SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                                                            FROM (                           
                                                                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                                                    FROM RH_CENTRAL_LOTACAO
                                                                                                    WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                                                                    AND  LOTA_DAT_FIM IS NULL
                                                                                                )
                                                                                            CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                                                            --AND LOTA_TIPO_LOTACAO NOT IN (2) /*SUBSEÇÃO*/
                                                                                            START WITH LOTA_COD_LOTACAO = $codlotacao
                                                                                        )
                                                                                )
                                   AND TPCX.TPCX_ID_TIPO_CAIXA = $tipocaixa
                                   ORDER BY $order");
            break;
            case 1: /* TIPO 1 SEÇÃO*/
                 $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA, TPCX.TPCX_ID_TIPO_CAIXA, TPCX.TPCX_DS_CAIXA_ENTRADA
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                         SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                         SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                         SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                         RH_CENTRAL_LOTACAO RHLOTA
                                     WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                     AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                     AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                     AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                     AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                     AND (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                            (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                                AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        START WITH LOTA_COD_LOTACAO = $codlotacao
                                                    )
                                            )
                                    AND TPCX.TPCX_ID_TIPO_CAIXA = $tipocaixa
                                    AND CXEN.CXEN_DT_EXCLUSAO IS NULL");
            break;
            case 2: /* TIPO 2 SUBSEÇÃO*/
                 $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA, TPCX.TPCX_ID_TIPO_CAIXA, TPCX.TPCX_DS_CAIXA_ENTRADA
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                         SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                         SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                         SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                         RH_CENTRAL_LOTACAO RHLOTA
                                     WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                     AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                     AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                     AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                     AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                     AND (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                            (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                                AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        AND LOTA_TIPO_LOTACAO NOT IN (2) /*SUBSEÇÃO*/
                                                        START WITH LOTA_COD_LOTACAO = $codlotacao
                                                    )
                                            )
                                    AND TPCX.TPCX_ID_TIPO_CAIXA = $tipocaixa
                                    AND CXEN.CXEN_DT_EXCLUSAO IS NULL");
            break;
            default:
            break;
        }
        return $stmt->fetchAll();
    }
    
    public function getCaixaAtendimentoUsuGrupoServicoPorLotacao($sgsecao, $codlotacao)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        try {
        /**
         * Obtendo a lotacao pai tribunal ou seção.
         */
        $stmt = $db->query(" SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO A
                                        WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                      --AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_LOTA_COD_LOTACAO_PAI = LOTA_COD_LOTACAO
                                START WITH LOTA_COD_LOTACAO = $codlotacao
                              )
                              WHERE LOTA_TIPO_LOTACAO IN(9,1)/*LOTAÇÃO PAI SEÇÃO OU TRIBUNAL*/");
        $lotaPai =  $stmt->fetch();
        //Zend_Debug::dump($lotaPai);
        /**
         * Tratamento para não trazer as lotações subseções filhas das seções caso seja uma seção a lotação pai  
         */
        $stmt = $db->query(" SELECT CASE WHEN RHLOTA.LOTA_TIPO_LOTACAO = 1 /*SECAO*/ THEN 2 /*SUBSECAO*/
                                           ELSE -1
                                           END AS LOTA_TIPO_LOTACAO
                                    FROM RH_CENTRAL_LOTACAO RHLOTA
                                    WHERE RHLOTA.LOTA_SIGLA_SECAO = '$lotaPai[LOTA_SIGLA_SECAO]'
                                    AND   RHLOTA.LOTA_COD_LOTACAO = $lotaPai[LOTA_COD_LOTACAO] 
                                    --AND  RHLOTA.LOTA_DAT_FIM IS NULL ");
        $nao_do_tipo =  $stmt->fetch();
        //Zend_Debug::dump($nao_do_tipo);
        /**
         * Obtendo o grupo de serviço que atende à Seção ou ao tribunal e o id da caixa junto com a lotação responsável pelo grupo de serviço
         */
        $stmt = $db->query(" SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                                    INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                                    ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                    INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                                    ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                    INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                    INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                    AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                    WHERE TPCX.TPCX_ID_TIPO_CAIXA = 1 /*ATENDIMENTO AOS USUÁRIOS*/
                                    AND   (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                    (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$lotaPai[LOTA_SIGLA_SECAO]'
                                                                --AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        /*TRATAMENTO PARA O CASO DE SER UMA SUBSECAO*/
                                                        AND LOTA_TIPO_LOTACAO NOT IN ($nao_do_tipo[LOTA_TIPO_LOTACAO])
                                                        START WITH LOTA_COD_LOTACAO = $lotaPai[LOTA_COD_LOTACAO]
                                                    )
                                            )");
        $grupoServico = $stmt->fetchAll();
        $db->rollBack();
        
        return  $grupoServico;
        
        } catch (Exception $exc) {
            $db->rollBack();
        }
    }
    public function getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao($sgsecao, $codlotacao)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        try {
        /**
         * Obtendo a lotacao pai tribunal ou seção.
         */
        $stmt = $db->query(" SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO A
                                        WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                      --AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_LOTA_COD_LOTACAO_PAI = LOTA_COD_LOTACAO
                                START WITH LOTA_COD_LOTACAO = $codlotacao
                              )
                              WHERE LOTA_TIPO_LOTACAO IN(9,1)/*LOTAÇÃO PAI SEÇÃO OU TRIBUNAL*/");
        $lotaPai =  $stmt->fetch();
        //Zend_Debug::dump($lotaPai);
        /**
         * Tratamento para não trazer as lotações subseções filhas das seções caso seja uma seção a lotação pai  
         */
        $stmt = $db->query(" SELECT CASE WHEN RHLOTA.LOTA_TIPO_LOTACAO = 1 /*SECAO*/ THEN 2 /*SUBSECAO*/
                                           ELSE -1
                                           END AS LOTA_TIPO_LOTACAO
                                    FROM RH_CENTRAL_LOTACAO RHLOTA
                                    WHERE RHLOTA.LOTA_SIGLA_SECAO = '$lotaPai[LOTA_SIGLA_SECAO]'
                                    AND   RHLOTA.LOTA_COD_LOTACAO = $lotaPai[LOTA_COD_LOTACAO] 
                                    --AND  RHLOTA.LOTA_DAT_FIM IS NULL ");
        $nao_do_tipo =  $stmt->fetch();
        //Zend_Debug::dump($nao_do_tipo);
        /**
         * Obtendo o grupo de serviço que atende à Seção ou ao tribunal e o id da caixa junto com a lotação responsável pelo grupo de serviço
         */
        $stmt = $db->query(" SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                                    INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                                    ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                    INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                                    ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                    INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                    INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                    AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                    WHERE TPCX.TPCX_ID_TIPO_CAIXA = 7 /*GESTÃO DO ATENDIMENTO AOS USUÁRIOS*/
                                    AND   (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                    (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$lotaPai[LOTA_SIGLA_SECAO]'
                                                                --AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        /*TRATAMENTO PARA O CASO DE SER UMA SUBSECAO*/
                                                        AND LOTA_TIPO_LOTACAO NOT IN ($nao_do_tipo[LOTA_TIPO_LOTACAO])
                                                        START WITH LOTA_COD_LOTACAO = $lotaPai[LOTA_COD_LOTACAO]
                                                    )
                                            )");
        $grupoServico = $stmt->fetchAll();
        $db->rollBack();
        
        return  $grupoServico;
        
        } catch (Exception $exc) {
            $db->rollBack();
        }
    }
    
    public function getCaixasGrupoServicoPorLotacao($sgsecao, $codlotacao, $retiraCaixa1 = null, $retiraCaixa2 = null)
    {
//        exit('chegou');
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        try {
        /**
         * Obtendo a lotacao pai tribunal ou seção.
         */
        $stmt = $db->query(" SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO A
                                        WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_LOTA_COD_LOTACAO_PAI = LOTA_COD_LOTACAO
                                START WITH LOTA_COD_LOTACAO = $codlotacao
                              )
                              WHERE LOTA_TIPO_LOTACAO IN(9,1)/*LOTAÇÃO PAI SEÇÃO OU TRIBUNAL*/");
        $lotaPai =  $stmt->fetch();
        //Zend_Debug::dump($lotaPai);
        /**
         * Tratamento para não trazer as lotações subseções filhas das seções caso seja uma seção a lotação pai 
         */
        $stmt = $db->query(" SELECT CASE WHEN RHLOTA.LOTA_TIPO_LOTACAO = 1 /*SECAO*/ THEN 2 /*SUBSECAO*/
                                           ELSE -1
                                           END AS LOTA_TIPO_LOTACAO
                                    FROM RH_CENTRAL_LOTACAO RHLOTA
                                    WHERE RHLOTA.LOTA_SIGLA_SECAO = '$lotaPai[LOTA_SIGLA_SECAO]'
                                    AND   RHLOTA.LOTA_COD_LOTACAO = $lotaPai[LOTA_COD_LOTACAO] 
                                    AND  RHLOTA.LOTA_DAT_FIM IS NULL ");
        $nao_do_tipo =  $stmt->fetch();
        //Zend_Debug::dump($nao_do_tipo);
        /**
         * Obtendo o grupo de serviço que atende à Seção ou ao tribunal e o id da caixa junto com a lotação responsável pelo grupo de serviço
         */
        //$stmt = $db->query(" SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
        $query = "                 SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                                    INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                                    ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                    INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                                    ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                    INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                    INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                    AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                    WHERE (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                    (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '$lotaPai[LOTA_SIGLA_SECAO]'
                                                                --AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        /*TRATAMENTO PARA O CASO DE SER UMA SUBSECAO*/
                                                        AND LOTA_TIPO_LOTACAO NOT IN ($nao_do_tipo[LOTA_TIPO_LOTACAO])
                                                        START WITH LOTA_COD_LOTACAO = $lotaPai[LOTA_COD_LOTACAO]
                                                    )
                                            )";
   //     Zend_Debug::dump($retiraCaixa1);
        ($retiraCaixa1)? $query .= " AND SGRS.SGRS_ID_GRUPO NOT IN ($retiraCaixa1)" : "";
        ($retiraCaixa2)? $query .= " AND SGRS.SGRS_ID_GRUPO NOT IN ($retiraCaixa2)" : "";
        
        $query .= " ORDER BY TPCX_DS_CAIXA_ENTRADA ASC";
        
        $stmt = $db->query($query);
       // Zend_Debug::dump($query);exit;
        $grupoServico = $stmt->fetchAll();
        
        $db->rollBack();
        
        return  $grupoServico;
        
        } catch (Exception $exc) {
            $db->rollBack();
        }
    }
    
    public function getTodasCaixas()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                            ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            ORDER BY 1");
        return $stmt->fetchAll();
    }
    
    public function getCaixasSecoes()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                            ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND CXEN_ID_CAIXA_ENTRADA IN(5,6,18,7,8,9,10,11,12,13,14,15,16,17)
                            ORDER BY 1");
        return $stmt->fetchAll();
    }
    
    public function getTodosNiveis($grupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT * FROM SOS_TB_SNAT_NIVEL_ATENDIMENTO
                            WHERE SNAT_ID_GRUPO = $grupo
                            ORDER BY 3");
        return $stmt->fetchAll();
    }
    
    public function getIndicadorConformidade($grupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT * FROM SOS_TB_SINS_INDIC_NIVEL_SERV
                            WHERE UPPER(SINS_DS_INDICADOR) LIKE UPPER('Índice de chamados com Não Conformidade%')
                            AND  SINS_ID_GRUPO = $grupo
                            ORDER BY 3";
        
        return $db->query($stmt)->fetchAll();
    }
    
    public function getGrupoAtendimento($idGrupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            TPCX.TPCX_DS_PROPRIETARIO_CAIXA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                            ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND SGRS_ID_GRUPO = $idGrupo
                            ORDER BY 1");
        return $stmt->fetchAll();
    }
    
    public function getGrupoAtendimentoNivel($idNivel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO,
                                            TPCX.TPCX_DS_PROPRIETARIO_CAIXA
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                            ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                            INNER JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                            ON SNAT.SNAT_ID_GRUPO= SGRS.SGRS_ID_GRUPO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND SNAT.SNAT_ID_NIVEL  = $idNivel
                            ORDER BY 1");
        return $stmt->fetchAll();
    }
    
    public function getGrupoAtendimentoByCaixa($idCaixaEntrada)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                            CXEN.CXEN_DS_CAIXA_ENTRADA, 
                                            TPCX.TPCX_ID_TIPO_CAIXA, 
                                            TPCX.TPCX_DS_CAIXA_ENTRADA,
                                            SGRS.SGRS_ID_GRUPO,
                                            SGRS.SGRS_DS_GRUPO,
                                            SGRS.SGRS_SG_SECAO_LOTACAO,
                                            SGRS.SGRS_CD_LOTACAO,
                                            TPCX.TPCX_DS_PROPRIETARIO_CAIXA
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                            ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND CXEN_ID_CAIXA_ENTRADA = $idCaixaEntrada
                            ORDER BY 1");
        return $stmt->fetchAll();
    }
    
    public function getIndicadorByCaixa($idCaixaEntrada)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SINS_ID_INDICADOR,
                                   SINS_DS_INDICADOR 
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN SOS_TB_SINS_INDIC_NIVEL_SERV SINS
                            ON SINS.SINS_ID_GRUPO = SGRS.SGRS_ID_GRUPO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND CXEN_ID_CAIXA_ENTRADA = $idCaixaEntrada
                            AND UPPER(TRIM(SINS_DS_INDICADOR)) LIKE UPPER(TRIM('Índice de Chamados com Não Conformidade'))
                            ORDER BY 1");
        return $stmt->fetch();
    }
    
    public function getNivel($grupo, $nivel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($grupo, $nivel);
        $stmt = $db->query("SELECT * FROM SOS_TB_SNAT_NIVEL_ATENDIMENTO
                            WHERE SNAT_ID_GRUPO = $grupo 
                            AND SNAT_CD_NIVEL = $nivel
                            ORDER BY 3");
        return $stmt->fetchAll();
    }
    
    public function getSecaoLotacaoSigla($sigla)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                        CXEN.CXEN_DS_CAIXA_ENTRADA, 
                        TPCX.TPCX_ID_TIPO_CAIXA, 
                        TPCX.TPCX_DS_CAIXA_ENTRADA,
                        SGRS.SGRS_ID_GRUPO,
                        SGRS.SGRS_DS_GRUPO,
                        SGRS.SGRS_SG_SECAO_LOTACAO,
                        SGRS.SGRS_CD_LOTACAO
                 FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                 INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                 ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                 INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                 ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                 INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                 ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                 INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                 ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                 AND   SGRS.SGRS_CD_LOTACAO    = RHLOTA.LOTA_COD_LOTACAO
                 WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                 AND SGRS.SGRS_SG_SECAO_LOTACAO = '".$sigla."'
                 ORDER BY 1";
        $stmt = $db->query($q);
        return $stmt->fetchAll();
    }
}