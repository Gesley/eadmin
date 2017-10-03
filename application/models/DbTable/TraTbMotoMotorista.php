<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbMotoMotorista extends Zend_Db_Table_Abstract
{
    protected $_name = 'TRA_TB_MOTO_MOTORISTA';
    protected $_primary = 'MOTO_ID_MOTORISTA';
    protected $_sequence = 'TRA_SQ_MOTO_ID_MOTORISTA';

    public function getMotorista($order)
    {
        if ( !isset($order) ) {
            $order = 'COU_COD_NOME';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MOTO_CD_MATR_MOTORISTA, COU_COD_NOME, LOTA_SIGLA_LOTACAO 
                              FROM TRA_TB_MOTO_MOTORISTA, CO_USER_ID, OCS_TB_PMAT_MATRICULA, RH_LOTACAO
                             WHERE MOTO_CD_MATR_MOTORISTA = COU_COD_MATRICULA
                               AND PMAT_CD_MATRICULA = MOTO_CD_MATR_MOTORISTA
                               AND LOTA_COD_LOTACAO = PMAT_CD_UNIDADE_LOTACAO
                             ORDER BY $order");
        return $stmt->fetchAll();
    }
   

}