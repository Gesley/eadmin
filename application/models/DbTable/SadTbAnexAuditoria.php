<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SadTbAnexAuditoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_ANEX_AUDITORIA';
    //a tabela nao possui chave mas tive que colocar por causa do erro
    //A table must have a primary key, but none was found 
    protected $_primary = 'ANEX_TS_OPERACAO';
}