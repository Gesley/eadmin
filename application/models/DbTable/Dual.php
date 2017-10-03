<?php

class Application_Model_DbTable_Dual extends Zend_Db_Table_Abstract
{
    protected $_name = 'DUAL';

    public function sysdate()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATAHORA"];
        $datahora2 = new Zend_Db_Expr("TO_DATE('$datahora','dd/mm/yyyy HH24:MI:SS')");
        return $datahora2;
    }
    
    public function setEspera()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_DATE(SYSDATE,'dd/mm/yyyy')+1||' '||'08:00:00' LIMITE FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["LIMITE"];
        $datahora2 = new Zend_Db_Expr("TO_DATE('$datahora','dd/mm/yyyy HH24:MI:SS')");
        return $datahora2;
    }
    
    public function sysdateDb()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy') DATA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATA"];
        return $datahora;
    }
    
	   public function sysdatehoraDb()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS')DATA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
		$datahora = $datahora_aux[0]["DATA"];
        return $datahora;
    }
	
    public function localtimestampDb()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOCALTIMESTAMP AS DATA FROM DUAL");
        return  $stmt->fetch();
    }
    
    public function verificaPrazo($data)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query
        (
        "
            SELECT 
            CASE 
                WHEN ( (TO_DATE('$data','DD/MM/YYYY HH24:MI:SS') - SYSDATE) >= 0 ) THEN
                    'NO_PRAZO'
                ELSE
                    'FORA_DO_PRAZO'
                END AS VERIFICA_PRAZO
            FROM DUAL
        "
        );
        return $stmt->fetch();
    }
    
    public function sysdateAddMonths($meses)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(ADD_MONTHS(SYSDATE,'$meses'),'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATAHORA"];
        return $datahora;
    }
    
        public function sysdateDbFirstDay()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT '01/'||SUBSTR(TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS'),4) DATA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATA"];
        return $datahora;
    }
    
    public function sysDataHoraDb()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATAHORA"];
        return $datahora;
    }
}
