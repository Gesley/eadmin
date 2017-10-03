<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Sosti_Model_DbTable_SosTbPrdePriorizaDemanda extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_PRDE_PRIORIZA_DEMANDA';
    protected $_primary = array('PRDE_ID_PRIORIZACAO');
    protected $_sequence = 'SOS_SQ_PRDE';

    protected $_referenceMap = array(
        'SOS_TB_PRDE_OCS_TB_PMAT_FK' => array(
            'columns' => array('PRDE_CD_MATR_PRIORIZACAO'),
            'refTableClass' => 'Guardiao_Model_DbTable_OcsTbPmatMatricula', 
            'refColumns' => array('PMAT_CD_MATRICULA')
        ),
        'SOS_TB_PRDE_SAD_TB_CXEN_FK' => array(
            'columns' => array('PRDE_ID_CAIXA_ENTRADA'),
            'refTableClass' => 'Sosti_Model_DbTable_SadTbCxenCaixaEntrada', 
            'refColumns' => array('CXEN_ID_CAIXA_ENTRADA')
        ),
        'SOS_TB_PRDE_SOS_TB_SSER_FK' => array(
            'columns' => array('PRDE_ID_SERVICO'),
            'refTableClass' => 'Sosti_Model_Dbtable_SosTbSserServico', 
            'refColumns' => array('SSER_ID_SERVICO')
        ),
        'SOS_TB_PRDE_SOS_TB_SSOL_FK' => array(
            'columns' => array('PRDE_ID_SOLICITACAO'),
            'refTableClass' => 'Sosti_Model_Dbtable_SosTbSsolSolicitacao', 
            'refColumns' => array('SSOL_ID_DOCUMENTO')
        ),
    );
}