<?php
/**
 * Validações das OS
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Os_Model_DataMapper_Atendente extends Zend_Db_Table_Abstract
{

    public function __construct() 
    {
    }
    
    public function fetchPairs($idCaixa)
    {
        $atendentes = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        foreach ($atendentes->getAtendentesCaixa($idCaixa, null, null) as $a) {
            if ($a["IC_ATIVIDADE"] != "NÃO") {
                $matriculaNome = explode(' - ', $a['ATENDENTE']);
                $arrayFetchPairs[$a['ATCX_CD_MATRICULA']] = $matriculaNome[1];
            }
       }
       return $arrayFetchPairs;
    }
}