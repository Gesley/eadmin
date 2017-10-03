<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSgrsGrupoServico extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SGRS_GRUPO_SERVICO';
    protected $_primary = 'SGRS_ID_GRUPO';
    protected $_sequence = 'SOS_SQ_SGRS';

    public function getGrupoServico($order)
    {
        if ( !isset($order) ) {
            $order = 'SGRS_DS_GRUPO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT G.SGRS_ID_GRUPO, G.SGRS_DS_GRUPO, L.LOTA_DSC_LOTACAO,
                                   SGRS_IC_VISIVEL VISIBILIDADE,
                                   L.LOTA_SIGLA_SECAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO(L.LOTA_SIGLA_SECAO, RH_BUSCA_CENTRAL_LOTA_SEC_SUBS(L.LOTA_SIGLA_SECAO, L.LOTA_COD_LOTACAO)) LOTACAO
                            FROM   SOS_TB_SGRS_GRUPO_SERVICO G
                            INNER  JOIN RH_CENTRAL_LOTACAO L
                            ON     L.LOTA_SIGLA_SECAO = G.SGRS_SG_SECAO_LOTACAO
                            AND    L.LOTA_COD_LOTACAO = G.SGRS_CD_LOTACAO
                            ORDER  BY $order");
        return $stmt->fetchAll();
    }

    public function getComboGrupoServico($sigla)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT LOTA_SIGLA_SECAO||LOTA_COD_LOTACAO LOTACAO,
                                   LOTA_DSC_LOTACAO||' - '||B.SESB_UF||' - '||LOTA_SIGLA_SECAO||LOTA_COD_LOTACAO LOTA_DSC_LOTACAO
                            FROM   RH_CENTRAL_LOTACAO a, RH_SECAO_SUBSECAO B
                            WHERE  A.LOTA_SIGLA_SECAO = B.SESB_SIGLA_SECAO_SUBSECAO
                            AND    LOTA_SIGLA_SECAO = '".$sigla."'
                            AND    LOTA_DAT_FIM IS NULL
                            ORDER  BY 2");
        $grupoServico = $stmt->fetchAll();
        return $grupoServico;
    }

    public function getEspecificoGrupoServico($cod_lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SGRS_DS_GRUPO
                              FROM SOS_TB_SGRS_GRUPO_SERVICO
                             WHERE SGRS_ID_GRUPO = $cod_lotacao");
        $grupoServico = $stmt->fetchAll();
        return $grupoServico;
    }
    
    
    /**
     * CAIXA DO GRUPO DE ATENDIMENTO PELA SEÇÃO PAI
     */
    public function getCaixaGrupodeAtendimentoByCodLotacaoPaiSecao($cd_lotacao_pai_secao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT  CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,  SAD_TB_CXEN_CAIXA_ENTRADA CXEN , SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, RH_CENTRAL_LOTACAO RHLOTA
                            WHERE TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                            AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                            AND   CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND   CXLO.CXLO_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                            AND   RHLOTA.LOTA_DAT_FIM IS NULL
                            AND   TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND   TPCX.TPCX_ID_TIPO_CAIXA  IN(1,2,3,4)
                            AND   CXLO.CXLO_CD_LOTACAO_PAI = $cd_lotacao_pai_secao
                            ORDER BY 2");
        $grupoAtendimento = $stmt->fetchAll();
        return $grupoAtendimento;
    }
    /**
     * GRUPO DE SERVIÇO PELO GRUPO DE ATENDIMENTO
     */
    public function getGrupodeServicoByidCaixaDoGrupodeAtendimento($id_caixa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SGRS_ID_GRUPO,SGRS_DS_GRUPO,SGRS_SG_SECAO_LOTACAO,SGRS_CD_LOTACAO, CXEN_ID_CAIXA_ENTRADA, SGRS_IC_VISIVEL, CXEN_DS_CAIXA_ENTRADA
                            FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                 SAD_TB_TPCX_TIPO_CAIXA TPCX,  
                                 SAD_TB_CXEN_CAIXA_ENTRADA CXEN , 
                                 SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, 
                                 RH_CENTRAL_LOTACAO RHLOTA
                             WHERE TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                            AND CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                            AND CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                            AND CXLO.CXLO_CD_SECAO = RHLOTA.LOTA_COD_LOTACAO
                            AND RHLOTA.LOTA_DAT_FIM IS NULL
                            AND CXLO.CXLO_SG_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO 
                            AND CXLO.CXLO_CD_SECAO = SGRS.SGRS_CD_LOTACAO 
                            AND TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND TPCX.TPCX_ID_TIPO_CAIXA  IN(1,2,3,4)
                            AND CXEN.CXEN_ID_CAIXA_ENTRADA = $id_caixa
                            ORDER BY SGRS_DS_GRUPO");
        $grupoAtendimento = $stmt->fetchAll();
        return $grupoAtendimento;
    }
    
    
    
    
    public function getGrupoServicoBySecsubsec($codsecsubseclotacao,$userTI = true)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($userTI){
        $stmt = $db->query("SELECT SGRS_ID_GRUPO,SGRS_DS_GRUPO,SGRS_SG_SECAO_LOTACAO,SGRS_CD_LOTACAO, CXEN_ID_CAIXA_ENTRADA, SGRS_IC_VISIVEL, CXEN_DS_CAIXA_ENTRADA
                                FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                     SAD_TB_TPCX_TIPO_CAIXA TPCX,  
                                     SAD_TB_CXEN_CAIXA_ENTRADA CXEN , 
                                     SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, 
                                     RH_CENTRAL_LOTACAO RHLOTA
                                 WHERE (SGRS.SGRS_SG_SECAO_LOTACAO , SGRS.SGRS_CD_LOTACAO, CXEN.CXEN_ID_CAIXA_ENTRADA) 
                                    IN ( 
                                        SELECT RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO, CXEN.CXEN_ID_CAIXA_ENTRADA
                                        FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,  SAD_TB_CXEN_CAIXA_ENTRADA CXEN , SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, RH_CENTRAL_LOTACAO RHLOTA
                                            WHERE TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                                            AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                            AND   CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                            AND   CXLO.CXLO_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                            AND   RHLOTA.LOTA_DAT_FIM IS NULL
                                            AND   TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                                            AND   TPCX.TPCX_ID_TIPO_CAIXA  IN(1,2,3,4)
                                            AND   CXLO.CXLO_CD_LOTACAO_PAI = $codsecsubseclotacao
                                       )
                                AND TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                                AND CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                AND CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                AND CXLO.CXLO_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                AND RHLOTA.LOTA_DAT_FIM IS NULL
                                AND CXLO.CXLO_SG_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO 
                                AND CXLO.CXLO_CD_LOTACAO = SGRS.SGRS_CD_LOTACAO 
                                AND TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                                AND TPCX.TPCX_ID_TIPO_CAIXA  IN(1,2,3,4)
                                AND CXLO.CXLO_CD_LOTACAO_PAI = $codsecsubseclotacao ");
        $grupoServico = $stmt->fetchAll();
        }else{
            $stmt = $db->query("SELECT SGRS_ID_GRUPO,SGRS_DS_GRUPO,SGRS_SG_SECAO_LOTACAO,SGRS_CD_LOTACAO, CXEN_ID_CAIXA_ENTRADA, SGRS_IC_VISIVEL, CXEN_DS_CAIXA_ENTRADA
                                FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                     SAD_TB_TPCX_TIPO_CAIXA TPCX,  
                                     SAD_TB_CXEN_CAIXA_ENTRADA CXEN , 
                                     SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, 
                                     RH_CENTRAL_LOTACAO RHLOTA
                                 WHERE (SGRS.SGRS_SG_SECAO_LOTACAO , SGRS.SGRS_CD_LOTACAO, CXEN.CXEN_ID_CAIXA_ENTRADA) 
                                    IN ( 
                                        SELECT RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO, CXEN.CXEN_ID_CAIXA_ENTRADA
                                        FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,  SAD_TB_CXEN_CAIXA_ENTRADA CXEN , SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, RH_CENTRAL_LOTACAO RHLOTA
                                            WHERE TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                                            AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                            AND   CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                            AND   CXLO.CXLO_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                            AND   RHLOTA.LOTA_DAT_FIM IS NULL
                                            AND   TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                                            AND   TPCX.TPCX_ID_TIPO_CAIXA  IN(1,2,3,4)
                                            AND   CXLO.CXLO_CD_LOTACAO_PAI = $codsecsubseclotacao
                                       )
                                AND TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                                AND CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                AND CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                AND CXLO.CXLO_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                AND RHLOTA.LOTA_DAT_FIM IS NULL
                                AND CXLO.CXLO_SG_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO 
                                AND CXLO.CXLO_CD_LOTACAO = SGRS.SGRS_CD_LOTACAO 
                                AND TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                                AND TPCX.TPCX_ID_TIPO_CAIXA  IN(1,2,3,4)
                                AND CXLO.CXLO_CD_LOTACAO_PAI = $codsecsubseclotacao
                                AND SGRS_IC_VISIVEL = 'S'");
            $grupoServico = $stmt->fetchAll();
        }
        return $grupoServico;
    }
    
    public function getGrupoServicoPorUnidadePai($sgsecao,$codlotacao,$tipolotacao)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        /**
         * TIPO 9 TRF1
         * TIPO 1 SEÇÃO
         * TIPO 2 SUBSEÇÃO
         */
        switch ($tipolotacao) {
            case 9:/* TIPO 9 TRF1*/
                 $stmt = $db->query("SELECT SGRS_ID_GRUPO,SGRS_DS_GRUPO,  RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_DSC_LOTACAO, RHLOTA.LOTA_COD_LOTACAO, RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_SIGLA_SECAO
                                FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                     RH_CENTRAL_LOTACAO RHLOTA
                                WHERE RHLOTA.LOTA_SIGLA_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO 
                                AND RHLOTA.LOTA_COD_LOTACAO = SGRS.SGRS_CD_LOTACAO
                                AND (SGRS_SG_SECAO_LOTACAO,SGRS_CD_LOTACAO) IN
                                        (	
                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                  FROM    
                                                  (
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                    FROM (                           
                                                            SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI
                                                            FROM RH_CENTRAL_LOTACAO
                                                            WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                            AND  LOTA_DAT_FIM IS NULL
                                                        )
                                                    CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                    START WITH LOTA_COD_LOTACAO = $codlotacao
                                                )
                                        )
                                ORDER BY SGRS_DS_GRUPO");
            break;
            case 1: /* TIPO 1 SEÇÃO*/
             $stmt = $db->query("SELECT SGRS_ID_GRUPO,SGRS_DS_GRUPO,  RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_DSC_LOTACAO, RHLOTA.LOTA_COD_LOTACAO, RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_SIGLA_SECAO
                                    FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                         RH_CENTRAL_LOTACAO RHLOTA
                                    WHERE RHLOTA.LOTA_SIGLA_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO 
                                    AND RHLOTA.LOTA_COD_LOTACAO = SGRS.SGRS_CD_LOTACAO
                                    AND (SGRS_SG_SECAO_LOTACAO,SGRS_CD_LOTACAO) IN
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
                            ORDER BY SGRS_DS_GRUPO");
            break;
            case 2: /* TIPO 2 SUBSEÇÃO*/
             $stmt = $db->query("SELECT SGRS_ID_GRUPO,SGRS_DS_GRUPO,  RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_DSC_LOTACAO, RHLOTA.LOTA_COD_LOTACAO, RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_SIGLA_SECAO
                            FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                 RH_CENTRAL_LOTACAO RHLOTA
                            WHERE RHLOTA.LOTA_SIGLA_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO 
                            AND RHLOTA.LOTA_COD_LOTACAO = SGRS.SGRS_CD_LOTACAO
                            AND (SGRS_SG_SECAO_LOTACAO,SGRS_CD_LOTACAO) IN
                                    (	
                                            SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                              FROM    
                                              (
                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                FROM (                           
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI
                                                        FROM RH_CENTRAL_LOTACAO
                                                        WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                                        AND  LOTA_DAT_FIM IS NULL
                                                    )
                                                CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI
                                                START WITH LOTA_COD_LOTACAO = $codlotacao
                                            )
                                    )
                            ORDER BY SGRS_DS_GRUPO");
            break;
            default:
            break;
        }
        return $stmt->fetchAll();
    }
    
     public function getGrupoServicoPorTrfSecaoSubsecao($sgsecao,$codlotacao,$tipolotacao,$order)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        /**
         * TIPO 9 TRF1
         * TIPO 1 SEÇÃO
         * TIPO 2 SUBSEÇÃO
         */
        switch ($tipolotacao) {
            case 9:/* TIPO 9 TRF1*/
                 $stmt = $db->query("SELECT SGRS.SGRS_ID_GRUPO, SGRS.SGRS_DS_GRUPO, RHLOTA.LOTA_DSC_LOTACAO,
                                           SGRS.SGRS_IC_VISIVEL  VISIBILIDADE,
                                           RHLOTA.LOTA_SIGLA_SECAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO, RH_BUSCA_CENTRAL_LOTA_SEC_SUBS(RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO)) LOTACAO
                                    FROM   SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    INNER  JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON     RHLOTA.LOTA_SIGLA_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO
                                    AND    RHLOTA.LOTA_COD_LOTACAO = SGRS.SGRS_CD_LOTACAO
                                    AND    (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
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
                                    ORDER  BY $order");
            break;
            case 1: /* TIPO 1 SEÇÃO*/
             $stmt = $db->query("SELECT SGRS.SGRS_ID_GRUPO, SGRS.SGRS_DS_GRUPO, RHLOTA.LOTA_DSC_LOTACAO,
                                           SGRS.SGRS_IC_VISIVEL VISIBILIDADE,
                                           RHLOTA.LOTA_SIGLA_SECAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO, RH_BUSCA_CENTRAL_LOTA_SEC_SUBS(RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO)) LOTACAO
                                    FROM   SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    INNER  JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON     RHLOTA.LOTA_SIGLA_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO
                                    AND    RHLOTA.LOTA_COD_LOTACAO = SGRS.SGRS_CD_LOTACAO
                                    AND    (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
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
                                    ORDER  BY $order");
            break;
            case 2: /* TIPO 2 SUBSEÇÃO*/
             $stmt = $db->query("SELECT SGRS.SGRS_ID_GRUPO, SGRS.SGRS_DS_GRUPO, RHLOTA.LOTA_DSC_LOTACAO,
                                           SGRS.SGRS_IC_VISIVEL VISIBILIDADE,
                                           RHLOTA.LOTA_SIGLA_SECAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO, RH_BUSCA_CENTRAL_LOTA_SEC_SUBS(RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO)) LOTACAO
                                    FROM   SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    INNER  JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON     RHLOTA.LOTA_SIGLA_SECAO = SGRS.SGRS_SG_SECAO_LOTACAO
                                    AND    RHLOTA.LOTA_COD_LOTACAO = SGRS.SGRS_CD_LOTACAO
                                    AND    (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
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
                                    ORDER  BY $order");
            break;
            default:
            break;
        }
        return $stmt->fetchAll();
    }
    
    

}