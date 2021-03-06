<?php
class Application_Model_DbTable_Parte extends Zend_Db_Table_Abstract
{
    protected $_name = 'PARTE';
    protected $_primary = 'PARTE_PROC_ID';

    public function getPartesProcesso($parte_proc_id){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("          	SELECT PARTE_TIP,
                                                DECODE(PARTE_COD_ENT,NULL,PARTE_NOME,ENT_DESC)||
                                                DECODE(PARTE_COD_ADVOG,NULL,NULL,ADVOG_NOME)||' '||
                                                DECODE(PARTE_CARAC,NULL,NULL,CARAC_DESC) PARTE, CIDADE, UF, ENT_CID, ENT_UF
                                      FROM   ADVOGADO, GRUPO, ENTIDADE, CARACTERISTICA_PARTE, PARTE, PROCESSO
                                      WHERE  PARTE_PROC_ID   = $parte_proc_id
                                      AND    PARTE_PROC_ID   = PROC_ID
                                      AND    PARTE_COD_ADVOG = ADVOG_COD(+)
                                      AND    PARTE_COD_ENT   = ENT_COD(+)
                                      AND    PARTE_CARAC     = CARAC_COD(+)
                                      AND    PROC_COD_GRUPO  = GRUPO_COD(+)
                                      ORDER BY PARTE_SEQ   ");
        return $stmt->fetchAll();
    }
}