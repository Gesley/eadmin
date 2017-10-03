<?php

/**
 * To change this template, choose Tools | Templates
 * and open the templat e in the editor.
 * 
 */
class Application_Model_DbTable_SosTbCtssCategServSistema extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_CTSS_CATEG_SERV_SISTEMA';
    protected $_primary = 'CTSS_ID_CATEGORIA_SERVICO';
    protected $_sequence = 'SOS_SQ_CTSS';

    public function getCategoriaServicoByIdOcorrencia($id_ocorrencia) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($id);
        $stmt = $db->query("SELECT DISTINCT (ASIS_ID_CORRENCIA)||'-'||ASIS_ID_CATEGORIA_SERVICO AS OCO_CTSS,
                                   CTSS_NM_CATEGORIA_SERVICO
                           FROM SOS_TB_ASIS_ATEND_SISTEMA
                           INNER JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA
                           ON ASIS_ID_CATEGORIA_SERVICO = CTSS_ID_CATEGORIA_SERVICO
                           WHERE ASIS_ID_CORRENCIA = $id_ocorrencia");
        return $stmt->fetchAll();
    }

    public function getNivelCriticidadeByCtss($id_ocorrencia,$id_categoria_servico) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($id);
        $stmt = $db->query("SELECT DISTINCT (ASIS_IC_NIVEL_CRITICIDADE)
                            FROM SOS_TB_ASIS_ATEND_SISTEMA
                            INNER JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA
                            ON ASIS_ID_CATEGORIA_SERVICO = CTSS_ID_CATEGORIA_SERVICO
                            WHERE ASIS_ID_CORRENCIA = $id_ocorrencia
                            AND ASIS_ID_CATEGORIA_SERVICO = $id_categoria_servico");
        return $stmt->fetchAll();
    }

}