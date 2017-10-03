<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_SosTbSinsIndicNivelServ extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SINS_INDIC_NIVEL_SERV';
    protected $_primary = 'SINS_ID_INDICADOR';
    protected $_sequence = 'SOS_SQ_SINS';

    public function getIndicNivelServico ($order) {
        if (!isset($order)) {
            $order = 'SINS_ID_INDICADOR DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  I.SINS_ID_INDICADOR,
                                    I.SINS_ID_GRUPO,
                                    I.SINS_CD_INDICADOR,
                                    I.SINS_DS_INDICADOR,
                                    I.SINS_SG_INDICADOR,
                                    I.SINS_DS_FORMULA_CALC,
                                    I.SINS_ID_UNID_MEDIDA,
                                    I.SINS_DS_SINAL_META,
                                    I.SINS_NR_META,
                                    U.UNME_DS_UNID_MEDIDA
                            FROM    SOS_TB_SINS_INDIC_NIVEL_SERV I
                            INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA U
                            ON U.UNME_ID_UNID_MEDIDA = I.SINS_ID_UNID_MEDIDA
                            ORDER BY $order");
        return $stmt->fetchAll();
    }

    public function getIndicNivelServicoGrupo ($grupo, $order) {
        if ($order == '') {
            $order = 'SINS_ID_INDICADOR ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  I.SINS_ID_INDICADOR,
                                    I.SINS_ID_GRUPO,
                                    I.SINS_CD_INDICADOR,
                                    I.SINS_DS_INDICADOR,
                                    I.SINS_SG_INDICADOR,
                                    I.SINS_DS_FORMULA_CALC,
                                    I.SINS_ID_UNID_MEDIDA,
                                    I.SINS_DS_SINAL_META,
                                    I.SINS_NR_META,
                                    U.UNME_DS_UNID_MEDIDA
                            FROM    SOS_TB_SINS_INDIC_NIVEL_SERV I
                            INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA U
                            ON U.UNME_ID_UNID_MEDIDA = I.SINS_ID_UNID_MEDIDA
                            WHERE I.SINS_ID_GRUPO = $grupo
                            AND I.SINS_ID_INDICADOR NOT IN (16)
                            ORDER BY $order");
        return $stmt->fetchAll();
    }

    public function getIndicadorDefeito () {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT A.SINS_ID_INDICADOR, A.SINS_ID_GRUPO, A.SINS_CD_INDICADOR,
       A.SINS_DS_INDICADOR, A.SINS_SG_INDICADOR, A.SINS_DS_FORMULA_CALC,
       A.SINS_ID_UNID_MEDIDA, A.SINS_DS_SINAL_META, A.SINS_NR_META
  	   FROM SOS_TB_SINS_INDIC_NIVEL_SERV A
       WHERE UPPER(SINS_DS_INDICADOR) LIKE UPPER('Índice de defeito (qualidade)%')
       ORDER BY 3";
        return $db->query($stmt)->fetchAll();
    }

    public function getMaiorIndicador ($grupo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MAX(SINS_CD_INDICADOR) MAIOR
                            FROM   SOS_TB_SINS_INDIC_NIVEL_SERV
                            WHERE  SINS_ID_GRUPO = $grupo");
        $result = $stmt->fetchAll();
        return $result[0]['MAIOR'];
    }

    public function getIndicadorPorCaixaeSiglaIndicador ($idCaixa, $sgIndicador) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SINS.* 
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                            INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER JOIN SOS_TB_SINS_INDIC_NIVEL_SERV SINS
                            ON SINS.SINS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            WHERE TPCX.TPCX_DS_PROPRIETARIO_CAIXA = 'SOSTI'
                            AND CXEN_ID_CAIXA_ENTRADA = $idCaixa
                            AND SINS_SG_INDICADOR = '$sgIndicador'
                            ORDER BY 1");
        return $stmt->fetch();
    }

    public function getTotalChamadosMes ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT SSOL_ID_DOCUMENTO) as TOTAL,   TO_CHAR(F.mofa_dh_fase, 'mm/yyyy') DATA, INITCAP( LOWER( TO_CHAR(F.mofa_dh_fase, 'MONTH'))) LABEL
                       FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  F.MOFA_ID_MOVIMENTACAO  = (
                                                SELECT MIN(FF.MOFA_ID_MOVIMENTACAO) 
                                                FROM   SAD_TB_DOCM_DOCUMENTO BB
                                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                                       ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                                       ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                                WHERE  EE.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                                AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO 
                                              ) 
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

        $q.="  GROUP BY TO_CHAR(F.mofa_dh_fase, 'mm/yyyy'), TO_CHAR(F.mofa_dh_fase, 'MONTH') ORDER BY TO_DATE(DATA, 'mm/yyyy') ASC";


        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadosDias ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT SSOL_ID_DOCUMENTO) as TOTAL,  SUBSTR(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'),0,2) LABEL
                       FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  F.MOFA_ID_MOVIMENTACAO  = (
                                                SELECT MIN(FF.MOFA_ID_MOVIMENTACAO) 
                                                FROM   SAD_TB_DOCM_DOCUMENTO BB
                                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                                       ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                                       ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                                WHERE  EE.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                                AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO 
                                              ) 
             AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

        $q.=" GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy') ORDER BY TO_DATE(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'),'dd/mm/yyyy') ASC";


        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadosSemana ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT SSOL_ID_DOCUMENTO) as TOTAL,   TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'), INITCAP( LOWER( TO_CHAR(F.mofa_dh_fase, 'DAY'))) LABEL
                       FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  F.MOFA_ID_MOVIMENTACAO  = (
                                                SELECT MIN(FF.MOFA_ID_MOVIMENTACAO) 
                                                FROM   SAD_TB_DOCM_DOCUMENTO BB
                                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                                       ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                                       ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                                WHERE  EE.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                                AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO 
                                              ) 
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

        $q.=" GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'), TO_CHAR(F.mofa_dh_fase, 'DAY') ORDER BY TO_DATE(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'),'dd/mm/yyyy') ASC";


        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadosHoras ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT SSOL_ID_DOCUMENTO) as TOTAL,   SUBSTR(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy HH24'),12) ||' h'   LABEL
                       FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  F.MOFA_ID_MOVIMENTACAO  = (
                                                SELECT MIN(FF.MOFA_ID_MOVIMENTACAO) 
                                                FROM   SAD_TB_DOCM_DOCUMENTO BB
                                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                                       ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                                       ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                                       ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                                WHERE  EE.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                                AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO 
                                              ) 
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

//          $q.=" GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'), TO_CHAR(F.mofa_dh_fase, 'DAY') ";

        $q.="  GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy HH24') ORDER BY TO_DATE(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy HH24'),'dd/mm/yyyy HH24') ASC";


        echo nl2br($q);

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadoscomRechamadosMes ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT MOFA_ID_MOVIMENTACAO) as TOTAL,   TO_CHAR(F.mofa_dh_fase, 'mm/yyyy') DATA, INITCAP( LOWER( TO_CHAR(F.mofa_dh_fase, 'MONTH'))) LABEL
              FROM SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa 
            /*TIPO DOCUMENTO SOLICITAÇÃO*/
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

        $q.="  GROUP BY TO_CHAR(F.mofa_dh_fase, 'mm/yyyy'), TO_CHAR(F.mofa_dh_fase, 'MONTH') ORDER BY TO_DATE(DATA, 'mm/yyyy') ASC";


        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadoscomRechamadosDias ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT MOFA_ID_MOVIMENTACAO) as TOTAL,  SUBSTR(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'),0,2) LABEL
              FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa 
            /*TIPO DOCUMENTO SOLICITAÇÃO*/
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

        $q.=" GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy') ORDER BY TO_DATE(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'),'dd/mm/yyyy') ASC";


        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadoscomRechamadosSemana ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT MOFA_ID_MOVIMENTACAO) as TOTAL,   TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'), INITCAP( LOWER( TO_CHAR(F.mofa_dh_fase, 'DAY'))) LABEL
                       FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa 
            /*TIPO DOCUMENTO SOLICITAÇÃO*/
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";


        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

        $q.=" GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'), TO_CHAR(F.mofa_dh_fase, 'DAY') ORDER BY TO_DATE(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'),'dd/mm/yyyy') ASC";


        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    public function getTotalChamadoscomRechamadosHoras ($idCaixa, $params) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT COUNT(DISTINCT MOFA_ID_MOVIMENTACAO) as TOTAL,   SUBSTR(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy HH24'),12) ||' h'   LABEL
                       FROM   SOS_TB_SSOL_SOLICITACAO A
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                       ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                       ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                       ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                       ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                       INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                       ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
            WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa 
            /*TIPO DOCUMENTO SOLICITAÇÃO*/
            AND B.DOCM_ID_TIPO_DOC = 160
            AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                            FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                   ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                   ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                   ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                               WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                               AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                 ";
        /* Data da Ultima fase */
//          (($params['DATA_INICIAL'] ==  "") && ($params['DATA_FINAL']  != ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_FINAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
//          (($params['DATA_INICIAL']  != "") && ($params['DATA_FINAL']  == ""))?($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI') AND TO_DATE('".$params['DATA_INICIAL']."', 'DD/MM/YYYY HH24:MI')+1-1/24/60/60 "):("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI') ") : ("");

//          $q.=" GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy'), TO_CHAR(F.mofa_dh_fase, 'DAY') ";

        $q.="  GROUP BY TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy HH24') ORDER BY TO_DATE(TO_CHAR(F.mofa_dh_fase, 'dd/mm/yyyy HH24'),'dd/mm/yyyy HH24') ASC";

        //echo $q

        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    /**
     * Indicador Índice de Início de Atendimento no Prazo (IIA) 
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getDatasSLA_IIA ($idCaixa, $data_inicial, $data_final, $fusoHorario, $idIndicador) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM (
                SELECT 
                A.SSOL_ID_DOCUMENTO,
                F.MOFA_ID_MOVIMENTACAO, 
                F.MOFA_DS_COMPLEMENTO,
                B.DOCM_NR_DOCUMENTO,
                TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                         FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                         INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                         ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                         INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                         ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                         INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                         ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                         INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                         INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                         WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                         AND    DOCM.DOCM_ID_TIPO_DOC = 160
                         AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                ),'DD/MM/YYYY HH24:MI:SS') AS DATA_PRIMEIRO_ATENDIMENTO
                ,
                (
                SELECT DSIN_ID_MOVIMENTACAO
                FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                AND DSIN_ID_INDICADOR = $idIndicador
                ) AS DESCONSIDERADO_IAA
                FROM   SOS_TB_SSOL_SOLICITACAO A
                                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                           /*TIPO DOCUMENTO SOLICITAÇÃO*/
                           AND B.DOCM_ID_TIPO_DOC = 160 --Tipo de documento = solicitação de ti
                           AND F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                            )
                            WHERE DATA_PRIMEIRO_ATENDIMENTO IS NOT NULL ";
        $q.= " AND TO_DATE(DATA_PRIMEIRO_ATENDIMENTO,  'DD/MM/YYYY HH24:MI:SS') BETWEEN 
                                            TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') 
                                            AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS') ";
        $q.="  ORDER BY SSOL_ID_DOCUMENTO, DATA_PRIMEIRO_ATENDIMENTO ASC";
