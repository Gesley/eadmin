<?php

class Application_Model_DbTable_RhCentralLotacao extends Zend_Db_Table_Abstract
{

    protected $_schema = 'SARH';
    protected $_name = 'RH_CENTRAL_LOTACAO';
    protected $_primary = array('LOTA_SIGLA_SECAO', 'LOTA_COD_LOTACAO');

    public function getLotacao()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_LOTACAO,LOTA_DSC_LOTACAO,LOTA_COD_LOTACAO,LOTA_SIGLA_SECAO,LOTA_TIPO_LOTACAO
                              FROM RH_CENTRAL_LOTACAO
                              --WHERE LOTA_DAT_FIM IS NULL
                              ORDER BY 1 DESC");
        return $stmt->fetchAll();
    }

    public function getLotacaoAjax($lotacao, $sigla = null, $secao = null, $subSecao = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt  = "SELECT\n";
        $stmt .= "\tLOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),' - ',' ') ||' - '||LOTA_COD_LOTACAO||' - '||LOTA_SIGLA_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LABEL,\n";
        $stmt .= "\tLOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),' - ',' ') ||' - '||LOTA_COD_LOTACAO||' - '||LOTA_SIGLA_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS VALUE,\n";
        $stmt .= "\tLOTA_COD_LOTACAO COD_LOTA,\n";
        $stmt .= "\tLOTA_SIGLA_SECAO SIGLA_SECAO\n";
        $stmt .= "FROM\n";
        $stmt .= "(\n\tSELECT *\n";
        $stmt .= "\tFROM RH_CENTRAL_LOTACAO\n";
        if (!empty($sigla))
            $stmt .= "\tWHERE lota_sigla_secao = '$sigla' \n";
        $stmt .= ")\n";
        $stmt .= "WHERE UPPER(LOTA_SIGLA_LOTACAO||' - '||LOTA_COD_LOTACAO||' - '||LOTA_DSC_LOTACAO) LIKE UPPER('%$lotacao%')\n";
        $stmt .= "AND LOTA_DAT_FIM IS NULL \n";
        if (!empty($subSecao))
            $stmt .= "AND LOTA_COD_LOTACAO <> $secao \n";
        if (!empty($subSecao) || !empty($secao)) {
            $stmt .= "CONNECT BY PRIOR lota_cod_lotacao = lota_lota_cod_lotacao_pai\n";
            $stmt .= "AND lota_tipo_lotacao > 2 \n";
            $stmt .= "START WITH lota_cod_lotacao = " . (($subSecao) ?: $secao)."\n";
        }
        $stmt .= "ORDER BY 1 DESC";
//        print_r($stmt);die;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    /*
     * Retorna todas as lotacoes que sao filhas de uma determinada secao judiciaria
     * Foi necessario criar essa funcao pois a RH_CENTRAL_LOTACAO possui subsecoes judiciarias cadastradas com todas as siglas
     * @param $descLotacao - termo pesquisado
     * @param $siglaSecao - sigla da secao judiciaria
     * @param $codSecao - codigo da secao judiciaria
     */

    public function getLotacoesDaSecaoAjax($descLotacao, $siglaSecao, $codSecao)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),'-',' ') ||' - '||LOTA_COD_LOTACAO||' - '||LOTA_SIGLA_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LABEL,
                         LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),'-',' ') ||' - '||LOTA_COD_LOTACAO||' - '||LOTA_SIGLA_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS VALUE,
                         LOTA_COD_LOTACAO COD_LOTA,
                         LOTA_SIGLA_SECAO SIGLA_SECAO
                     FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE   LOTA_SIGLA_SECAO   = '$siglaSecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                START WITH LOTA_COD_LOTACAO = $codSecao
                            )
                            WHERE UPPER(LOTA_SIGLA_LOTACAO||' - '||LOTA_COD_LOTACAO||' - '||LOTA_DSC_LOTACAO) LIKE UPPER('%$descLotacao%')
                            ORDER BY 1 DESC";

        //Zend_debug::dump($query); exit;
        $stmt = $db->query($query);

        //echo 'aqui'; exit;

        return $stmt->fetchAll();
    }

    public function getLotacaoBySecaoLotacao($secao, $lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if (isset($secao)) {
            $stmt = $db->query("SELECT LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO, LOTA_COD_LOTACAO, LOTA_SIGLA_SECAO
                      FROM RH_CENTRAL_LOTACAO
                      WHERE LOTA_SIGLA_LOTACAO||LOTA_COD_LOTACAO||LOTA_DSC_LOTACAO LIKE UPPER('%$lotacao%')
                      AND   LOTA_SIGLA_SECAO = '$secao'
                      AND LOTA_DAT_FIM IS NULL
                      ORDER BY 1 DESC");
        } else {
            $stmt = $db->query("SELECT LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO, LOTA_COD_LOTACAO, LOTA_SIGLA_SECAO
                      FROM RH_CENTRAL_LOTACAO
                      WHERE LOTA_SIGLA_LOTACAO||LOTA_COD_LOTACAO||LOTA_DSC_LOTACAO LIKE UPPER('%$lotacao%')
                      AND LOTA_DAT_FIM IS NULL
                      ORDER BY 1 DESC");
        }
        return $stmt->fetch();
    }

    public function getLotacaoByMatricula($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO, LOTA_COD_LOTACAO 
                                FROM OCS_TB_APSU_PAPEL_SIST_UNIDADE A ,OCS_TB_APSP_PAP_SIST_UNID_PESS B, RH_CENTRAL_LOTACAO C , OCS_TB_PMAT_MATRICULA D
                                WHERE A.APSU_ID_PAPEL_SISTEMA_UNIDADE = B.APSP_ID_PAPEL_SIST_UNID
                                AND C.LOTA_COD_LOTACAO = A.APSU_CD_UNIDADE
                                AND  B.APSP_ID_PESSOA = D.PMAT_ID_PESSOA
                                AND D.PMAT_CD_MATRICULA = '$matricula'");
        return $stmt->fetchAll();
    }

    public function getSelectSecao($siglaSecao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_UF,SESB_SIGLA_SECAO_SUBSECAO, 
                                   LOTA_DSC_LOTACAO,
                                   LOTA_COD_LOTACAO,
                                   LOTA_TIPO_LOTACAO
                            FROM   RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                            WHERE  SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                            AND    LOTA_SIGLA_SECAO = UPPER('BA')
                            AND    LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                            AND    ((LOTA_TIPO_LOTACAO in (1,9)
                            AND    LOTA_LOTA_COD_LOTACAO_PAI = 1)
                            OR     (LOTA_TIPO_LOTACAO = 2
                            AND    LOTA_LOTA_COD_LOTACAO_PAI IN (SELECT LOTA_COD_LOTACAO
                                                                 FROM   RH_CENTRAL_LOTACAO
                                                                 WHERE  LOTA_SIGLA_SECAO = UPPER('BA')
                                                                 AND    LOTA_TIPO_LOTACAO in (1,9)
                                                                 AND    LOTA_LOTA_COD_LOTACAO_PAI = 1)))
                            ORDER BY SESB_UF,LOTA_TIPO_LOTACAO,LOTA_DSC_LOTACAO");
        return $stmt->fetchAll();
    }

    public function getSecoestrf1()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_SIGLA_SECAO_SUBSECAO, LOTA_COD_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, LOTA_TIPO_LOTACAO
                              FROM RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                             AND   LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                             AND   LOTA_TIPO_LOTACAO = 1
                             AND   LOTA_SIGLA_SECAO = 'TR'
                             AND   LOTA_LOTA_COD_LOTACAO_PAI = 1
                             UNION 
                            SELECT SESB_SIGLA_SECAO_SUBSECAO, LOTA_COD_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, LOTA_TIPO_LOTACAO
                              FROM RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                             AND   LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                             AND   LOTA_TIPO_LOTACAO = 9
                             AND   LOTA_SIGLA_SECAO =  'TR'
                             AND   LOTA_LOTA_COD_LOTACAO_PAI = 1
                             ORDER BY   LOTA_COD_LOTACAO");
        return $stmt->fetchAll();
    }

    public function getAllSecoes()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_SIGLA_SECAO_SUBSECAO, LOTA_COD_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, LOTA_TIPO_LOTACAO
                              FROM RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                             AND   LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                             AND   LOTA_TIPO_LOTACAO = 1
                             AND   LOTA_SIGLA_SECAO = 'TR'
                             AND   LOTA_LOTA_COD_LOTACAO_PAI = 1
                             ");
        return $stmt->fetchAll();
    }

    public function getSubSecoes($siglasecao, $codlotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_COD_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, LOTA_SIGLA_SECAO, LOTA_TIPO_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI
                              FROM RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                             AND   LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                             AND   LOTA_TIPO_LOTACAO = 9 /*TRIBUNAL*/
                             AND   LOTA_SIGLA_SECAO = '$siglasecao'
                             AND   LOTA_COD_LOTACAO = $codlotacao
                             UNION
                             SELECT LOTA_COD_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, LOTA_SIGLA_SECAO, LOTA_TIPO_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI
                              FROM RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                             AND   LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                             AND   LOTA_TIPO_LOTACAO = 1 /*SEÇAO*/
                             AND   LOTA_SIGLA_SECAO = '$siglasecao'
                             AND   LOTA_COD_LOTACAO = $codlotacao
                             UNION
                             SELECT LOTA_COD_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, LOTA_SIGLA_SECAO, LOTA_TIPO_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI
                              FROM RH_CENTRAL_LOTACAO,
                                   RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                             AND   LOTA_SIGLA_SECAO = SESB_SIGLA_CENTRAL
                             AND   LOTA_TIPO_LOTACAO = 2 /*SUBSECÕES*/
                             AND   LOTA_SIGLA_SECAO = '$siglasecao'
                             AND   LOTA_LOTA_COD_LOTACAO_PAI = $codlotacao");

        return $stmt->fetchAll();
    }

    public function getLotacaobySecao($siglasecao, $codLotacao, $tipolotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        /**
         * TIPO 9 TRF1
         * TIPO 1 SEÇÃO
         * TIPO 2 SUBSEÇÃO  AND LOTA_TIPO_LOTACAO NOT IN (2)
         */
        switch ($tipolotacao) {
            case 9:/* TIPO 9 TRF1 */
                $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_LOTACAO 
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE   LOTA_SIGLA_SECAO   = '$siglasecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                START WITH LOTA_COD_LOTACAO = $codLotacao
                            )");
                break;
            case 1: /* TIPO 1 SEÇÃO */
                $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_LOTACAO 
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE   LOTA_SIGLA_SECAO   = '$siglasecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI
                                AND LOTA_TIPO_LOTACAO NOT IN (2)
                                START WITH LOTA_COD_LOTACAO = $codLotacao
                            )");
                break;
            case 2: /* TIPO 2 SUBSEÇÃO */
                $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_LOTACAO 
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE   LOTA_SIGLA_SECAO   = '$siglasecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                START WITH LOTA_COD_LOTACAO = $codLotacao
                            )");
                break;
            default:
                break;
        }

        return $stmt->fetchAll();
    }

    public function getAllLotacaobySecao()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO, RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_LOTACAO 
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE   LOTA_DAT_FIM IS NULL
                                    )
                               
                            )");


        return $stmt->fetchAll();
    }

    public function getDadosPessoais($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT T.PFON_CD_DDD||'-'||T.PFON_NR_TELEFONE TELEFONE, E.PEEM_ED_EMAIL,
                     C.LOTA_ANDAR||' - '||C.LOTA_SIGLA_LOTACAO LOCALIZACAO, P.PNAT_NO_PESSOA, 
                     C.LOTA_COD_LOTACAO, REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(C.LOTA_SIGLA_SECAO,C.LOTA_COD_LOTACAO),'-',' ') LOTA_DSC_LOTACAO, C.LOTA_SIGLA_LOTACAO,
                     C.LOTA_SIGLA_SECAO, C.LOTA_DAT_FIM
                            FROM   OCS_TB_PMAT_MATRICULA M
                            LEFT JOIN OCS_TB_PFON_TELEFONE_PESSOA T
                            ON T.PFON_ID_PESSOA = M.PMAT_ID_PESSOA
                            LEFT JOIN OCS_TB_PEEM_EMAIL_PESSOA E
                            ON E.PEEM_ID_PESSOA = M.PMAT_ID_PESSOA
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO AND 
                               C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO
                            INNER JOIN CO_USER_ID ID
                            ON M.PMAT_CD_MATRICULA = ID.COU_COD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE M.PMAT_CD_MATRICULA = '$matricula'
                            AND (M.PMAT_DT_FIM IS NULL OR M.PMAT_DT_FIM > SYSDATE)";
