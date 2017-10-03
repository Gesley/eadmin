<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbCateCategoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_CATE_CATEGORIA';
    protected $_primary = 'CATE_ID_CATEGORIA';
    protected $_sequence = 'SOS_SQ_CATE';

    
    public function setCategoria($cateCategoria){
        $cateCategoria["CATE_NO_CATEGORIA"] = new Zend_Db_Expr("UPPER('$cateCategoria[CATE_NO_CATEGORIA]')");
        $cateCategoria["CATE_DS_OBSERVACAO"] = new Zend_Db_Expr("UPPER('$cateCategoria[CATE_DS_OBSERVACAO]')");
        
        $sosCateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        
        $qtdLinhas = $sosCateCategoria->fetchAll()->count();
        $cateCategoria["CATE_ID_CATEGORIA"] = $qtdLinhas+1;
        
        $rowCategoria = $sosCateCategoria->createRow($cateCategoria);
        return $rowCategoria->save();
        
    }
    
    public function setAlterarCategoria($cateCategoria){
        $cateCategoria["CATE_NO_CATEGORIA"] = new Zend_Db_Expr("UPPER('$cateCategoria[CATE_NO_CATEGORIA]')");
        $cateCategoria["CATE_DS_OBSERVACAO"] = new Zend_Db_Expr("UPPER('$cateCategoria[CATE_DS_OBSERVACAO]')");
        
        $sosCateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        
        $rowCateCategoria = $sosCateCategoria->find($cateCategoria["CATE_ID_CATEGORIA"])->current();
        $rowCateCategoria->setFromArray($cateCategoria);
        return $rowCateCategoria->save();
    }
}