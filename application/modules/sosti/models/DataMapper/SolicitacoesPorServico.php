<?php

/**
 * Classe Mapper para a geração do Relatório de Solicitações por Serviço
 * 
 * @author Daniel Rodrigues <daniel.fernandes@trf1.jus.br>
 */
class Sosti_Model_DataMapper_SolicitacoesPorServico extends Zend_Db_Table_Abstract
{

    protected $_db;

    public function __construct() 
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    public function listAll($cdSecao, $sgSecao, $completaStatus, $dataInicio, $dataFim, $agrupador, $completaCategoria = null) 
    {
        $sql = " SELECT ";
        
        $sql .= (($sgSecao.$cdSecao == 'TR1784') || ($sgSecao.$cdSecao == 'TR1155') || ($sgSecao.$cdSecao == "TR1783"))?(" COUNT(DECODE(CTSS_ID_CATEGORIA_SERVICO,2,0)) CATEGORIA_SERVICO_CORRETIVA,
            COUNT(DECODE(CTSS_ID_CATEGORIA_SERVICO,8,7,6,5,4,3,1,0)) DEMAIS_CATEGORIAS, "):("");
        
        $sql .= " SSER.SSER_ID_SERVICO,
            SSER.SSER_DS_SERVICO,
            TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO, '$agrupador') MOVI_DH_ENCAMINHAMENTO,
            COUNT(TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO, '$agrupador')) QTD
            $completaCategoria
            --,SUM(DECODE(CTSS_ID_CATEGORIA_SERVICO,2,1,0)) CAT
            FROM
            SAD_TB_DOCM_DOCUMENTO DOCM
            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO
            ON DOCM.DOCM_ID_DOCUMENTO =  MODO.MODO_ID_DOCUMENTO

            INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI
            ON MODO.MODO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO

            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE1
            ON MOVI.MOVI_ID_MOVIMENTACAO = MODE1.MODE_ID_MOVIMENTACAO

            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
            ON MOVI.MOVI_ID_MOVIMENTACAO = SSES.SSES_ID_MOVIMENTACAO

            INNER JOIN SOS_TB_SSER_SERVICO SSER
            ON SSES.SSES_ID_SERVICO = SSER.SSER_ID_SERVICO

            INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
            ON SGRS.SGRS_ID_GRUPO = SSER.SSER_ID_GRUPO

            LEFT JOIN SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
            ON ASSO.ASSO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO

            LEFT JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
            ON ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS = ASIS.ASIS_ID_ATENDIMENTO_SISTEMA

            LEFT JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA CTSS
            ON ASIS.ASIS_ID_CATEGORIA_SERVICO = CTSS.CTSS_ID_CATEGORIA_SERVICO

        WHERE 
            MOVI.MOVI_DH_ENCAMINHAMENTO BETWEEN TO_DATE('$dataInicio','DD/MM/YYYY') AND TO_DATE('$dataFim','DD/MM/YYYY')
            AND DOCM.DOCM_ID_TIPO_DOC = 160
            AND MODE1.MODE_CD_SECAO_UNID_DESTINO = $cdSecao
            AND MODE1.MODE_SG_SECAO_UNID_DESTINO = '$sgSecao'
            --AND MODE1.MODE_ID_CAIXA_ENTRADA = 2
            AND MODO.MODO_ID_MOVIMENTACAO  = (SELECT MOFA_ID_MOVIMENTACAO 
                                              FROM SAD_TB_MOFA_MOVI_FASE 
                                              WHERE MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO
                                              $completaStatus
                                              AND MOFA_DH_FASE = (SELECT MAX(MOFA_DH_FASE) 
                                                                  FROM SAD_TB_MOFA_MOVI_FASE 
                                                                  WHERE MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO))
                                              AND MOVI.MOVI_DH_ENCAMINHAMENTO = (SELECT MAX(MOVI_DH_ENCAMINHAMENTO)
                                                                                 FROM SAD_TB_MODO_MOVI_DOCUMENTO, SAD_TB_MOVI_MOVIMENTACAO 
                                                                                 WHERE MODO_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO
                                                                                 AND MODO_ID_DOCUMENTO = MODO.MODO_ID_DOCUMENTO)
        GROUP BY ";
        $sql .= (($sgSecao.$cdSecao == 'TR1784') || ($sgSecao.$cdSecao == 'TR1155') || ($sgSecao.$cdSecao == "TR1783"))?("CTSS_ID_CATEGORIA_SERVICO, "):("");
        $sql .= " SSER_ID_SERVICO, SSER_DS_SERVICO, TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO, '$agrupador')
        ORDER BY 2,3 DESC  
        ";
        return $this->_db->query($sql)->fetchAll();
    }

    public function __destruct() {
        
    }

}