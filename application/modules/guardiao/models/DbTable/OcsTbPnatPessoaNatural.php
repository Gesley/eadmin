<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_DbTable_OcsTbPnatPessoaNatural extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PNAT_PESSOA_NATURAL';
    protected $_primary = 'PNAT_ID_PESSOA';

        protected $_referenceMap = array(
        'OCS_TB_PNAT_OCS_TB_PESS_FK' => array(
            'columns' => array('PNAT_ID_PESSOA'),
            'refTableClass' => 'Guardiao_Model_DbTable_OcsTbPessPessoa', 
            'refColumns' => array('PESS_ID_PESSOA')
        ),
        'OCS_TB_PNAT_PROCESSUAL_PLOC_FK' => array(
            'columns' => array('PNAT_CD_LOCAL_NASCIMENTO'),
            'refTableClass' => 'Guardiao_Model_DbTable_PLocalidade', 
            'refColumns' => array('LOCD_CD_LOCALIDADE')
        ),
    );
}
