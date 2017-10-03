<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbPecaPeca extends Zend_Db_Table_Abstract
{
    //protected $_schema = 'trf1dsv';
    protected $_name = 'TRA_TB_PECA_PECA';
    protected $_primary = 'PECA_ID_PECA';
    protected $_sequence = 'TRA_SQ_PECA_PECA';
    //protected $_referenceMap = array();
    //protected $_dependentTables = array();
    //protected $_primary = ''; # caso a primary key esteja diferente

    //protected $_dependentTables = array('Application_Model_DbTable_Comment');

//    protected $_referenceMap = array (
//        array ('refTableClass'  => 'Application_Model_DbTable_Post',
//               'refColumns'     => 'ID',
//               'columns'        => 'EMAIL'));

}