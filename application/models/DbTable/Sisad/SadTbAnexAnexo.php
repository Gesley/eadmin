<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_Sisad_SadTbAnexAnexo extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_ANEX_ANEXO';
    protected $_primary = array('ANEX_ID_DOCUMENTO','ANEX_NR_DOCUMENTO_INTERNO');


}