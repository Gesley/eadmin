<?php

class Application_Model_DbTable_ProcessoFase extends Zend_Db_Table_Abstract {

    protected $_name = 'PROCESSO_FASE';
    protected $_primary = 'PFASE_PROC_ID';

    public function getPartesProcesso($proc_id, $cod_org, $nu_oficio, $parte, $baixa_digital) {
        /*
         * -=FEITO POR MAURÍCIO E IMPLEMENTADO POR SCHMACHER 03/08/2012=-
         * 
         * PARAMETROS DE ENTRADA
         */
        $V_PROCID_P = $proc_id;
        $V_COD_ORGAO = $cod_org;
        $V_NU_OFICIO = $nu_oficio;
        $V_PARTE = $parte;
        $V_BAI_DIGI = $baixa_digital;
        /*
         * QUERY - EXECUÇÃO
         */
        $ProcedureMovimenta = ""
                . "DECLARE   \n"
                . "   V_RETORNO 	 	VARCHAR2(32767);  \n"
                . "   V_PROCID_P 	NUMBER(30);  \n"
                . "   V_COD_ORGAO 	NUMBER(30);  \n"
                . "   V_NU_OFICIO 	VARCHAR2(100);  \n"
                . "   V_PARTE 		VARCHAR2(32767);  \n"
                . "   RETORNO     	VARCHAR2(32767);  \n"
                . "   V_BAI_DIGI 	CHAR(1);   \n"
                . "BEGIN  \n"
                . "      V_PROCID_P          := :V_PROCID_P; \n"
                . "	V_COD_ORGAO	    := :V_COD_ORGAO; \n"
                . "	V_NU_OFICIO	    := :V_NU_OFICIO; \n"
                . "	V_PARTE	 	    := :V_PARTE; \n"
                . "	V_BAI_DIGI	    := :V_BAI_DIGI;	 \n"
                . "	V_RETORNO 	    := JURIS_INSERE_MOVIMENTACAO( \n"
//      ."	V_RETORNO 	    := MOVI_MOVIMENTA_JURIS.MOVIMENTA_PROCESSO( \n"
                . "	                V_PROCID_P,V_COD_ORGAO,'Remetido o ofício nº: '||V_NU_OFICIO||' para '||V_PARTE, 140800,SYSDATE \n"
//      ."	                V_PROCID_P,V_COD_ORGAO,V_NU_OFICIO,V_PARTE,V_BAI_DIGI \n"
                . "	); \n"
                . "  IF RETORNO IS NOT NULL THEN \n"
                . "      V_RETORNO := 'Não foi possível obter o resultado: ' || RETORNO; \n"
                . "  END IF;	 \n"
                . "  :RETORNO    := substr(substr(V_RETORNO,51),0,66);	\n"
                . "  EXCEPTION  \n"
                . "  	WHEN OTHERS THEN \n"
                . "		V_RETORNO :=SUBSTR(SQLERRM,1,200); \n"
                . "      :RETORNO    := substr(substr(V_RETORNO,51),0,66);	\n"
                . "END;	 \n";
        /*
         * CONECTA NO BANCO
         */
        $CnxOracle = oci_connect("SAD_S", "SADISMO", "(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.216)(PORT = 1521)) )(CONNECT_DATA = (SERVICE_NAME = trf1dsv.trf1.gov.br)(INSTANCE_NAME = trf1dsv)))");
        /*
         * PREPARA A EXECUÇÃO
         */
        $Stmt = oci_parse($CnxOracle, $ProcedureMovimenta);
        /*
         * ALIMENTA AS VARIÁVEIS
         */
        oci_bind_by_name($Stmt, ":V_PROCID_P", &$V_PROCID_P, 30, 1);
        oci_bind_by_name($Stmt, ":V_PROCID_P", &$V_PROCID_P, 30, 1);
        oci_bind_by_name($Stmt, ":V_COD_ORGAO", &$V_COD_ORGAO, 30, 1);
        oci_bind_by_name($Stmt, ":V_NU_OFICIO", &$V_NU_OFICIO, 100, 1);
        oci_bind_by_name($Stmt, ":V_PARTE", &$V_PARTE, 32767, 1);
        oci_bind_by_name($Stmt, ":V_BAI_DIGI", &$V_BAI_DIGI, 1, 1);
        oci_bind_by_name($Stmt, ":RETORNO", &$RETORNO, 32767, 1);
        /*
         * EXECUTA O PROCEDIMENTO
         */
        oci_execute($Stmt, OCI_DEFAULT);
        oci_commit($CnxOracle);
        oci_free_statement($Stmt);
        return $RETORNO;
        /*
         *       -=TENTATIVA UTILIZANDO ZEND -- SHCUMACHER 03/08/2012=-  
         *       
         *       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         *       $sql = "DECLARE
         *                   v_retorno VARCHAR2(32000);
         *               BEGIN
         *                   v_retorno := MOVI_MOVIMENTA_JURIS.MOVIMENTA_PROCESSO($proc_id,$cod_org,'$nu_oficio','$parte','$baixa_digital');
         *                   :v_retorno := v_retorno;
         *               END ; ";
         *       $stmt = $db->prepare($sql);
         *       $stmt->bindValue(":v_retorno",$retorno);
         *       $stmt->execute();        
         *      return $stmt->fetchAll();
         *
         */
    }

    public function getProcessoID($proc) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("          	SELECT PKG_JURIS_NU.GETNUMPID($proc) as proc
                                          FROM dual  ");
        return $stmt->fetch();
    }

    public function getProcessoOriginario($proc_id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "          	SELECT proc_pori
                                          FROM processo
                                         WHERE proc_id = $proc_id";
        $stmt = $db->query($sql);
        return $stmt->fetch();
    }

    public function getProcessoCodOrgJulg($proc_id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("          	SELECT proc_cod_org_julg
                                          FROM processo
                                         WHERE proc_id = $proc_id");
        return $stmt->fetch();
    }

}