<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Array.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
/**
 * @see Zend_Paginator_Adapter_Interface
 */
require_once 'Zend/Paginator/Adapter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class App_Paginator_Adapter_Sql_Oracle implements Zend_Paginator_Adapter_Interface {

    /**
     * Array
     *
     * @var array
     */
    protected $_sql = null;
    /**
     * Item count
     *
     * @var integer
     */
    protected $_count = null;
    
    protected $_db = null;
    protected $_rowCount = null;
    
    /**
     * Constructor.
     *
     * @param string $sql
     */
    //public function __construct(array $array) {
    public function __construct($sql) {
        $this->_db = Zend_Db_Table::getDefaultAdapter();
        $this->_sql = $sql;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage) {
        
        $limit_sql = "";
        if(is_string($this->_sql)){
            
            $limit_sql = "SELECT z2.*
               FROM (
                   SELECT z1.*, ROWNUM AS \"zend_db_rownum\"
                   FROM (
                       " . $this->_sql . "
                   ) z1
               ) z2
               WHERE z2.\"zend_db_rownum\" BETWEEN " . ($offset+1) . " AND " . ($offset+$itemCountPerPage);
            
        }else if ($this->_sql instanceof Trf1_Sosti_Negocio_Caixas_Caixa) {
            
            $limit_sql .= $this->_sql->_Clausula_Select_topo;
            $limit_sql .= " FROM (";
            $limit_sql .= "SELECT z2.*
               FROM (
                   SELECT z1.*, ROWNUM AS \"zend_db_rownum\"
                   FROM (
                       " . $this->_sql->mountNucleoConsulta() . "
                   ) z1
               ) z2
               WHERE z2.\"zend_db_rownum\" BETWEEN " . ($offset+1) . " AND " . ($offset+$itemCountPerPage);
            $limit_sql .= " ) caixas_paginar_select";
            $limit_sql .= $this->_sql->_Clausula_Order_topo;
        }
        $stmt = $this->_db->query( $limit_sql);
        return $stmt->fetchAll();
    }

    /**
     * Returns the total number of rows in the array.
     *
     * @return integer
     */
    public function count() {
        if ($this->_rowCount === null) {
            $this->setRowCount(
                $this->getCountSelect()
            );
        }

        return $this->_rowCount;
    }
    
    public function getCountSelect()
    {
        if(is_string($this->_sql)){
            $sql = "SELECT COUNT(*) AS zend_paginator_row_count FROM($this->_sql)";
        }else if ($this->_sql instanceof Trf1_Sosti_Negocio_Caixas_Caixa) {
            $sql = $this->_sql->mountConsultaCaixaPreCount();
            $sql = "SELECT COUNT(*) AS zend_paginator_row_count FROM($sql)";
        }
        return $sql;
    }
    
    public function setRowCount($sql)
    {
        $rowCount = $this->_db->fetchOne($sql);
        $this->_rowCount = $rowCount;
    }

}