<?php
class Application_Model_DbTable_Sisad_SadTbModpDestinoPessoa extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_MODP_DESTINO_PESSOA';
    protected $_primary = array ('MODP_ID_MOVIMENTACAO','MODP_SG_SECAO_UNID_DESTINO','MODP_CD_SECAO_UNID_DESTINO','MODP_CD_MAT_PESSOA_DESTINO');
}