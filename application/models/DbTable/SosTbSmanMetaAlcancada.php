<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSmanMetaAlcancada extends Zend_Db_Table_Abstract
{
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SMAN_META_ALCANCADA';
    protected $_primary = array('SINS_ID_INDICADOR, SMAN_ID_META');
    //protected $_sequence = '';
    //protected $_referenceMap = array();
    //protected $_dependentTables = array();
    //protected $_primary = ''; # caso a primary key esteja diferente

    //protected $_dependentTables = array('Application_Model_DbTable_Comment');

//    protected $_referenceMap = array (
//        array ('refTableClass'  => 'Application_Model_DbTable_Post',
//               'refColumns'     => 'ID',
//               'columns'        => 'EMAIL'));
}