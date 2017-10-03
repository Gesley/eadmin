<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbGrupVeiculo extends Zend_Db_Table_Abstract
{
    //protected $_schema = 'trf1dsv';
    protected $_name = 'TRA_TB_GRUP_VEICULO';
    protected $_primary = 'GRUP_ID_GRUPO';
    protected $_sequence = 'TRA_SQ_GRUP_ID_GRUPO';
    //protected $_referenceMap = array();
    //protected $_dependentTables = array();
    //protected $_primary = ''; # caso a primary key esteja diferente

    //protected $_dependentTables = array('Application_Model_DbTable_Comment');

//    protected $_referenceMap = array (
//        array ('refTableClass'  => 'Application_Model_DbTable_Post',
//               'refColumns'     => 'ID',
//               'columns'        => 'EMAIL'));

}