<?php
class Application_Model_DbTable_Sisad_SadTbModeMoviDestinatario extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_MODE_MOVI_DESTINATARIO';
    protected $_primary = array('MODE_ID_MOVIMENTACAO', 'MODE_SG_SECAO_UNID_DESTINO', 'MODE_CD_SECAO_UNID_DESTINO') ;
}