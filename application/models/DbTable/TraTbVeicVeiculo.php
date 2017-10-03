<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbVeicVeiculo extends Zend_Db_Table_Abstract
{
    protected $_name = 'TRA_TB_VEIC_VEICULO';

    protected $_primary = array(1 => 'VEIC_ID_VEICULO');

    protected $_dependentTables = array(
        'TRA_TB_ACID_ACIDENTE',
        'TRA_TB_DEAV_DESP_ADM_VEIC',
        'TRA_TB_MULT_MULTA',
        'TRA_TB_REQU_REQUISICAO',
        'TRA_TB_SIVE_SITUACAO_VEICULO',
        'TRA_TB_VERE_VEICULO_RESERVA',
        'TRA_TB_VERE_VEICULO_RESERVA'
        );

    public function getVeiculo($order)
    {
        if ( !isset($order) ) {
            $order = 'MODE_DS_MODELO';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT veic_cd_placa, MODE_DS_MODELO, MARC_DS_MARCA, veic_nr_renavam, veic_nr_tombo
                              FROM TRA_TB_VEIC_VEICULO, OCS_TB_MARC_MARCA, OCS_TB_MODE_MODELO
                             WHERE VEIC_CD_MARCA = MARC_ID_MARCA
                               AND VEIC_CD_MODELO = MODE_ID_MODELO
                             ORDER BY $order");
        return $stmt->fetchAll();
    }    
    
}