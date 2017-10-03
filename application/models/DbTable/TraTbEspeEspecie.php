<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbEspeEspecie extends Zend_Db_Table_Abstract
{
    //protected $_schema = 'trf1dsv';
    protected $_name = 'TRA_TB_ESPE_ESPECIE';
    protected $_primary = 'ESPE_ID_ESPECIE';
    protected $_sequence = 'TRA_SQ_ESPE_ID_ESPECIE';
    //protected $_referenceMap = array();
    //protected $_dependentTables = array();
    //protected $_primary = ''; # caso a primary key esteja diferente

    //protected $_dependentTables = array('Application_Model_DbTable_Comment');

//    protected $_referenceMap = array (
//        array ('refTableClass'  => 'Application_Model_DbTable_Post',
//               'refColumns'     => 'ID',
//               'columns'        => 'EMAIL'));

}