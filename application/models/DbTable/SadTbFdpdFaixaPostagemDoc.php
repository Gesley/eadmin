<?php
class Application_Model_DbTable_SadTbFdpdFaixaPostagemDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = array('SAD_TB_FPDP_FAIXA_POSTAGEM_DOC',
                             'FPDP_ID_FAIXA_POSTAGEM',
                             'FPDP_NR_NUMERO_INICIAL',
                             'FPDP_NR_NUMERO_FINAL');    
}