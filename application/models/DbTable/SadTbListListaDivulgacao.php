<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SadTbListListaDivulgacao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_LIST_LISTA_DIVULGACAO';
    protected $_primary = 'LIST_ID_LISTA_DIVULGACAO';
    protected $_sequence = 'SAD_SQ_LIST';


    public function getListasById()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($id);
        $stmt = $db->query("SELECT GRDV_ID_GRUPO_DIVULGACAO,
                                     GRDV_DS_GRUPO_DIVULGACAO,
                                     GRDV_IC_ATIVO
                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
                            WHERE LIST_ID_LISTA_DIVULGACAO = $id");
        return $stmt->fetchAll();
    }

}