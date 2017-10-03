<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SosTbLtpuTipoUsuario extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LTPU_TIPO_USUARIO';
    protected $_primary = 'LTPU_ID_TP_USUARIO';
    protected $_sequence = 'SOS_SQ_LTPU';
    
    public function getTipoUsuario($order)
    {
        if ( !isset($order) ) {
            $order = 'LTPU_ID_TP_USUARIO ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LTPU_ID_TP_USUARIO, LTPU_DS_TP_USUARIO
                              FROM SOS_TB_LTPU_TIPO_USUARIO
                                ORDER BY $order");
        $listTipousuario = $stmt->fetchAll();
        return $listTipousuario;
    }
    
    public function getEditarTpUsuario($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LTPU_ID_TP_USUARIO, LTPU_DS_TP_USUARIO
                              FROM SOS_TB_LTPU_TIPO_USUARIO
                             WHERE LTPU_ID_TP_USUARIO = $id");
        $editTipousuario = $stmt->fetch();
        return $editTipousuario;
    }
    
}