//              Zend_Debug::dump($q);exit;
        return $db->query($q)->fetchAll();
    }

    /**
     * Tempo médio para atendimento às solicitações por parte da equipe de monitoria 1º nível (TMA) 
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @param type $idIndicador
     * @return type 
     */
    public function getDatasSLA_TMA ($idCaixa, $data_inicial, $data_final, $idIndicador) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM (
                SELECT 
                A.SSOL_ID_DOCUMENTO,
                F.MOFA_ID_MOVIMENTACAO, 
                F.MOFA_DS_COMPLEMENTO,
                B.DOCM_NR_DOCUMENTO,
                TO_CHAR(F.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                         FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                         INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                         ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                         INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                         ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                         INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                         ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                         INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                         INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                         WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                         AND    DOCM.DOCM_ID_TIPO_DOC = 160
                         AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                ),'DD/MM/YYYY HH24:MI:SS') AS DATA_PRIMEIRO_ATENDIMENTO
                ,
                (
                SELECT DSIN_ID_MOVIMENTACAO
                FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                AND DSIN_ID_INDICADOR = $idIndicador
                ) AS DESCONSIDERADO_TMA
                ,
                (SELECT SSES_1.SSES_ID_SERVICO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO) AS SSES_ID_SERVICO
                FROM   SOS_TB_SSOL_SOLICITACAO A
                                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                           /*TIPO DOCUMENTO SOLICITAÇÃO*/
                           AND B.DOCM_ID_TIPO_DOC = 160 --Tipo de documento = solicitação de ti
                           AND F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                            )
                            WHERE DATA_PRIMEIRO_ATENDIMENTO IS NOT NULL 
                            AND SSES_ID_SERVICO <> 6071 --VIDEOCONFERÊNCIA
                            AND SSES_ID_SERVICO NOT IN (SELECT SSER_ID_SERVICO FROM SOS.SOS_TB_SSER_SERVICO WHERE SSER_IC_VIDEOCONFERENCIA = 'S' AND SSER_ID_GRUPO = 4) --VIDEOCONFERÊNCIA
                            ";
        $q.= " AND TO_DATE(DATA_PRIMEIRO_ATENDIMENTO,  'DD/MM/YYYY HH24:MI:SS') BETWEEN 
                                            TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') 
                                            AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS') ";
        $q.="  ORDER BY SSOL_ID_DOCUMENTO, DATA_PRIMEIRO_ATENDIMENTO ASC";
//              Zend_Debug::dump($q);exit;
        return $db->query($q)->fetchAll();
    }

    public function getDatasSLA_ISC ($idCaixa, $data_inicial, $data_final, $fusoHorario, $indicadorISC, $indicadorIAP) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM ( 
                SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_ISC,
                    DESCONSIDERADO_IAP
                FROM (
                SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DO ENCAMINHAMENTO PARA OUTRO GRUPO
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                    --FOI RECUSADA NÃO CONTA O ENCAMINHAMENTO COMO FIM DO CHAMADO FICA SENDO A DATA DA BAIXA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                    AND   FF.MOFA_ID_FASE = 1019                              
                   ) AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorISC
                    ) AS DESCONSIDERADO_ISC
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorIAP
                    ) AS DESCONSIDERADO_IAP
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                     
                    UNION
                     SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_ISC,
                    DESCONSIDERADO_IAP
                     FROM 
                    (
                    SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DA PRIMEIRA BAIXA
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   NULL AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorISC
                    ) AS DESCONSIDERADO_ISC
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorIAP
                    ) AS DESCONSIDERADO_IAP
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                    )
                    ORDER BY SSOL_ID_DOCUMENTO, DATA_FIM_CHAMADO ASC  
                    ";
//       Zend_Debug::dump($q);
//       exit;
        return $db->query($q)->fetchAll();
        ;
    }

    public function getDatasSLA_TMCSA ($idCaixa, $data_inicial, $data_final, $indicadorTMCSA, $indicadorMAICPA) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM ( 
                SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_TMCSA,
                    DESCONSIDERADO_MAICPA
                FROM (
                SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DO ENCAMINHAMENTO PARA OUTRO GRUPO
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                    --FOI RECUSADA NÃO CONTA O ENCAMINHAMENTO COMO FIM DO CHAMADO FICA SENDO A DATA DA BAIXA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                    AND   FF.MOFA_ID_FASE = 1019                              
                   ) AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorTMCSA
                    ) AS DESCONSIDERADO_TMCSA
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorMAICPA
                    ) AS DESCONSIDERADO_MAICPA
                    ,
                    (SELECT SSES_1.SSES_ID_SERVICO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO) AS SSES_ID_SERVICO
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                    AND SSES_ID_SERVICO <> 6071 --VIDEOCONFERÊNCIA 
                    UNION
                     SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_TMCSA,
                    DESCONSIDERADO_MAICPA
                     FROM 
                    (
                    SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE ,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DA PRIMEIRA BAIXA
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   NULL AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorTMCSA
                    ) AS DESCONSIDERADO_TMCSA
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorMAICPA
                    ) AS DESCONSIDERADO_MAICPA
                    ,
                    (SELECT SSES_1.SSES_ID_SERVICO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO) AS SSES_ID_SERVICO
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                    AND SSES_ID_SERVICO <> 6071 --VIDEOCONFERÊNCIA 
                    )
                    ORDER BY SSOL_ID_DOCUMENTO, DATA_FIM_CHAMADO ASC  
                    ";
//       Zend_Debug::dump($q);
//       exit;
        return $db->query($q)->fetchAll();
        ;
    }

    public function getDatasSLA_NVNR ($idCaixa, $data_inicial, $data_final, $indicadorNVNR) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM ( 
                    SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE ,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DA PRIMEIRA BAIXA
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorNVNR
                    ) AS DESCONSIDERADO_NVNR
                   ,
                    (SELECT SSES_1.SSES_ID_SERVICO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO) AS SSES_ID_SERVICO
                      ,
                    TO_CHAR((SELECT SSES_1.SSES_DT_INICIO_VIDEO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO),'DD/MM/YYYY HH24:MI:SS') AS SSES_DT_INICIO_VIDEO
                    ,
                    (SELECT SSES_1.SSES_IC_VIDEO_REALIZADA 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO) AS SSES_IC_VIDEO_REALIZADA
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                    AND SSES_DT_INICIO_VIDEO IS NOT NULL  --VIDEOCONFERÊNCIA 
                    ORDER BY SSOL_ID_DOCUMENTO, DATA_FIM_CHAMADO ASC  
                    ";
//       Zend_Debug::dump($q);
//       exit;
        return $db->query($q)->fetchAll();
        ;
    }

    /**
     * Indicador Índice de Soluções dos Chamados Encerrados no Prazo (ISC)
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getDatasSLA_ICR ($idCaixa, $data_inicial, $data_final, $fusoHorario, $idIndicador) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT SUB_3.*,
                    REPLACE( (TO_DATE(DATA_RECUSA, 'DD/MM/YYYY hh24:mi:ss' )) - (TO_DATE(DATA_BAIXA, 'DD/MM/YYYY hh24:mi:ss' )) ,',','.') AS DIAS_RECUSA,
                    (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = SUB_3.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $idIndicador
                    ) AS DESCONSIDERADO_ICR
             FROM(
                SELECT SUB_2.*,
                TO_CHAR((
                        SELECT MIN(MOFA_1.MOFA_DH_FASE + (($fusoHorario)/24) )
                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO = SUB_2.MOFA_ID_MOVIMENTACAO
                        AND    MOFA_1.MOFA_ID_FASE = 1000
                        ), 'DD/MM/YYYY hh24:mi:ss' ) AS DATA_BAIXA,

                TO_CHAR((
                        SELECT MIN(MOFA_1.MOFA_DH_FASE + (($fusoHorario)/24) )
                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO = SUB_2.MOFA_ID_MOVIMENTACAO
                        AND    MOFA_1.MOFA_ID_FASE = 1019
                        AND    MOFA_DH_FASE > (
                                                SELECT MIN(MOFA_1.MOFA_DH_FASE)
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO = SUB_2.MOFA_ID_MOVIMENTACAO
                                                AND    MOFA_1.MOFA_ID_FASE = 1000
                                                )

                        ), 'DD/MM/YYYY hh24:mi:ss' ) AS DATA_RECUSA
                FROM
                ( 
                 SELECT SUB_1.* FROM (

                                        SELECT SSOL_ID_DOCUMENTO, DOCM_NR_DOCUMENTO, MOFA_ID_MOVIMENTACAO, DATA_CHAMADO, DATA_AVALIACAO_FINAL FROM (

                                        SELECT DOCM.DOCM_ID_DOCUMENTO, TO_CHAR((MOFA.MOFA_DH_FASE + (($fusoHorario)/24)), 'DD/MM/YYYY HH24:MI:SS') DATA_AVALIACAO_FINAL
                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM
                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA 
                                        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                        WHERE MOFA.MOFA_ID_FASE = 1014
                                        AND DOCM.DOCM_ID_TIPO_DOC = 160
                                        AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_MOVI.MOFA_DH_FASE) 
                                                                   FROM SAD_TB_MOFA_MOVI_FASE MOFA_MOVI 
                                                                  WHERE MOFA_MOVI.MOFA_ID_FASE = 1014
                                                                    AND MOFA_MOVI.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                        AND DOCM.DOCM_ID_DOCUMENTO = (SELECT DISTINCT MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                             FROM SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                         ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                          INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                         ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                         WHERE MODE_MOVI_1.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                                                         AND MODO_MOVI_1.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                                        AND (MOFA.MOFA_DH_FASE + (($fusoHorario)/24)) BETWEEN TO_DATE('$data_inicial', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final', 'DD/MM/YYYY HH24:MI:SS')                                  
                                        ) TAB_1, 


                                        (SELECT  
                        SSOL_ID_DOCUMENTO,
                        DOCM_NR_DOCUMENTO,
                        MOFA_ID_MOVIMENTACAO,
                                        TO_CHAR(MOVI_DH_ENCAMINHAMENTO + (($fusoHorario)/24), 'DD/MM/YYYY HH24:MI:SS') DATA_CHAMADO
                        FROM           
                        -- solicitacao    
                        SOS_TB_SSOL_SOLICITACAO SSOL

                        -- documento
                        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                        -- documento movimentacao
                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

                        -- movimentacao origem
                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

                        -- movimentacao destino
                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

                        --fase
                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO

                                        where MOFA.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                        FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO)
                        --movimentação avaliada
                        AND 0 <  (SELECT COUNT(MOFA_1.MOFA_ID_FASE)
                                    FROM     SAD_TB_MOFA_MOVI_FASE MOFA_1 
                                    WHERE    MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                    AND      MOFA_ID_FASE IN ( 1019,1014)
                            )                               

                    --Solicitação                               
                    AND DOCM.DOCM_ID_TIPO_DOC = 160
                    --Caixa de atendiemnto      
                    AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa 
                                        ) TAB_2
                                        WHERE TAB_1.DOCM_ID_DOCUMENTO = TAB_2.SSOL_ID_DOCUMENTO  ) SUB_1                
            ) SUB_2 
            ) SUB_3";
//        Zend_Debug::dump($q);
//        EXIT;
//        WHERE TO_DATE(DATA_AVALIACAO_FINAL, 'DD/MM/YYYY HH24:MI:SS')  BETWEEN TO_DATE('$data_inicial', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final', 'DD/MM/YYYY HH24:MI:SS') 

        return $db->query($q)->fetchAll();
    }

    /**
     * Indicador Índices de Chamados Resolvidos pelo Contratante (IRC)
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getDatasSLA_IRC ($idCaixa, $data_inicial, $data_final) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM ( 
                SELECT * FROM (
                SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    TO_CHAR(F.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   (
                   SELECT  MOFA.MOFA_CD_MATRICULA
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                                 AND MOFA.MOFA_DH_FASE = 
                                                       (SELECT  MIN(MOFA_1.MOFA_DH_FASE)
                                                                     FROM   SOS_TB_SSOL_SOLICITACAO SSOL_1
                                                                     INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                     ON  SSOL_1.SSOL_ID_DOCUMENTO     = DOCM_1.DOCM_ID_DOCUMENTO
                                                                     INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                     ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                     INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                     ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                     INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                     ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                     INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                                     ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                                     WHERE    DOCM_1.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                                                     AND    DOCM_1.DOCM_ID_TIPO_DOC = 160
                                                                     AND    MOFA_1.MOFA_DH_FASE > F.MOFA_DH_FASE
                                                                     AND    MOFA_1.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                                                        )
                   ) AS MATRICULA_BAIXA_ENCAM
                   ,
                   (
                                SELECT FF.MOFA_ID_FASE
                                FROM  SAD_TB_DOCM_DOCUMENTO BB
                                     INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                     ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                     INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                     ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                     INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                     ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                     INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                     ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                WHERE  FF.MOFA_DH_FASE = (SELECT MAX(FFF.MOFA_DH_FASE)
                                                                  FROM SAD_TB_DOCM_DOCUMENTO BBB
                                                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                 ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                 ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                 ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF
                                                                 ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                  
                                                                 WHERE    BBB.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                                                 AND FFF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                                                                 AND BBB.DOCM_ID_TIPO_DOC = 160
                                                                 AND FFF.MOFA_ID_FASE = 1019  
                                                                 )
                                AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                                AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO 
                                AND B.DOCM_ID_TIPO_DOC = 160
                                AND FF.MOFA_ID_FASE = 1019                              
                   ) AS RECUSADA
                    FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                               WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa  
                               AND B.DOCM_ID_TIPO_DOC = 160
                               AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                                                        FROM   SAD_TB_DOCM_DOCUMENTO BB
                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                                               ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                                               ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                                               ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                                               ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                                        WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                                                        FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                                               ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                                               ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                                               ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                                               ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                                                           WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                                                           AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                                                        AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                                                        AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO) 
                                                        AND ROWNUM = 1                                                       
                                                        ) 
                                                        WHERE DATA_FIM_CHAMADO IS NOT NULL
                                                        AND RECUSADA IS NULL
                                                        AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                     
                    UNION
                     SELECT * FROM 
                    (
                    SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    TO_CHAR(F.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   (
                   SELECT  MOFA.MOFA_CD_MATRICULA
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_FASE = 1000
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                                 AND MOFA.MOFA_DH_FASE = 
                                                       (SELECT  MIN(MOFA_1.MOFA_DH_FASE)
                                                                     FROM   SOS_TB_SSOL_SOLICITACAO SSOL_1
                                                                     INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                     ON  SSOL_1.SSOL_ID_DOCUMENTO     = DOCM_1.DOCM_ID_DOCUMENTO
                                                                     INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                     ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                     INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                     ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                     INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                     ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                     INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                                     ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                                     WHERE    DOCM_1.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                                                     AND    DOCM_1.DOCM_ID_TIPO_DOC = 160
                                                                     AND    MOFA_1.MOFA_DH_FASE > F.MOFA_DH_FASE
                                                                     AND    MOFA_1.MOFA_ID_FASE = 1000
                                                                     AND    MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                                                        )
                   ) AS MATRICULA_BAIXA_ENCAM
                   ,
                   NULL AS RECUSADA
                    FROM   SOS_TB_SSOL_SOLICITACAO A
                                          INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                                          ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                                          INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                                          ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                                          INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                                          ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                                          INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                                          ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                                          INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                                          ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                               WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                               /*TIPO DOCUMENTO SOLICITAÇÃO*/
                               AND B.DOCM_ID_TIPO_DOC = 160
                               AND F.MOFA_DH_FASE  = (SELECT FF.MOFA_DH_FASE 
                                                            FROM   SAD_TB_DOCM_DOCUMENTO BB
                                                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DD
                                                                   ON  BB.DOCM_ID_DOCUMENTO     = DD.MODO_ID_DOCUMENTO
                                                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CC
                                                                   ON  DD.MODO_ID_MOVIMENTACAO  = CC.MOVI_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EE
                                                                   ON  CC.MOVI_ID_MOVIMENTACAO  = EE.MODE_ID_MOVIMENTACAO
                                                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE FF
                                                                   ON  CC.MOVI_ID_MOVIMENTACAO  = FF.MOFA_ID_MOVIMENTACAO
                                                            WHERE  FF.MOFA_DH_FASE = (SELECT MIN(FFF.MOFA_DH_FASE)
                                                                                        FROM  SAD_TB_DOCM_DOCUMENTO BBB
                                                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO DDD
                                                                                               ON  BBB.DOCM_ID_DOCUMENTO     = DDD.MODO_ID_DOCUMENTO
                                                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO CCC
                                                                                               ON  DDD.MODO_ID_MOVIMENTACAO  = CCC.MOVI_ID_MOVIMENTACAO
                                                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO EEE
                                                                                               ON  CCC.MOVI_ID_MOVIMENTACAO  = EEE.MODE_ID_MOVIMENTACAO
                                                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE FFF 
                                                                                               ON  CCC.MOVI_ID_MOVIMENTACAO  = FFF.MOFA_ID_MOVIMENTACAO                                                                                      
                                                                                           WHERE    BB.DOCM_ID_DOCUMENTO = BBB.DOCM_ID_DOCUMENTO
                                                                                           AND      FF.MOFA_ID_MOVIMENTACAO = FFF.MOFA_ID_MOVIMENTACAO )
                                                            AND B.DOCM_ID_DOCUMENTO = BB.DOCM_ID_DOCUMENTO
                                                            AND F.MOFA_ID_MOVIMENTACAO = FF.MOFA_ID_MOVIMENTACAO)
                                                            )
                                                             WHERE DATA_FIM_CHAMADO IS NOT NULL
                                                             AND RECUSADA IS NULL
                                                             AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                            )
                            ORDER BY SSOL_ID_DOCUMENTO, DATA_FIM_CHAMADO ASC                         ";
