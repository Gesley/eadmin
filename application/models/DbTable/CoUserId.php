<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_CoUserId extends Zend_Db_Table_Abstract
{
    protected $_name = 'CO_USER_ID';
 
    public function getNomeBanco($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT COU_NM_BANCO, COU_COD_SECAO 
                            FROM   CO_USER_ID
                            WHERE  COU_COD_MATRICULA = '".$matricula."' 
                            AND COU_NM_BANCO NOT IN ('ILSJEFV', 'JFTRM', 'TRF3', 'JFDSV', 'TRF1DSV', 'JFHML')
                            AND    COU_ST_STATUS = 1
                            ORDER BY COU_COD_SECAO");
        $array = $stmt->fetchAll();
        $arrayDistinct = array();
        $i = 0;
        foreach ($array as $arr) {
            $arrayDistinct[$i] = $arr['COU_NM_BANCO'];
            $i++;
        }
        return array_unique($arrayDistinct);
    }
    
       public function getAssinatura($matricula, $senha){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) AS RESULTADO
                              FROM CO_USER_ID 
                             WHERE COU_ST_STATUS = 1 
                               AND COU_COD_MATRICULA = UPPER('$matricula')
                               AND (COU_COD_PASSWORD = '".md5($senha)."'
                                OR COU_COD_PASSWORD = '".md5(strtoupper($senha))."'
                                OR COU_COD_PASSWORD = '".md5(strtolower($senha))."')");
        
        $resultado = $stmt->fetch(); 
        return $resultado["RESULTADO"];
      
    }

}