<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbUtrqUtilizadorReq extends Zend_Db_Table_Abstract
{
    //protected $_schema = 'trf1dsv';
    protected $_name = 'TRA_TB_UTRQ_UTILIZADOR_REQ';
    protected $_primary = 'UTRQ_ID_UTILIZADOR';
    protected $_sequence = 'TRA_SQ_UTRQ_ID_UTILIZADOR';
    //protected $_referenceMap = array();
    //protected $_dependentTables = array();
    //protected $_primary = ''; # caso a primary key esteja diferente

    //protected $_dependentTables = array('Application_Model_DbTable_Comment');

//    protected $_referenceMap = array (
//        array ('refTableClass'  => 'Application_Model_DbTable_Post',
//               'refColumns'     => 'ID',
//               'columns'        => 'EMAIL'));

}