//        Zend_Debug::dump($q);exit;
        return $db->query($q)->fetchAll();
    }

    /**
     * Indicador Índices de Chamados com Não Conformidade (INC)
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getDatasSLA_INC ($idCaixa, $data_inicial, $data_final, $fusoHorario, $indicadorINC) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT * FROM ( 
                   SELECT  SSOL_ID_DOCUMENTO,
                                DOCM_NR_DOCUMENTO, 
                                MOFA_ID_FASE,
                                MOFA_ID_MOVIMENTACAO,
                                TO_CHAR(MOVI_DH_ENCAMINHAMENTO + (($fusoHorario)/24) , 'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO, 

                                            (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE + (($fusoHorario)/24), 'DD/MM/YYYY HH24:MI:SS') 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                        ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                        ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                        WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                        AND   MOFA_2.MOFA_ID_FASE = 1014 )) DATA_AVALIACAO
                                                                                        
                ,                                                                       
                (
                SELECT COUNT(MVCO_ID_MOVIMENTACAO) NAO_CONFORME
                FROM SOS_TB_MVCO_MOVIM_N_CONFORM
                WHERE MVCO_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND   MVCO_IC_ATIVO_INATIVO = 'S'
                ) AS NAO_CONFORME
                ,
                (
                SELECT DSIN_ID_MOVIMENTACAO
                FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                WHERE DSIN_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND DSIN_ID_INDICADOR = $indicadorINC
                ) AS DESCONSIDERADO_INC
              FROM           
        -- solicitacao    
        SOS_TB_SSOL_SOLICITACAO SSOL

        -- documento
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        -- movimentacao origem
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

        -- movimentacao destino
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        --fase
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
        
        
            WHERE  
         --Foi avaliado
        '1014'  = (SELECT MOFA_1.MOFA_ID_FASE 
                      FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                      WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                      FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                             INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                             ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                             INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                             ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                             INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                             ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                             INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                             ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                             WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1014 ))
        
        --Movimentações
            AND MOFA.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                        FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO)
        
            AND DOCM.DOCM_ID_TIPO_DOC = 160 
            AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa 
        )
        WHERE    TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS')  BETWEEN TO_DATE('$data_inicial', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final', 'DD/MM/YYYY HH24:MI:SS') 
        ORDER BY TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') ASC,TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS')ASC, DOCM_NR_DOCUMENTO ASC";

        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
        ;
    }

    /**
     * IIA – Índice de Início de Atendimento no Prazo
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getAtendUsuDatasSLA_IIA ($idCaixa, $idNivel, $data_inicial, $data_final, $fusoHorario, $idIndicador) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM (
                SELECT 
                A.SSOL_ID_DOCUMENTO,
                F.MOFA_ID_MOVIMENTACAO, 
                F.MOFA_DS_COMPLEMENTO,
                B.DOCM_NR_DOCUMENTO,
                TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                         FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                         INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                         ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                         INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                         ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                         INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                         ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                         INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                         INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                         WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                         AND    DOCM.DOCM_ID_TIPO_DOC = 160
                         AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                ),'DD/MM/YYYY HH24:MI:SS') AS DATA_PRIMEIRO_ATENDIMENTO
                ,
                (
                SELECT DSIN_ID_MOVIMENTACAO
                FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                AND DSIN_ID_INDICADOR = $idIndicador
                ) AS DESCONSIDERADO_IAA
                FROM   SOS_TB_SSOL_SOLICITACAO A
                                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                                      INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC I
                                      ON  F.MOFA_ID_MOVIMENTACAO  = I.SNAS_ID_MOVIMENTACAO
                                      INNER JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO N
                                      ON  N.SNAT_ID_NIVEL         =  I.SNAS_ID_NIVEL                    
                                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                      AND N.SNAT_CD_NIVEL = $idNivel
                           /*TIPO DOCUMENTO SOLICITAÇÃO*/
                           AND B.DOCM_ID_TIPO_DOC = 160 --Tipo de documento = solicitação de ti
                           AND F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                            )
                            WHERE DATA_PRIMEIRO_ATENDIMENTO IS NOT NULL ";
        $q.= " AND TO_DATE(DATA_PRIMEIRO_ATENDIMENTO,  'DD/MM/YYYY HH24:MI:SS') BETWEEN 
                                            TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') 
                                            AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS') ";
        $q.="  ORDER BY SSOL_ID_DOCUMENTO, DATA_PRIMEIRO_ATENDIMENTO ASC";
        //       Zend_Debug::dump($q);exit;
        return $db->query($q)->fetchAll();
    }

    /**
     * ISS – Índice de Soluções das Solicitações no Prazo
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @param type $fusoHorario
     * @param type $idIndicador
     * @return type 
     */
    public function getAtendUsuDatasSLA_ISS ($idCaixa, $data_inicial, $data_final, $fusoHorario, $indicadorISS, $indicadorIAP, $indicadorISD) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT
                SSOL_ID_DOCUMENTO,MOFA_ID_MOVIMENTACAO,MOFA_DS_COMPLEMENTO,DOCM_NR_DOCUMENTO,DATA_CHAMADO,
                MAX(DATA_FIM_CHAMADO) DATA_FIM_CHAMADO,RECUSADA,SSPA_DT_PRAZO,PRAZO_ULTRAPASSADO,DESCONSIDERADO_ISS,
                DESCONSIDERADO_IAP,DESCONSIDERADO_ISD
                FROM (
                SELECT
                    SSOL_ID_DOCUMENTO,
                    MOFA_ID_MOVIMENTACAO,
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )
                          THEN 1
                        ELSE 0
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_ISS,
                    DESCONSIDERADO_IAP,
                    DESCONSIDERADO_ISD
                FROM (
                SELECT
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO,
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DO ENCAMINHAMENTO PARA OUTRO GRUPO
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                    --FOI RECUSADA NÃO CONTA O ENCAMINHAMENTO COMO FIM DO CHAMADO FICA SENDO A DATA DA BAIXA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND   FF.MOFA_ID_FASE = 1019
                   ) AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorISS
                    ) AS DESCONSIDERADO_ISS
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorIAP
                    ) AS DESCONSIDERADO_IAP
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorISD
                    ) AS DESCONSIDERADO_ISD
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE)
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    )
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')

                    UNION
                     SELECT
                    SSOL_ID_DOCUMENTO,
                    MOFA_ID_MOVIMENTACAO,
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )
                          THEN 1
                        ELSE 0
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_ISS,
                    DESCONSIDERADO_IAP,
                    DESCONSIDERADO_ISD
                     FROM
                    (
                    SELECT
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO,
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DA PRIMEIRA BAIXA
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   NULL AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorISS
                    ) AS DESCONSIDERADO_ISS
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorIAP
                    ) AS DESCONSIDERADO_IAP
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorISD
                    ) AS DESCONSIDERADO_ISD
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE)
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    )
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                    )
                    GROUP BY SSOL_ID_DOCUMENTO,MOFA_ID_MOVIMENTACAO,MOFA_DS_COMPLEMENTO,DOCM_NR_DOCUMENTO,DATA_CHAMADO,RECUSADA,SSPA_DT_PRAZO,PRAZO_ULTRAPASSADO,DESCONSIDERADO_ISS,DESCONSIDERADO_IAP,DESCONSIDERADO_ISD
                    ";
