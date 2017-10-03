<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Sosti_Model_DbTable_SosTbSsolSolicitacao extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SSOL_SOLICITACAO';
    protected $_primary = array('SSOL_ID_DOCUMENTO');

    protected $_referenceMap = array(
        'SOS_TB_SSOL_OCS_TB_PMAT_FK' => array(
            'columns' => array('SSOL_CD_MATRICULA_ATENDENTE'),
            'refTableClass' => 'Guardiao_Model_DbTable_OcsTbPmatMatricula', 
            'refColumns' => array('PMAT_CD_MATRICULA')
        ),
        'SOS_TB_SSOL_SAD_TB_DOCM_FK' => array(
            'columns' => array('SSOL_ID_DOCUMENTO'),
            'refTableClass' => 'Sisad_Model_DbTable_SadTbDocmDocumento', 
            'refColumns' => array('DOCM_ID_DOCUMENTO')
        ),
        'SOS_TB_SSOL_SOS_TB_ABSL_FK' => array(
            'columns' => array('SSOL_ID_ATEND_BAIXA_SOLIC'),
            'refTableClass' => 'Sosti_Model_Dbtable_SosTbAbslAtendBaixaSolic', 
            'refColumns' => array('ABSL_ID_ATEND_BAIXA_SOLIC')
        ),
        'SOS_TB_SSOL_SOS_TB_STCA_FK' => array(
            'columns' => array('SSOL_ID_TIPO_CAD'),
            'refTableClass' => 'Sosti_Model_Dbtable_SosTbStcaTipoCadastro', 
            'refColumns' => array('STCA_ID_TIPO_CAD')
        ),
        'SOS_TB_SSOL_TOMBO_FK' => array(
            'columns' => array('SSOL_NR_TOMBO', 'SSOL_SG_TIPO_TOMBO'),
            'refTableClass' => 'Patrimonio_Model_DbTable_Tombo', 
            'refColumns' => array('TI_TOMBO', 'NU_TOMBO')
        ),
    );
}
