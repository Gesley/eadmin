<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Sosti_Model_DbTable_SosTbLbkpBackup extends Zend_Db_Table_Abstract 
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LBKP_BACKUP';
    protected $_primary = 'LBKP_NR_TOMBO';
    protected $_sequence = 'SOS_SQ_LBKP';

    protected $_referenceMap = array(
        'SOS_TB_LBKP_OCS_TB_PMAT_FK_CAD' => array(
            'columns' => array('LBKP_CD_MATRICULA_CAD'),
            'refTableClass' => 'Guardiao_Model_DbTable_OcsTbPmatMatricula', 
            'refColumns' => array('PMAT_CD_MATRICULA')
        ),
        'SOS_TB_LBKP_OCS_TB_PMAT_FK_EXC' => array(
            'columns' => array('LBKP_CD_MATRICULA_EXC'),
            'refTableClass' => 'Guardiao_Model_DbTable_OcsTbPmatMatricula', 
            'refColumns' => array('PMAT_CD_MATRICULA')
        ),
        'SOS_TB_LBKP_TOMBO_TI_CENT_FK' => array(
            'columns' => 'LBKP_ID_TOMBO_TI_CENTRAL',
            'refTableClass' => 'Application_Model_DbTable_TomboTiCentral',
            'refColumns' => 'ID_TOMBO_TI_CENTRAL'
        ),
//        'SOS_TB_LBKP_RH_CENTRAL_LOT_FK' => array(
//            'columns' => array('LBKP_CD_LOTACAO', 'LBKP_SG_SECAO'),
//            'refTableClass' => 'Sarh_Model_DbTable_RhCentralLotacao',
//            'refColumns' => array('LOTA_COD_LOTACAO', 'LOTA_SIGLA_SECAO')
//        ),
//        'SOS_TB_LBKP_TOMBO_FK' => array(
//            'columns' => array('LBKP_NR_TOMBO', 'LBKP_SG_TOMBO'),
//            'refTableClass' => 'Patrimonio_Model_DbTable_Tombo',
//            'refColumns' => array('TI_TOMBO', 'NU_TOMBO')
//        ),
    );
}