//       Zend_Debug::dump($q);
//       exit;
        return $db->query($q)->fetchAll();
        ;
    }

    /**
     * ISD – Índice de chamados solucionados no mesmo dia
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @param type $fusoHorario
     * @param type $idIndicador
     * @return type 
     */
    public function getAtendUsuDatasSLA_ISD ($idCaixa, $data_inicial, $data_final, $fusoHorario, $idIndicador) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM ( 
                SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO
                FROM (
                SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DO ENCAMINHAMENTO PARA OUTRO GRUPO
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                                 FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                 INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                    --FOI RECUSADA NÃO CONTA O ENCAMINHAMENTO COMO FIM DO CHAMADO FICA SENDO A DATA DA BAIXA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                    AND   FF.MOFA_ID_FASE = 1019                              
                   ) AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $idIndicador
                    ) AS DESCONSIDERADO
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                     
                    UNION
                     SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO,
                    DESCONSIDERADO
                     FROM 
                    (
                    SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE + (($fusoHorario)/24),'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DA PRIMEIRA BAIXA
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE) + (($fusoHorario)/24)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   NULL AS RECUSADA
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $idIndicador
                   ) AS DESCONSIDERADO
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('$data_inicial',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final',  'DD/MM/YYYY HH24:MI:SS')
                    )
                    ORDER BY SSOL_ID_DOCUMENTO, DATA_FIM_CHAMADO ASC  
                    ";