//        Zend_Debug::dump($q);exit;
        $stmt = $db->query($q);
        return $stmt->fetchAll();
    }

    public function getSecSubsecPai($sgsecao, $codlotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, 
                                   LOTA_SIGLA_LOTACAO,
                                   LOTA_COD_LOTACAO, 
                                   LOTA_DSC_LOTACAO,
                                   LOTA_TIPO_LOTACAO,
                                   LOTA_LOTA_COD_LOTACAO_PAI,
                                   SESB_SESU_CD_SECSUBSEC,
                                   SESB_SIGLA_SECAO_SUBSECAO, 
                                   SESB_MUNICIPIO_SECAO_SUBSECAO, 
                                   SESB_UF , 
                                   SESB_BAIRRO_SECAO_SUBSECAO,
                                   SESB_ENDERECO_SECAO_SUBSECAO, 
                                   SESB_CEP_SECAO_SUBSECAO
                            FROM RH_CENTRAL_LOTACAO A
                            INNER JOIN RH_CENTRAL_SECAO_SUBSECAO B
                            ON A.LOTA_SIGLA_SECAO = B.SESB_SIGLA_CENTRAL
                            AND A.LOTA_COD_LOTACAO = B.SESB_LOTA_COD_LOTACAO
                            WHERE LOTA_SIGLA_SECAO = '$sgsecao'
                            AND   LOTA_COD_LOTACAO = RH_BUSCA_CENTRAL_LOTA_SEC_SUBS('$sgsecao',$codlotacao)");
        return $stmt->fetch();
    }

    public function getAllSecSubsecPai()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, 
                                   LOTA_SIGLA_LOTACAO,
                                   LOTA_COD_LOTACAO, 
                                   LOTA_DSC_LOTACAO,
                                   LOTA_TIPO_LOTACAO,
                                   LOTA_LOTA_COD_LOTACAO_PAI,
                                   SESB_SESU_CD_SECSUBSEC,
                                   SESB_SIGLA_SECAO_SUBSECAO, 
                                   SESB_MUNICIPIO_SECAO_SUBSECAO, 
                                   SESB_UF , 
                                   SESB_BAIRRO_SECAO_SUBSECAO,
                                   SESB_ENDERECO_SECAO_SUBSECAO, 
                                   SESB_CEP_SECAO_SUBSECAO
                            FROM RH_CENTRAL_LOTACAO A
                            INNER JOIN RH_CENTRAL_SECAO_SUBSECAO B
                            ON A.LOTA_SIGLA_SECAO = B.SESB_SIGLA_CENTRAL
                            AND A.LOTA_COD_LOTACAO = B.SESB_LOTA_COD_LOTACAO
                            ");
        return $stmt->fetch();
    }

    public function getCapitalUF()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT      CAP_UF,
                                        UF_NOME 
                            FROM        CAPITAL_UF 
                            ORDER BY    UF_NOME");
        return $stmt->fetchall();
    }

    public function getDescricaoLotacao($siglaSecao, $codLotacao)
    {

        $stmt = "SELECT LOTA_SIGLA_SECAO SIGLA_SECAO, 
                       LOTA_SIGLA_LOTACAO SIGLA_LOTACAO,
                       LOTA_COD_LOTACAO COD_LOTACAO,
                       LOTA_SIGLA_LOTACAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO('$siglaSecao', $codLotacao ) DESCRICAO,
                       RH_SIGLAS_FAMILIA_CENTR_LOTA('$siglaSecao', $codLotacao) SIGLA
                FROM RH_CENTRAL_LOTACAO   
                WHERE  LOTA_SIGLA_SECAO = '$siglaSecao'
                AND LOTA_COD_LOTACAO = $codLotacao
                AND LOTA_DAT_FIM IS NULL ";

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        return $db->query($stmt)->fetchAll();
    }

    /*
     * Busca o codigo da lotacao das SEDER's nas Seccionais
     */

    public function getCodLotacaoSeder($siglaSecao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_SECAO, 
                                   LOTA_SIGLA_LOTACAO,
                                   LOTA_COD_LOTACAO
                            FROM RH_CENTRAL_LOTACAO   
                            WHERE LOTA_SIGLA_LOTACAO LIKE '%SEDER%' 
                            AND LOTA_SIGLA_SECAO = $siglaSecao
                            AND LOTA_DAT_FIM IS NULL");
        return $stmt->fetchall();
    }

    /**
     * Funcao retorna o codigo de uma determinado lotacao
     *
     * @author Daniel Rodrigues
     * @param type $siglaSecao
     * @param type $siglaUnidade
     * @return array
     */
    public function getCodLotacapelaSigla($siglaSecao, $siglaUnidade)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT  
                LOTA_COD_LOTACAO
            FROM 
                RH_CENTRAL_LOTACAO
            WHERE
                LOTA_SIGLA_SECAO = '$siglaSecao' AND
                LOTA_SIGLA_LOTACAO = '$siglaUnidade'
        ");
        return $stmt->fetch();
    }

}
