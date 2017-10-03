<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_DbTable_OcsTbPmatMatricula extends Zend_Db_Table_Abstract 
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PMAT_MATRICULA';
    protected $_primary = 'PMAT_CD_MATRICULA';

    protected $_referenceMap = array(
        'OCS_TB_PMAT_OCS_TB_PNAT_FK' => array(
            'columns' => array('PMAT_ID_PESSOA'),
            'refTableClass' => 'Sosti_Model_Dbtable_OcsTbPnatPessoaNatural', 
            'refColumns' => array('PNAT_ID_PESSOA')
        ),
        'OCS_TB_PMAT_OCS_TB_PTMA_FK' => array(
            'columns' => array('PMAT_ID_TIPO_MATRICULA'),
            'refTableClass' => 'Sosti_Model_Dbtable_OcsTbPtmaTipoMatricula', 
            'refColumns' => array('PTMA_ID_TIPO_MATRICULA')
        ),
        'OCS_TB_PMAT_RH_CENTRAL_LOTA_FK' => array(
            'columns' => array('PMAT_SG_SECSUBSEC_LOTACAO', 'PMAT_CD_UNIDADE_LOTACAO'),
            'refTableClass' => 'Sarh_Model_DbTable_RhCentralLotacao', 
            'refColumns' => array('LOTA_SIGLA_SECAO', 'LOTA_COD_LOTACAO')
        ),
    );
}