//       Zend_Debug::dump($q);
//       exit;
        return $db->query($q)->fetchAll();
        ;
    }

    /**
     * ITP – Índice de Ligações Telefônicas Perdidas
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getAtendUsuDatasSLA_ITP ($idCaixa, $data_inicial, $data_final) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT DISTINCT SSOL_ID_DOCUMENTO,B.DOCM_NR_DOCUMENTO, MOFA_ID_FASE,TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
                    DOCM_CD_MATRICULA_CADASTRO, SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    MODE_ID_CAIXA_ENTRADA,SNAS_ID_NIVEL,SSER_ID_SERVICO, SSER_DS_SERVICO , SESP_DH_LIMITE_ESP, MOFA_ID_MOVIMENTACAO, TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO
                    FROM   SOS_TB_SSOL_SOLICITACAO A
                           INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                           ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                           INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                           ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                           INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                           ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                           INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                           ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                           INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                           ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                           LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC I
                           ON  F.MOFA_ID_MOVIMENTACAO  = I.SNAS_ID_MOVIMENTACAO
                           LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC G
                           ON  F.MOFA_ID_MOVIMENTACAO  = G.SSES_ID_MOVIMENTACAO
                           LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA J
                           ON  F.MOFA_ID_MOVIMENTACAO  = J.SESP_ID_MOVIMENTACAO
                           LEFT JOIN SOS_TB_SSER_SERVICO H
                           ON  G.SSES_ID_SERVICO = H.SSER_ID_SERVICO                               
                    WHERE    F.MOFA_DH_FASE  = B.DOCM_DH_FASE
                    AND      F.MOFA_ID_MOVIMENTACAO = B.DOCM_ID_MOVIMENTACAO
                    AND H.SSER_ID_SERVICO = (SELECT GG.SSES_ID_SERVICO /*SERVIÇO DA SOLICITAÇÃO*/
                                                FROM   SAD_TB_MOFA_MOVI_FASE FF
                                                       INNER JOIN SOS_TB_SSES_SERVICO_SOLIC GG
                                                       ON  FF.MOFA_ID_MOVIMENTACAO  = GG.SSES_ID_MOVIMENTACAO
                                                       AND FF.MOFA_DH_FASE          = GG.SSES_DH_FASE
                                                WHERE  FF.MOFA_DH_FASE = (SELECT MAX(FFF.MOFA_DH_FASE)
                                                                                FROM   SAD_TB_MOFA_MOVI_FASE FFF
                                                                                   INNER JOIN SOS_TB_SSES_SERVICO_SOLIC GGG
                                                                                   ON  FFF.MOFA_ID_MOVIMENTACAO  = GGG.SSES_ID_MOVIMENTACAO
                                                                                   AND FFF.MOFA_DH_FASE          = GGG.SSES_DH_FASE                                                                                         
                                                                                   WHERE    GGG.SSES_ID_DOCUMENTO = GG.SSES_ID_DOCUMENTO)
                                                AND B.DOCM_ID_DOCUMENTO = GG.SSES_ID_DOCUMENTO)
                    AND (I.SNAS_ID_NIVEL = (SELECT II.SNAS_ID_NIVEL /*NÍVEL DE ATENDIMENTO*/
                                                FROM   SAD_TB_MOFA_MOVI_FASE FF
                                                       INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC II
                                                       ON  FF.MOFA_ID_MOVIMENTACAO  = II.SNAS_ID_MOVIMENTACAO
                                                       AND FF.MOFA_DH_FASE          = II.SNAS_DH_FASE
                                                WHERE  FF.MOFA_DH_FASE = (SELECT MAX(FFF.MOFA_DH_FASE)
                                                                                    FROM SAD_TB_MOFA_MOVI_FASE FFF
                                                                                       INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC III
                                                                                       ON  FFF.MOFA_ID_MOVIMENTACAO  = III.SNAS_ID_MOVIMENTACAO
                                                                                       AND FFF.MOFA_DH_FASE          = III.SNAS_DH_FASE
                                                                                       WHERE    III.SNAS_ID_DOCUMENTO = II.SNAS_ID_DOCUMENTO)
                                                AND B.DOCM_ID_DOCUMENTO = II.SNAS_ID_DOCUMENTO ) OR I.SNAS_ID_NIVEL IS NULL)
                    AND (J.SESP_DH_LIMITE_ESP = ( SELECT JJ.SESP_DH_LIMITE_ESP /*SOLICITAÇÃO EM ESPERA*/
                                                FROM   SAD_TB_MOFA_MOVI_FASE FF
                                                       INNER JOIN SOS_TB_SESP_SOLIC_ESPERA JJ
                                                       ON  FF.MOFA_ID_MOVIMENTACAO  = JJ.SESP_ID_MOVIMENTACAO
                                                       AND FF.MOFA_DH_FASE          = JJ.SESP_DH_FASE
                                                WHERE    FF.MOFA_DH_FASE = (SELECT MAX(FFF.MOFA_DH_FASE)
                                                                                    FROM  SAD_TB_MOFA_MOVI_FASE FFF
                                                                                       INNER JOIN SOS_TB_SESP_SOLIC_ESPERA JJJ
                                                                                       ON  FFF.MOFA_ID_MOVIMENTACAO  = JJJ.SESP_ID_MOVIMENTACAO
                                                                                       AND FFF.MOFA_DH_FASE          = JJJ.SESP_DH_FASE
                                                                                    WHERE    JJJ.SESP_ID_DOCUMENTO = JJ.SESP_ID_DOCUMENTO)
                                                AND B.DOCM_ID_DOCUMENTO = JJ.SESP_ID_DOCUMENTO )OR J.SESP_DH_LIMITE_ESP IS NULL)
                    AND E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                    AND F.MOFA_ID_FASE = 1000 
                    AND MOFA_CD_MATRICULA NOT LIKE '%PS' ";
        (($data_inicial != "") && ($data_final != "")) ? ($q .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $data_inicial . "',  'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $data_final . "',  'DD/MM/YYYY HH24:MI:SS') ") : ("");
        //Zend_Debug::dump($q);exit;
        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

    /**
     * INC – Índice de chamados com Não Conformidade
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getAtendUsuDatasSLA_INC ($idCaixa, $data_inicial, $data_final, $fusoHorario, $indicadorINC) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT SSOL_ID_DOCUMENTO, DOCM_NR_DOCUMENTO, MOFA_ID_FASE, MOFA_ID_MOVIMENTACAO, MOVI_DH_ENCAMINHAMENTO, DATA_AVALIACAO, NAO_CONFORME, DESCONSIDERADO_INC
         FROM (

        SELECT DOCM.DOCM_ID_DOCUMENTO, 
               TO_CHAR((MOFA.MOFA_DH_FASE + (($fusoHorario)/24)), 'DD/MM/YYYY HH24:MI:SS') DATA_AVALIACAO      
          FROM  SAD_TB_DOCM_DOCUMENTO DOCM
         INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
         ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
         INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
         ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
         INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
         INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA 
         ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
         WHERE MOFA.MOFA_ID_FASE = 1014
           AND DOCM.DOCM_ID_TIPO_DOC = 160
           AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_MOVI.MOFA_DH_FASE) 
                                      FROM SAD_TB_MOFA_MOVI_FASE MOFA_MOVI 
                                     WHERE MOFA_MOVI.MOFA_ID_FASE = 1014
                                       AND MOFA_MOVI.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
           AND DOCM.DOCM_ID_DOCUMENTO = (SELECT DISTINCT MODO_MOVI_1.MODO_ID_DOCUMENTO
                                             FROM SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                         ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                          INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                         ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                         WHERE MODE_MOVI_1.MODE_ID_CAIXA_ENTRADA = $idCaixa
                                         AND MODO_MOVI_1.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
           AND (MOFA.MOFA_DH_FASE + (($fusoHorario)/24))  BETWEEN TO_DATE('$data_inicial', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('$data_final', 'DD/MM/YYYY HH24:MI:SS') 
           ) TAB_1, 

                    (SELECT  SSOL_ID_DOCUMENTO,
                                DOCM_NR_DOCUMENTO, 
                                MOFA_ID_FASE,
                                MOFA_ID_MOVIMENTACAO,
                                TO_CHAR(MOVI_DH_ENCAMINHAMENTO + (($fusoHorario)/24) , 'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO, 

                (
                SELECT COUNT(MVCO_ID_MOVIMENTACAO) NAO_CONFORME
                FROM SOS_TB_MVCO_MOVIM_N_CONFORM
                WHERE MVCO_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND   MVCO_IC_ATIVO_INATIVO = 'S'
                ) AS NAO_CONFORME
                ,
                (
                SELECT DSIN_ID_MOVIMENTACAO
                FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                WHERE DSIN_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND DSIN_ID_INDICADOR = $indicadorINC
                ) AS DESCONSIDERADO_INC
              FROM           
                -- SOLICITACAO    
        SOS_TB_SSOL_SOLICITACAO SSOL

                -- DOCUMENTO
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                -- DOCUMENTO MOVIMENTACAO
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

                -- MOVIMENTACAO ORIGEM
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

                -- MOVIMENTACAO DESTINO
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

                --FASE
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
        
            WHERE  
                  MOFA.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                        FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO)
        
            AND DOCM.DOCM_ID_TIPO_DOC = 160 
                    AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa) TAB_2
        
        WHERE TAB_1.DOCM_ID_DOCUMENTO = TAB_2.SSOL_ID_DOCUMENTO
        ORDER BY DATA_AVALIACAO ASC,MOVI_DH_ENCAMINHAMENTO ASC, DOCM_NR_DOCUMENTO ASC";
//        echo '<pre>';
//        Zend_Debug::dump($stmt);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
        ;
    }

    public function getCaixaSemNivelControleSLA ($idCaixa, $params, $order, $todasFases = false) {

        switch ($order) {
            case 'MOVI_DH_ENCAMINHAMENTO ASC':
                $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'MOVI_DH_ENCAMINHAMENTO DESC':
                $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            case 'DATA_AVALIACAO ASC':
                $order = "TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'DATA_AVALIACAO DESC':
                $order = "TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            default:
                break;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT * FROM ( 
                   SELECT  SSOL_ID_DOCUMENTO,
                                DOCM_NR_DOCUMENTO, 
                                MOFA_ID_FASE,
                                MOFA_ID_MOVIMENTACAO,
                                TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO, 
                                (SELECT SSER_1.SSER_DS_SERVICO
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                            INNER JOIN SOS_TB_SSER_SERVICO SSER_1
                                            ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE
                                                                                                    INNER JOIN SOS_TB_SSER_SERVICO SSER_2
                                                                                                    ON  SSES_2.SSES_ID_SERVICO       = SSER_2.SSER_ID_SERVICO                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) AS SSER_DS_SERVICO,
                                (SELECT SSER_1.SSER_ID_SERVICO
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                            INNER JOIN SOS_TB_SSER_SERVICO SSER_1
                                            ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE
                                                                                                    INNER JOIN SOS_TB_SSER_SERVICO SSER_2
                                                                                                    ON  SSES_2.SSES_ID_SERVICO       = SSER_2.SSER_ID_SERVICO                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) AS SSER_ID_SERVICO,
                                    ( SELECT STSA_1.STSA_DS_TIPO_SAT
                                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                   INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_1
                                                   ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SAVS_1.SAVS_ID_MOVIMENTACAO
                                                   AND MOFA_1.MOFA_DH_FASE          = SAVS_1.SAVS_DH_FASE
                                                   LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA_1
                                                   ON   SAVS_1.SAVS_ID_TIPO_SAT = STSA_1.STSA_ID_TIPO_SAT 
                                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                               FROM   SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                               ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                               ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                               ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                               ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                               INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_2
                                                                               ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SAVS_2.SAVS_ID_MOVIMENTACAO
                                                                               AND MOFA_2.MOFA_DH_FASE          = SAVS_2.SAVS_DH_FASE
                                                                                WHERE    DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO) 
                                            ) AS STSA_DS_TIPO_SAT
                                            ,
                                            DECODE(
                                            (SELECT COUNT(MOFA_1.MOFA_ID_FASE)
                                                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                               ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                               ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                               ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1 
                                                                               ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO                                                                                      
                                                                           WHERE    DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                           AND      MOFA_1.MOFA_ID_FASE = 1019)
                                                                           ,0, '0',
                                                                           '1') RECUSADO
                                            ,
                                            (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE, 'DD/MM/YYYY HH24:MI:SS') 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                        ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                        ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                        WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                        AND   MOFA_2.MOFA_ID_FASE = 1014 )) DATA_AVALIACAO,
                                            (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE, 'DD/MM/YYYY HH24:MI:SS') 
                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                                    FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                                                            ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                                                            ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                                                            WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                                                            AND   MOFA_2.MOFA_ID_FASE = 1000 )) DATA_BAIXA,
                                            (SELECT MOFA_1.MOFA_CD_MATRICULA
                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                                    FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                                                            ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                                                            ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                                                            WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                                                            AND   MOFA_2.MOFA_ID_FASE = 1000 )) MATRICULA_BAIXA,                                                                                                                                                   
        DOCM_DH_CADASTRO,
        DOCM_SG_SECAO_GERADORA,
        DOCM_CD_LOTACAO_GERADORA
              FROM           
        -- solicitacao    
        SOS_TB_SSOL_SOLICITACAO SSOL

        -- documento
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        -- movimentacao origem
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

        -- movimentacao destino
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        --fase
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
        
        --Movimentações
           WHERE  MOFA.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                        FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO) ";
        
        $stmt .=   $todasFases ? "" :     " AND
         --Foi avaliado
        '1014'  = (SELECT MOFA_1.MOFA_ID_FASE 
                      FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                      WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                      FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                             INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                             ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                             INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                             ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                             INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                             ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                             INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                             ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                             WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1014 ))
        ";
        
        $stmt .= "    AND DOCM.DOCM_ID_TIPO_DOC = 160 
            AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa 
        ) 
        WHERE 1 = 1
        ";

        /* Data de avaliação */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY  HH24:MI:SS') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY  HH24:MI:SS') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY  HH24:MI:SS') ") : ("");

        /* Data de baixa */
        (($params['DATA_INICIAL_BAIXA'] == "") && ($params['DATA_FINAL_BAIXA'] != "")) ? ($stmt .= "AND TO_DATE(DATA_BAIXA, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_FINAL_BAIXA'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL_BAIXA'] . "', 'DD/MM/YYYY  HH24:MI:SS')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_BAIXA'] != "") && ($params['DATA_FINAL_BAIXA'] == "")) ? ($stmt .= "AND TO_DATE(DATA_BAIXA, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL_BAIXA'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_INICIAL_BAIXA'] . "', 'DD/MM/YYYY  HH24:MI:SS')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_BAIXA'] != "") && ($params['DATA_FINAL_BAIXA'] != "")) ? ($stmt .= "AND TO_DATE(DATA_BAIXA, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL_BAIXA'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL_BAIXA'] . "', 'DD/MM/YYYY  HH24:MI:SS')+1-1/24/60/60 ") : ("");

        /* Atendente */
        $stmt .= ( $params['SSOL_CD_MATRICULA_ATENDENTE']) ? (" AND MATRICULA_BAIXA = '" . $params['SSOL_CD_MATRICULA_ATENDENTE'] . "' ") : ('');

        /* Unidade fase */
        $stmt .= ( $params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' ") : ('');

        /* Unidade solicitante */
        $stmt .= ( $params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $stmt .= ( $params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " ") : ('');

        /* Solicitante */
        $stmt .= ( $params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' ") : ('');

        /* Categorias */
        if (is_array($params['CATE_ID_CATEGORIA'])) {
            //Remove valores vazios da array
            if (array_search("", $params['CATE_ID_CATEGORIA']) !== false) {
                unset($params['CATE_ID_CATEGORIA'][array_search("", $params['CATE_ID_CATEGORIA'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['CATE_ID_CATEGORIA']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['CATE_ID_CATEGORIA']);
                // Retira a utima virgula
                $stmt .= ( $params['CATE_ID_CATEGORIA']) ? (" 
                    AND SSOL_ID_DOCUMENTO IN( " .
                    "(
                    SELECT B.CASO_ID_DOCUMENTO 
                    FROM SOS.SOS_TB_CATE_CATEGORIA A,
                    SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                    WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                    AND A.CATE_ID_CATEGORIA IN ($value_query)
                    AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                    AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                    )"
                    . ") ") : ('');
            }
        }

        /* Serviço */
        if (is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $stmt .= ( $params['SSER_DS_SERVICO']) ? (" AND SSER_DS_SERVICO LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Data de cadastro */
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY HH24:MI:SS')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY HH24:MI:SS')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY HH24:MI:SS')+1-1/24/60/60 ") : ("");

        /* Número da solicitação */
        $stmt .= ( $params['DOCM_NR_DOCUMENTO'] ) ? (" AND DOCM_NR_DOCUMENTO = " . $params['DOCM_NR_DOCUMENTO'] . " ") : ('');

        $stmt .= "ORDER BY $order";

        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getCaixaSemNivelExtensaoPrazo ($idCaixa, $order) {
        $app_Sosti_CaixasQuerys = new App_Sosti_CaixasQuerys();
        switch ($order) {
            case 'SSPA_DT_PRAZO_SOLICITADO DESC':
                $order = "TO_DATE(SSPA_DT_PRAZO_SOLICITADO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            case 'SSPA_DT_PRAZO_SOLICITADO ASC':
                $order = "TO_DATE(SSPA_DT_PRAZO_SOLICITADO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'SSPA_DT_PRAZO ASC':
                $order = "TO_DATE(SSPA_DT_PRAZO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'SSPA_DT_PRAZO DESC':
                $order = "TO_DATE(SSPA_DT_PRAZO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            case 'MOVI_DH_ENCAMINHAMENTO ASC':
                $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'MOVI_DH_ENCAMINHAMENTO DESC':
                $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            default:
                break;
        }

        $query = $app_Sosti_CaixasQuerys->selectCaixa(2);
        $query .= ($idCaixa == Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA) ? $app_Sosti_CaixasQuerys->colunasServicosSistemas() : '';
        $query .= '

            --PRAZO            
            ,TO_CHAR((SSPA_DT_PRAZO), \'DD/MM/YYYY HH24:MI:SS\') SSPA_DT_PRAZO_SOLICITADO,
            SSPA_IC_CONFIRMACAO';

        $query .= $app_Sosti_CaixasQuerys->from();
        $query .= $app_Sosti_CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $query .= $app_Sosti_CaixasQuerys->leftJoinFaseServico();
        $query .= $app_Sosti_CaixasQuerys->leftJoinPriorizaDemanda();
        //verificar se é necessário
        $query .= $app_Sosti_CaixasQuerys->leftJoinFaseEspera();
        $query .= '
            LEFT JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA 
                ON  SSPA.SSPA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO';
        $query .= ($idCaixa == Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA) ? $app_Sosti_CaixasQuerys->leftJoinServicosSistemas() : '';
        //CLAUSULAS WHERE
        $query .= $app_Sosti_CaixasQuerys->where();
        $query .= $app_Sosti_CaixasQuerys->whereUltimoServico(false);
        $query .= $app_Sosti_CaixasQuerys->whereUltimaMovimentacao();
        $query .= $app_Sosti_CaixasQuerys->whereUltimaMovimentacao();
        $query .= '
            AND SSPA.SSPA_DT_PRAZO =   (SELECT SSPA_1.SSPA_DT_PRAZO 
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                                        WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                        AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                        AND SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)
            AND MOFA_ID_FASE NOT IN(1000,1014)
            AND SSPA_IC_CONFIRMACAO IS NULL
        
            /*TIPO DOCUMENTO SOLICITAÇÃO*/
            AND DOCM.DOCM_ID_TIPO_DOC = 160 
            AND SSPA_DT_PRAZO IS NOT NULL
            AND FADM.FADM_ID_FASE <> 1026
            AND MODE_ID_CAIXA_ENTRADA = ' . $idCaixa . '  
            ORDER BY ' . $order;



//        Zend_Debug::dump($query);exit;
        //exit;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $grupoServico = $db->fetchAll($query);

        return $grupoServico;
    }

    /**
     * Busca as extensões de prazo das caixas que o usuário possuir acesso
     * @param Object SESSION Do usuário
     * @return array
     */
    public function getQtdSosExtensaoPrazo ($userNs) {

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();

        /*
         * Busando Array de Caixas que o usuário tiver acesso
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
           SELECT
                CTRL_NM_CONTROLE_SISTEMA
            FROM    
                --RELACIONA A PESSOA COM A UNIDADE E O PERFIL
                OCS_TB_PUPE_PERFIL_UNID_PESSOA
                INNER JOIN OCS_TB_UNPE_UNIDADE_PERFIL
                   ON PUPE_ID_UNIDADE_PERFIL = UNPE_ID_UNIDADE_PERFIL       
                --RELACINA OS PERFIS DA UNIDADE COM O PAPEL
                INNER JOIN OCS_TB_PSPA_PERFIL_PAPEL
                    ON UNPE_ID_PERFIL = PSPA_ID_PERFIL
                --RELACIONA PERFIL COM PAPEL
                INNER JOIN OCS_TB_PAPL_PAPEL
                    ON PSPA_ID_PAPEL = PAPL_ID_PAPEL
                -- RELACIONA PAPEL COM A AÇÃO/CONTROLE/MODULO
                INNER JOIN OCS_TB_ACAO_ACAO_SISTEMA
                    ON PAPL_ID_ACAO_SISTEMA = ACAO_ID_ACAO_SISTEMA
                INNER JOIN OCS_TB_CTRL_CONTROLE_SISTEMA
                    ON ACAO_ID_CONTROLE_SISTEMA = CTRL_ID_CONTROLE_SISTEMA
                INNER JOIN OCS_TB_MODL_MODULO
                    ON CTRL_ID_MODULO = MODL_ID_MODULO

            WHERE
                PUPE_CD_MATRICULA = '$userNs->matricula'
                AND ACAO_NM_ACAO_SISTEMA = 'index'
                AND CTRL_NM_CONTROLE_SISTEMA IN (
                    'gestaodedemandasdoatendimentoaosusuarios'
                    ,'gestaodedemandasti'
                    ,'gestaodemandasinfraestrutura'
                    ,'gestaodedemandasdonoc'
                    ,'gestaodedemandasdoatendimentoaosusuariossecoes'
                )
                AND MODL_NM_MODULO = 'sosti'
            GROUP BY CTRL_NM_CONTROLE_SISTEMA
        ";
        $arrayControllersAcesso = $db->fetchAll($sql);

        /*
         * Array Com as caixas de gestão
         */
        $arrayTodasCaixas = array(
            1 => 'gestaodedemandasdoatendimentoaosusuarios'
            , 2 => 'gestaodedemandasti'
            , 3 => 'gestaodemandasinfraestrutura'
            , 4 => 'gestaodedemandasdonoc'
        );

        /*
         * Fazendo tratamento do Array
         */
        $arrayAux = array();
        foreach ($arrayControllersAcesso as $value) {
            $arrayAux[] = $value['CTRL_NM_CONTROLE_SISTEMA'];
        }
        $arrayControllersAcesso = $arrayAux;

        /*
         * Pega o ID da Caixa, caso o usuário seja da Seção e o ID não seja um dos TRF, definidos acima
         */
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($userNs->siglasecao, $userNs->codlotacao);
        /*
         * Pega as caixas da Gestão que o usuário possui acesso
         * e pega os IDS dessas Caixas
         */
        $caixasAcesso = array_intersect($arrayTodasCaixas, $arrayControllersAcesso);
        $arrayIdCaixas = array_keys($caixasAcesso);
        /*
         * Se a Caixa buscada da Seção for a mesma que alguma Caixa da Gestão, então faz apenas uma busca
         * Desta forma, se a Caixa da Seção buscada não for uma Caixa da Gestão, então adiciona o ID na lista
         * para o sistema fazer a busca dela também
         */
        if (count($SgrsGrupoServico) > 0 && !isset($arrayTodasCaixas[$SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"]])) {
            $arrayIdCaixas[] = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
        }
        /*
         * Monta uma String para buscar no banco na cláusula IN
         */
        $idsCaixa = implode(',', $arrayIdCaixas);

        /*
         * Busca as Caixas da Gestão que o usuário tem acesso e a quantidade de Solicitações com extensão de prazo
         * pendentes para cada caixa
         */
        $app_Sosti_CaixasQuerys = new App_Sosti_CaixasQuerys();
        $query = "SELECT COUNT(*) AS QTD,MODE_ID_CAIXA_ENTRADA,CXEN_DS_CAIXA_ENTRADA FROM ( SELECT DISTINCT SSOL_ID_DOCUMENTO,MODE_ID_CAIXA_ENTRADA,CXEN_DS_CAIXA_ENTRADA";
        $query .= $app_Sosti_CaixasQuerys->from();
        $query .= $app_Sosti_CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $query .= $app_Sosti_CaixasQuerys->leftJoinFaseServico();
        //verificar se é necessário
        $query .= $app_Sosti_CaixasQuerys->leftJoinFaseEspera();
        $query .= '
            LEFT JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA 
                ON  SSPA.SSPA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                ON  MODE_ID_CAIXA_ENTRADA = CXEN.CXEN_ID_CAIXA_ENTRADA';
        //CLAUSULAS WHERE
        $query .= $app_Sosti_CaixasQuerys->where();
        $query .= $app_Sosti_CaixasQuerys->whereUltimoServico(false);
        $query .= $app_Sosti_CaixasQuerys->whereUltimaMovimentacao();
        $query .= $app_Sosti_CaixasQuerys->whereUltimaMovimentacao();
        $query .= '
            AND SSPA.SSPA_DT_PRAZO =   (SELECT SSPA_1.SSPA_DT_PRAZO 
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                                        WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                        AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                        AND SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)
            AND MOFA_ID_FASE NOT IN(1000,1014)
            AND SSPA_IC_CONFIRMACAO IS NULL
        
            /*TIPO DOCUMENTO SOLICITAÇÃO*/
            AND DOCM.DOCM_ID_TIPO_DOC = 160
            AND FADM.FADM_ID_FASE <> 1026
            AND SSPA_DT_PRAZO IS NOT NULL
            AND MODE_ID_CAIXA_ENTRADA IN (' . $idsCaixa . '))
            GROUP BY MODE_ID_CAIXA_ENTRADA,CXEN_DS_CAIXA_ENTRADA';

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $qtdExtensaoPrazo = $db->fetchAll($query);

        return $qtdExtensaoPrazo;
    }

    public function getCaixaSemNivelControleSLAQualidade ($idCaixa, $params, $order) {

        switch ($order) {
            case 'MOVI_DH_ENCAMINHAMENTO ASC':
                $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'MOVI_DH_ENCAMINHAMENTO DESC':
                $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            case 'DATA_AVALIACAO ASC':
                $order = "TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') ASC";
                break;
            case 'DATA_AVALIACAO DESC':
                $order = "TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') DESC";
                break;
            default:
                break;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT * FROM ( 
                   SELECT  SSOL_ID_DOCUMENTO,
                                DOCM_NR_DOCUMENTO, 
                                MOFA_ID_FASE,
                                DOCM_CD_MATRICULA_CADASTRO,
                                MOFA_ID_MOVIMENTACAO,
                                TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO, 
                                (SELECT SSER_1.SSER_DS_SERVICO
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                            INNER JOIN SOS_TB_SSER_SERVICO SSER_1
                                            ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE
                                                                                                    INNER JOIN SOS_TB_SSER_SERVICO SSER_2
                                                                                                    ON  SSES_2.SSES_ID_SERVICO       = SSER_2.SSER_ID_SERVICO                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) AS SSER_DS_SERVICO,
                                (SELECT SSER_1.SSER_ID_SERVICO
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                            INNER JOIN SOS_TB_SSER_SERVICO SSER_1
                                            ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE
                                                                                                    INNER JOIN SOS_TB_SSER_SERVICO SSER_2
                                                                                                    ON  SSES_2.SSES_ID_SERVICO       = SSER_2.SSER_ID_SERVICO                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) AS SSER_ID_SERVICO,
                                    ( SELECT STSA_1.STSA_DS_TIPO_SAT
                                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                   INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_1
                                                   ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SAVS_1.SAVS_ID_MOVIMENTACAO
                                                   AND MOFA_1.MOFA_DH_FASE          = SAVS_1.SAVS_DH_FASE
                                                   LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA_1
                                                   ON   SAVS_1.SAVS_ID_TIPO_SAT = STSA_1.STSA_ID_TIPO_SAT 
                                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                               FROM   SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                               ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                               ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                               ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                               ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                               INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_2
                                                                               ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SAVS_2.SAVS_ID_MOVIMENTACAO
                                                                               AND MOFA_2.MOFA_DH_FASE          = SAVS_2.SAVS_DH_FASE
                                                                                WHERE    DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO) 
                                            ) AS STSA_DS_TIPO_SAT
                                            ,
                                            DECODE(
                                            (SELECT COUNT(MOFA_1.MOFA_ID_FASE)
                                                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                               ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                               ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                               ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1 
                                                                               ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO                                                                                      
                                                                           WHERE    DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                           AND      MOFA_1.MOFA_ID_FASE = 1019
                                                                           AND      MODE_MOVI_1.MODE_ID_CAIXA_ENTRADA = $idCaixa )
                                                                           ,0, '0',
                                                                           '1') RECUSADO
                                            ,
                                            (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE, 'DD/MM/YYYY HH24:MI:SS') 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                        ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                        ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                        WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                        AND   MOFA_2.MOFA_ID_FASE = 1014 )) DATA_AVALIACAO,
                                            (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE, 'DD/MM/YYYY HH24:MI:SS') 
                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                                    FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                                                            ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                                                            ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                                                            WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                                                            AND   MOFA_2.MOFA_ID_FASE = 1000 )) DATA_BAIXA,
                                            (SELECT MOFA_1.MOFA_CD_MATRICULA
                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                                    FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                                                            ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                                                            ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                                                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                                                            ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                                                            WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                                                            AND   MOFA_2.MOFA_ID_FASE = 1000 )) MATRICULA_BAIXA,                                                                                                                                                   
        DOCM_DH_CADASTRO,
        DOCM_SG_SECAO_GERADORA,
        DOCM_CD_LOTACAO_GERADORA
              FROM           
        -- solicitacao    
        SOS_TB_SSOL_SOLICITACAO SSOL

        -- documento
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        -- movimentacao origem
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

        -- movimentacao destino
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        --fase
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
        
        
            WHERE  
         --Foi avaliado
        '1014'  = (SELECT MOFA_1.MOFA_ID_FASE 
                      FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                      WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                      FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                             INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                             ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                             INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                             ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                             INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                             ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                             INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                             ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                             WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1014 ))
        
        --Movimentações
            AND MOFA.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                        FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO)
        
            AND DOCM.DOCM_ID_TIPO_DOC = 160 
            AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa 
        ) 
        LEFT JOIN SOS_TB_MDSI_MOVIM_DEF_SISTEMA
        ON MDSI_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
        --Não listar os chamandos fechados
        LEFT JOIN SOS_TB_FEMV_FECHAMENTO_MOVIMEN
        ON FEMV_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
        AND FEMV_ID_INDICADOR = 23 -- INDICADOR DE QUALIDADE
        WHERE 1 = 1
        AND RECUSADO = 1
        AND FEMV_ID_MOVIMENTACAO IS NULL
        ";

        /* Data de avaliação */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY  HH24:MI:SS') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY  HH24:MI:SS') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY  HH24:MI:SS') ") : ("");

        /* Data de baixa */
        (($params['DATA_INICIAL_BAIXA'] == "") && ($params['DATA_FINAL_BAIXA'] != "")) ? ($stmt .= "AND TO_DATE(DATA_BAIXA, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_FINAL_BAIXA'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL_BAIXA'] . "', 'DD/MM/YYYY  HH24:MI:SS')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_BAIXA'] != "") && ($params['DATA_FINAL_BAIXA'] == "")) ? ($stmt .= "AND TO_DATE(DATA_BAIXA, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL_BAIXA'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_INICIAL_BAIXA'] . "', 'DD/MM/YYYY  HH24:MI:SS')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_BAIXA'] != "") && ($params['DATA_FINAL_BAIXA'] != "")) ? ($stmt .= "AND TO_DATE(DATA_BAIXA, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $params['DATA_INICIAL_BAIXA'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $params['DATA_FINAL_BAIXA'] . "', 'DD/MM/YYYY  HH24:MI:SS')+1-1/24/60/60 ") : ("");

        /* Defeito */
        $stmt .= ( $params['VERIFICADAS'] ) ? ('') : (' AND MDSI_ID_MOVIMENTACAO IS NULL ');

        /* Unidade solicitante */
        $stmt .= ( $params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $stmt .= ( $params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " ") : ('');

        /* Solicitante */
        $stmt .= ( $params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' ") : ('');

        /* Serviço */
        if (is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $stmt .= ( $params['SSER_DS_SERVICO']) ? (" AND SSER_DS_SERVICO LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Número da solicitação */
        $stmt .= ( $params['DOCM_NR_DOCUMENTO'] ) ? (" AND DOCM_NR_DOCUMENTO = " . $params['DOCM_NR_DOCUMENTO'] . " ") : ('');

        $stmt .= "ORDER BY $order";

        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    /**
     * Volume de ordens de serviço executadas nos prazos acordados
     * @param int $idCaixa
     * @param string $data_inicial
     * @param string $data_final
     * @param int $indicadorEPA
     * @return type 
     */
    public function getDatasSLA_EPA ($idCaixa, $idsSolics, $intervaloData, $indicadorEPA, $indicadorMTA) {
        $clausulaInDocm = null;
        if (!is_null($idsSolics)) {
            $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
            $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($idsSolics, 'SOS_TB_SSOL_SOLICITACAO', 'SSOL_ID_DOCUMENTO', ',');
        }
//        Zend_Debug::dump($idCaixa);
//        Zend_Debug::dump($idsSolics);
//        Zend_Debug::dump($intervaloData);
//        Zend_Debug::dump($indicadorEPA);
//        Zend_Debug::dump($indicadorMTA);
//        exit;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM ( 
                SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    SSER_DS_SERVICO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    COM_PEDIDO,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_EPA,
                    DESCONSIDERADO_MTA,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_INICIO_ATENDIMENTO = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_INICIO_ATENDIMENTO
                    ,
                    
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_SOL_PROBLEMA = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_SOL_PROBLEMA
                    ,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_SOL_CAUSA_PROBLEMA = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_SOL_CAUSA_PROBLEMA
                    ,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_EXECUCAO_SERVICO = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_EXECUCAO_SERVICO,
                    SUBSTR(SERVICO_SISTEMA,1,1) AS EMERGENCIA,
                    SUBSTR(SERVICO_SISTEMA,3,1) AS PROBLEMA,
                    SUBSTR(SERVICO_SISTEMA,5,1) AS CAUSA,
                    SERVICO_SISTEMA,
                    CASE  
                        WHEN   (CORRETIVA IS NULL )
                          THEN 'N'  
                        ELSE 'S'  
                    END AS CORRETIVA,
                    SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL_ID_DOCUMENTO) PRINCIPAL_OU_ORF
                    
                FROM (
                SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    SSER.SSER_DS_SERVICO,
                    DOCM_DH_CADASTRO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DO ENCAMINHAMENTO PARA OUTRO GRUPO
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                 FROM  SAD_TB_DOCM_DOCUMENTO DOCM
                                 INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                 ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                 INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                 ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                 WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                 AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                 AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                 AND    MOFA.MOFA_ID_MOVIMENTACAO > F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                    --FOI RECUSADA NÃO CONTA O ENCAMINHAMENTO COMO FIM DO CHAMADO FICA SENDO A DATA DA BAIXA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                    AND   FF.MOFA_ID_FASE = 1019                              
                   ) AS RECUSADA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                    AND   FF.MOFA_ID_FASE = 1024                              
                   ) AS COM_PEDIDO
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorEPA
                    ) AS DESCONSIDERADO_EPA,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorMTA
                    ) AS DESCONSIDERADO_MTA,
                                        (
                    SELECT ASSO_IC_ATENDIMENTO_EMERGENCIA||'|'||ASSO_IC_SOLUCAO_PROBLEMA||'|'||ASSO_IC_SOLUCAO_CAUSA_PROBLEMA
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    )SERVICO_SISTEMA,
                    (SELECT ASIS_ID_OCORRENCIA 
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    WHERE ASIS_ID_CATEGORIA_SERVICO = 2
                    AND   MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) CORRETIVA

                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO   
                      LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
                        ON  F.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
                        AND  F.MOFA_DH_FASE  = SSES.SSES_DH_FASE
                     LEFT JOIN SOS_TB_SSER_SERVICO SSER
                        ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO


                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                                                         
                      AND F.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                        AND DOCM_1.DOCM_ID_TIPO_DOC = 160
                                        AND MODE_MOVI_1.MODE_ID_CAIXA_ENTRADA = $idCaixa)

                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND SERVICO_SISTEMA IS NOT NULL
                    AND RECUSADA IS NULL ";
        if (!is_null($clausulaInDocm)) {
            $q .= " AND $clausulaInDocm ";
        } else if (!is_null($intervaloData)) {
            $q .= " AND TO_DATE(DATA_CHAMADO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $intervaloData['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $intervaloData['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI:SS') ";
        } 
//        else {
//            throw new Exception('Valores nulos para a funçao.');
//        }
        $q .= " 
                    UNION
                     SELECT 
                    SSOL_ID_DOCUMENTO, 
                    MOFA_ID_MOVIMENTACAO, 
                    MOFA_DS_COMPLEMENTO,
                    DOCM_NR_DOCUMENTO,
                    SSER_DS_SERVICO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                    DATA_CHAMADO,
                    DATA_FIM_CHAMADO,
                    RECUSADA,
                    COM_PEDIDO,
                    SSPA_DT_PRAZO
                    ,
                    CASE  
                        WHEN ( TO_DATE(DATA_FIM_CHAMADO,'DD/MM/YYYY HH24:MI:SS') > TO_DATE(SSPA_DT_PRAZO,'DD/MM/YYYY HH24:MI:SS') )  
                          THEN 1  
                        ELSE 0  
                    END AS PRAZO_ULTRAPASSADO
                    ,
                    DESCONSIDERADO_EPA,
                    DESCONSIDERADO_MTA
                    ,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_INICIO_ATENDIMENTO = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_INICIO_ATENDIMENTO
                    ,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_SOL_PROBLEMA = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_SOL_PROBLEMA
                    ,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_SOL_CAUSA_PROBLEMA = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_SOL_CAUSA_PROBLEMA
                    ,
                    (
                    SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    INNER JOIN SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
                    ON ASIS.ASIS_PRZ_EXECUCAO_SERVICO = PRAT.PRAT_ID_PRAZO_ATENDIMENTO
                    INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
                    ON UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) AS ASIS_PRZ_EXECUCAO_SERVICO,
                    SUBSTR(SERVICO_SISTEMA,1,1) AS EMERGENCIA,
                    SUBSTR(SERVICO_SISTEMA,3,1) AS PROBLEMA,
                    SUBSTR(SERVICO_SISTEMA,5,1) AS CAUSA,
                    SERVICO_SISTEMA,
                    CASE  
                        WHEN   (CORRETIVA IS NULL )
                          THEN 'N'  
                        ELSE 'S'  
                    END AS CORRETIVA,
                    SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL_ID_DOCUMENTO) PRINCIPAL_OU_ORF
                     FROM 
                    (
                    SELECT 
                    A.SSOL_ID_DOCUMENTO,
                    F.MOFA_ID_MOVIMENTACAO, 
                    F.MOFA_DS_COMPLEMENTO,
                    B.DOCM_NR_DOCUMENTO,
                    SSER.SSER_DS_SERVICO,
                    DOCM_DH_CADASTRO,
                    --DATA ENCAMINHAMENTO PARA O GRUPO EM QUESTÃO
                    TO_CHAR(F.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS DATA_CHAMADO,
                    --DATA DA PRIMEIRA BAIXA
                    TO_CHAR((SELECT  MIN(MOFA.MOFA_DH_FASE)
                                FROM   SOS_TB_SSOL_SOLICITACAO SSOL
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON  SSOL.SSOL_ID_DOCUMENTO     = DOCM.DOCM_ID_DOCUMENTO
                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                WHERE    DOCM.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                AND    DOCM.DOCM_ID_TIPO_DOC = 160
                                AND    MOFA.MOFA_DH_FASE > F.MOFA_DH_FASE
                                AND    MOFA.MOFA_ID_FASE = 1000
                                AND    MOFA.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                   ),'DD/MM/YYYY HH24:MI:SS') AS DATA_FIM_CHAMADO
                   ,
                   NULL AS RECUSADA
                   ,
                   (
                    SELECT MAX(FF.MOFA_DH_FASE)
                    FROM  SAD_TB_MOFA_MOVI_FASE FF
                    WHERE FF.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO 
                    AND   FF.MOFA_ID_FASE = 1024                              
                   ) AS COM_PEDIDO
                   ,
                   TO_CHAR((
                            SELECT SSPA_1.SSPA_DT_PRAZO 
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                            AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                            FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                            INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                            AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO)
                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND (SSPA_1.SSPA_IC_CONFIRMACAO = 'S' OR SSPA_1.SSPA_IC_CONFIRMACAO IS NULL)    
                   ),'DD/MM/YYYY HH24:MI:SS') AS SSPA_DT_PRAZO
                   ,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorEPA
                    ) AS DESCONSIDERADO_EPA,
                   (
                    SELECT DSIN_ID_MOVIMENTACAO
                    FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                    WHERE DSIN_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                    AND DSIN_ID_INDICADOR = $indicadorMTA
                    ) AS DESCONSIDERADO_MTA,
                    (
                    SELECT ASSO_IC_ATENDIMENTO_EMERGENCIA||'|'||ASSO_IC_SOLUCAO_PROBLEMA||'|'||ASSO_IC_SOLUCAO_CAUSA_PROBLEMA
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    WHERE  MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    )SERVICO_SISTEMA,
                    (SELECT ASIS_ID_OCORRENCIA 
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    WHERE ASIS_ID_CATEGORIA_SERVICO = 2
                    AND   MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) CORRETIVA
                      FROM   SOS_TB_SSOL_SOLICITACAO A
                      INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                      ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                      ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                      ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                      ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                      INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                      ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO                     
                        LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
                        ON  F.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
                        AND  F.MOFA_DH_FASE  = SSES.SSES_DH_FASE
                     LEFT JOIN SOS_TB_SSER_SERVICO SSER
                        ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO
                      WHERE  E.MODE_ID_CAIXA_ENTRADA = $idCaixa
                      AND B.DOCM_ID_TIPO_DOC = 160
                      AND  F.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                                         FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                         WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO)
                                                         
                      AND F.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                                        AND DOCM_1.DOCM_ID_TIPO_DOC = 160
                                        AND MODE_MOVI_1.MODE_ID_CAIXA_ENTRADA = $idCaixa)
                    ) 
                    WHERE DATA_FIM_CHAMADO IS NOT NULL
                    AND RECUSADA IS NULL
                    AND SERVICO_SISTEMA IS NOT NULL ";
        if (!is_null($clausulaInDocm)) {
            $q .= " AND $clausulaInDocm ";
        } else if (!is_null($intervaloData)) {
            $q .= " AND TO_DATE(DATA_CHAMADO, 'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE('" . $intervaloData['DATA_INICIAL'] . "', 'DD/MM/YYYY HH24:MI:SS') AND TO_DATE('" . $intervaloData['DATA_FINAL'] . "', 'DD/MM/YYYY HH24:MI:SS') ";
        } 
