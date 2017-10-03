<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SadTbOrgjOrgaoJulgador
 *
 * @author TR17358PS
 */
class Application_Model_DbTable_SadTbOrgjOrgaoJulgador extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_ORGJ_ORGAO_JULGADOR';
    protected $_primary = 'ORGJ_CD_ORGAO_JULGADOR';
    
    public function getCodNomeOrgaoAjax($codNome)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     O.ORGJ_CD_ORGAO_JULGADOR||' - '||O.ORGJ_NM_ORGAO_JULGADOR  AS LABEL,
                                       O.ORGJ_CD_ORGAO_JULGADOR
                            FROM       SAD_TB_ORGJ_ORGAO_JULGADOR O
                            WHERE      UPPER(O.ORGJ_CD_ORGAO_JULGADOR||O.ORGJ_NM_ORGAO_JULGADOR) LIKE UPPER('%$codNome%')
                            OR         UPPER(O.ORGJ_CD_ORGAO_JULGADOR||' - '||O.ORGJ_NM_ORGAO_JULGADOR) LIKE UPPER('%$codNome%')
                            ORDER BY   O.ORGJ_NM_ORGAO_JULGADOR");
        return $stmt->fetchAll();
    }
    public function getOrgaosJulgadores($criterios = null)
    {
        switch ($criterios) {
            case '1':
                $completa = 'WHERE ORGJ_CD_ORGAO_JULGADOR IN (4000,7000)';
                break;
            default:
                $completa = '';
                break;
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT O.ORGJ_CD_ORGAO_JULGADOR,
                                   O.ORGJ_NM_ORGAO_JULGADOR, 
                                   O.ORGJ_CD_ORGAO_JULGADOR
                            FROM       SAD_TB_ORGJ_ORGAO_JULGADOR O
                            $completa
                            ORDER BY   O.ORGJ_NM_ORGAO_JULGADOR");
        return $stmt->fetchAll();
    }
}

?>