//        else {
//            throw new Exception('Valores nulos para a funçao.');
//        }
        $q .= " 
                    )
                    ORDER BY TO_DATE(DATA_CHAMADO,'DD/MM/YYYY HH24:MI:SS') ASC ";
//        Zend_Debug::dump($q);exit;
        return $db->query($q)->fetchAll();
    }

    /**
     * Índice de defeito (qualidade)(IDQ)
     * @param type $idCaixa
     * @param type $data_inicial
     * @param type $data_final
     * @return type 
     */
    public function getDatasSLA_IDQ ($idCaixa, $idsSolics, $indicadorIDQ) {
        $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
        $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($idsSolics, 'SOS_TB_SSOL_SOLICITACAO', 'SSOL_ID_DOCUMENTO', ',');
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT * FROM ( 
                   SELECT  SSOL_ID_DOCUMENTO,
                                DOCM_NR_DOCUMENTO,
                                TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                                MOFA_ID_FASE,
                                MOFA_ID_MOVIMENTACAO,
                                TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO, 

                                            (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE , 'DD/MM/YYYY HH24:MI:SS') 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                                FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                        ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                        ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                        WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                        AND   MOFA_2.MOFA_ID_FASE = 1014 )) DATA_AVALIACAO
                                                                                        
                ,                                                                       
                (
                SELECT SUM(MDSI_NR_DEFEITOS) ERROS_SISTEMA
                FROM SOS_TB_MDSI_MOVIM_DEF_SISTEMA
                WHERE MDSI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND   MDSI_IC_CANCELAMENTO = 'N'
                ) AS ERROS_SISTEMA
                ,
                (
                SELECT DSIN_ID_MOVIMENTACAO
                FROM SOS_TB_DSIN_DESCONSIDERA_INDIC
                WHERE DSIN_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND DSIN_ID_INDICADOR = $indicadorIDQ
                ) AS DESCONSIDERADO_IDQ
              FROM           
        -- solicitacao    
        SOS_TB_SSOL_SOLICITACAO SSOL

        -- documento
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        -- movimentacao origem
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

        -- movimentacao destino
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        --fase
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
        
        
            WHERE  
        --Movimentações
            MOFA.MOFA_DH_FASE  = (SELECT MIN(MOFA_1.MOFA_DH_FASE) 
                                        FROM  SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  MOFA_1.MOFA_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO)
                                        
        AND MOFA.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                  FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                  INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                  ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                  INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                  ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                  INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                  ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                  WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                  AND DOCM_1.DOCM_ID_TIPO_DOC = 160
                  AND MODE_MOVI_1.MODE_ID_CAIXA_ENTRADA = $idCaixa)

            AND DOCM.DOCM_ID_TIPO_DOC = 160 
            AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa
        )
        WHERE    $clausulaInDocm
        ORDER BY TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') ASC,TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS')ASC, DOCM_NR_DOCUMENTO ASC";

        Zend_Debug::dump($stmt);
        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
        ;
    }

    /**
     * Retorna as fases de pedido e resposta de informação
     * @param int $idMov
     * @return array 
     */
    public function getDatasMovPedidoRespostaInfo ($idsMov) {
        $maxIds = 1000;
        $auxIdsArr = explode(',', $idsMov);
        $auxCount = count($auxIdsArr);
        if ($auxCount > $maxIds) {
            $auxDivisao = (int) floor($auxCount / $maxIds);
            $auxMod = $auxCount % $maxIds;
            $countSlice = 0;
            $arrMovis = array();
            $auxOffset = 0;
            for ($i = 1; $i <= $auxDivisao; $i++) {
                $arrMovis[$countSlice] = array_slice($auxIdsArr, $auxOffset, $maxIds);
                $auxOffset += $maxIds;
                $countSlice++;
            }
            $arrMovis[$countSlice] = array_slice($auxIdsArr, $auxDivisao * $maxIds, $auxMod);
            $strClausulaIn = '';
            $countAuxUnion = 0;
            foreach ($arrMovis as $arrIds) {
                $auxString = implode(',', $arrIds);
                $strClausulaIn .= "
                                    SELECT DISTINCT MOFA_ID_MOVIMENTACAO FROM SAD_TB_MOFA_MOVI_FASE WHERE MOFA_ID_MOVIMENTACAO IN($auxString)
                                   ";
                if ($countAuxUnion < count($arrMovis) - 1) {
                    $strClausulaIn.=" UNION ";
                }
                $countAuxUnion++;
            }
            $strClausulaIn = "AND MOFA_ID_MOVIMENTACAO IN (
                                                            $strClausulaIn
                                                           )";
        } else {
            $strClausulaIn = " AND MOFA_ID_MOVIMENTACAO IN($idsMov) ";
        }


        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT MOFA_SUB_1.* FROM 
            (
            SELECT MOFA_ID_MOVIMENTACAO,MOFA_ID_FASE, TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE,MOFA_DH_FASE MOFA_DH_FASE_DATE_TIPE,
            (SELECT MIN(MOFA_1.MOFA_DH_FASE)
            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
            WHERE MOFA_1.MOFA_ID_FASE = 1000
            AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)DATA_BAIXA      
            FROM SAD_TB_MOFA_MOVI_FASE MOFA
            WHERE MOFA.MOFA_ID_FASE IN (1024,1025)
            
            $strClausulaIn
            
            ) MOFA_SUB_1
            WHERE 
            MOFA_SUB_1.MOFA_DH_FASE_DATE_TIPE <  
            NVL(DATA_BAIXA,TO_DATE('01/01/9999 00:00:00','DD/MM/YYYY HH24:MI:SS'))
            ORDER BY MOFA_ID_MOVIMENTACAO ASC, MOFA_DH_FASE_DATE_TIPE ASC";

        return $db->query($q)->fetchAll();
    }
    
    public function getDefeitosSolicit ($idCaixa, $intervaloData) {
//        $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
//        $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($idsSolics, 'SOS_TB_SSOL_SOLICITACAO', 'SSOL_ID_DOCUMENTO', ',');
        $data_inicial = $intervaloData["DATA_INICIAL"];
        $data_final = $intervaloData["DATA_FINAL"];
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        Zend_Debug::dump($intervaloData["DATA_INICIAL"]);
//        Zend_Debug::dump($idCaixa.','. $intervaloData);exit;
        $q = "SELECT DISTINCT 

            --solicitaÃ§Ã£o sos_tb_ssol_solicitacao
            TASO_ID_TAREFA_SOLICIT,
            SSOL_ID_DOCUMENTO,
            SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

            --documento sad_tb_docm_documento
            DOCM_NR_DOCUMENTO, 
            DOCM_CD_MATRICULA_CADASTRO,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
            TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
            DOCM_DH_CADASTRO DH_CADASTRO,

            --fase sad_tb_mofa_movi_fase
            MOFA_ID_FASE,TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 
            MOFA_ID_MOVIMENTACAO, 
            TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
            TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
            TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA_CD_MATRICULA) NOME_USARIO_BAIXA,

            --movimentacao destino sad_tb_mode_movi_destinatario
            MODE_ID_CAIXA_ENTRADA, 
            MODE_SG_SECAO_UNID_DESTINO,
            MODE_CD_SECAO_UNID_DESTINO,

            --nivel fase sos_tb_snas_nivel_atend_solic
            SNAS_ID_NIVEL,

            --nivel sos_tb_snat_nivel_atendimento
            SNAT_CD_NIVEL,

            --servico sos_tb_sser_servico
            SSER_ID_SERVICO, 
            SSER_DS_SERVICO, 

            --espera sos_tb_sesp_solic_espera
            SESP_DH_LIMITE_ESP, 
            TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

            --avaliacao  sos_tb_stsa_tipo_satisfacao
            STSA_DS_TIPO_SAT,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME((SELECT MOFA_1.MOFA_CD_MATRICULA
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                        ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                        ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                        ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                        WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                        AND   MOFA_2.MOFA_ID_FASE = 1000 ))) NOME_USARIO_BAIXA

                    FROM

        -- solicitacao    
        SOS_TB_SSOL_SOLICITACAO SSOL

        -- documento
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        -- movimentacao origem
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

        -- movimentacao destino
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        --fase
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO

        --descricao fase
        INNER JOIN SAD_TB_FADM_FASE_ADM FADM
        ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE

        --servico

        LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_SSER_SERVICO SSER
        ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO 

        --nivel

        LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS
        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SNAS.SNAS_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
        ON  SNAT.SNAT_ID_NIVEL         =  SNAS.SNAS_ID_NIVEL

        --espera

        LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA SESP
        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SESP.SESP_ID_MOVIMENTACAO

        --grupo serviÃ§o e serviÃ§o    

         LEFT JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
         ON SGRS.SGRS_ID_GRUPO = SSER.SSER_ID_GRUPO 

        --avaliaÃ§Ã£o    

        LEFT JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS
        ON   MOFA.MOFA_ID_MOVIMENTACAO = SAVS.SAVS_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA
        ON   SAVS.SAVS_ID_TIPO_SAT = STSA.STSA_ID_TIPO_SAT 

        --defeitos
        INNER JOIN SOS_TB_TASO_TAREFA_SOLICIT
        ON TASO_ID_DOCUMENTO = SSOL_ID_DOCUMENTO

                    WHERE

        --Ãºltimo serviÃ§o                                                  
        (SSER.SSER_ID_SERVICO = (SELECT SSES_1.SSES_ID_SERVICO 
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                        INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                        ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                        AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) OR SSER.SSER_ID_SERVICO IS NULL)

            AND 

        --Ãºltimo nÃ­vel
        (SNAT.SNAT_ID_NIVEL = (SELECT SNAS_1.SNAS_ID_NIVEL 
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                        INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_1
                        ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SNAS_1.SNAS_ID_MOVIMENTACAO
                        AND MOFA_1.MOFA_DH_FASE          = SNAS_1.SNAS_DH_FASE
                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                    FROM SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                        INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_2
                                                        ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SNAS_2.SNAS_ID_MOVIMENTACAO
                                                        AND MOFA_2.MOFA_DH_FASE          = SNAS_2.SNAS_DH_FASE
                                                        WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)  OR SNAT.SNAT_ID_NIVEL IS NULL)

            AND 

        --Ãºltima espera
        (SESP.SESP_DH_LIMITE_ESP = ( SELECT SESP_1.SESP_DH_LIMITE_ESP 
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                    INNER JOIN SOS_TB_SESP_SOLIC_ESPERA SESP_1
                    ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SESP_1.SESP_ID_MOVIMENTACAO
                    AND MOFA_1.MOFA_DH_FASE          = SESP_1.SESP_DH_FASE
                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                    INNER JOIN SOS_TB_SESP_SOLIC_ESPERA SESP_2
                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SESP_2.SESP_ID_MOVIMENTACAO
                                                    AND MOFA_2.MOFA_DH_FASE          = SESP_2.SESP_DH_FASE
                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) OR SESP.SESP_DH_LIMITE_ESP IS NULL)                                                                                    

            AND 

        --Ãºltima avaliacao
        (SAVS.SAVS_ID_TIPO_SAT = ( SELECT SAVS_1.SAVS_ID_TIPO_SAT
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                       INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_1
                       ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SAVS_1.SAVS_ID_MOVIMENTACAO
                       AND MOFA_1.MOFA_DH_FASE          = SAVS_1.SAVS_DH_FASE
                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                       INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_2
                                                       ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SAVS_2.SAVS_ID_MOVIMENTACAO
                                                       AND MOFA_2.MOFA_DH_FASE          = SAVS_2.SAVS_DH_FASE
                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO )OR SAVS.SAVS_ID_TIPO_SAT IS NULL)                                                                                 

            AND 

            -- tipo documento solicitacao
            DOCM_ID_TIPO_DOC = 160                                                                               

            AND 

            -- contendo a Ãºltima fase no historico
            (MOFA.MOFA_DH_FASE, MOFA.MOFA_ID_MOVIMENTACAO)  =  (SELECT MAX(MOFA_1.MOFA_DH_FASE), MAX(MOFA_1.MOFA_ID_MOVIMENTACAO)
                                                                    FROM  SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1 
                                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO                                                                                      
                                                                    WHERE    DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                    AND      MOFA_1.MOFA_ID_FASE = 1014 )

         AND SGRS_ID_GRUPO = $idCaixa 
         AND TASO_IC_ACEITE_SOLICITANTE = 'N' ";
        $q .= ( $data_inicial && $data_final) ? (" AND MOFA_DH_FASE between TO_DATE( '$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $q .= ( ($data_inicial == "") && ($data_final != "")) ? (" AND MOFA_DH_FASE between TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $q .= ( ($data_inicial != "") && ($data_final == "")) ? (" AND MOFA_DH_FASE between TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $q .= " ORDER BY DOCM_DH_CADASTRO ASC,DOCM_NR_DOCUMENTO "; 
//        Zend_Debug::dump($q);
//        exit;
        $stmt = $db->query($q);
        return $stmt->fetchAll();
        ;
    }

}
