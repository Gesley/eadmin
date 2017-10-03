<?php

class Application_Model_DbTable_SadTbHdpaHistDistribuicao extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_HDPA_HIST_DISTRIBUICAO';
    protected $_primary = 'HDPA_ID_DISTRIBUICAO';
    protected $_sequence = 'SAD_SQ_HDPA';

//    nao esta correto a referencia abaixo
//    protected $_dependentTables = array(
//        'Application_Model_DbTable_OcsTbPmatMatricula',
//        'Application_Model_DbTable_SadTbProcProcessoAdm',
//        'Application_Model_DbTable_SadTbCdpaContDistProcAdm',
//        'Application_Model_DbTable_SadTbCcpaContDistComissao',
//        'Application_Model_DbTable_SadTbPrdiProcessoDigital'
//    );

    
        public function relatorioProcDistribuidosPorPeriodo($dataInicial,$dataFinal) {
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $complemento = '';
        
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
       

        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                    HDPA.HDPA_CD_ORGAO_JULGADOR,
                                    ORGJ_NM_ORGAO_JULGADOR,
                                    HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                    HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                    PNAT_NO_PESSOA, 
                                    HDPA.HDPA_TS_DISTRIBUICAO,
                                    (SELECT COUNT(*)
                                        FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                        WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                    ) AS QTD_DISTRIBUICAO
                    FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                    SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                    OCS_TB_PMAT_MATRICULA PMAT,
                                    OCS_TB_PNAT_PESSOA_NATURAL PNAT,
                                    SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                    ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                    ,SAD_TB_DOCM_DOCUMENTO
                    WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                    AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                    AND (PRDI.PRDI_IC_SIGILOSO IS NULL OR PRDI.PRDI_IC_SIGILOSO = 'N')
                    AND HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA(+)
                    AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                    AND HDPA.HDPA_CD_JUIZ IS NOT NULL
                    AND ORGJ_NM_ORGAO_JULGADOR IS NOT NULL
                    AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                    AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                    AND DOCM_ID_TIPO_DOC = 152
                    $complemento
                    ORDER BY HDPA_TS_DISTRIBUICAO,ORGJ_NM_ORGAO_JULGADOR ASC");
        

        return $stmt->fetchAll();
    }
    
    public function relatorioDistribuicaoProcAdm() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT          DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA, 
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            HDPA.HDPA_DT_JULGAMENTO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                        OCS_TB_PMAT_MATRICULA PMAT,
                                        OCS_TB_PNAT_PESSOA_NATURAL PNAT,
                                        SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                        ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                        ,SAD_TB_DOCM_DOCUMENTO
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                            --AND (PRDI.PRDI_IC_SIGILOSO IS NULL OR PRDI.PRDI_IC_SIGILOSO = 'N')
                            AND HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA(+)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                            AND DOCM_ID_TIPO_DOC = 152
                            --AND HDPA.HDPA_CD_JUIZ IS NOT NULL
                            ORDER BY HDPA_TS_DISTRIBUICAO
                            ");

        return $stmt->fetchAll();
    }

    public function relatoresProcesso($id_proc) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT 
                                HDPA_CD_PROC_ADMINISTRATIVO
                                ,HDPA_CD_SERVIDOR
                                ,HDPA_CD_JUIZ
                                ,HDPA_IC_FORMA_DISTRIBUICAO
                                ,HDPA_CD_ORGAO_JULGADOR
                            FROM 
                                SAD_TB_HDPA_HIST_DISTRIBUICAO
                            WHERE
                                HDPA_CD_PROC_ADMINISTRATIVO = '$id_proc'
                            ");

        return $stmt->fetchAll();
    }

    /**
     * Recebe como parametros de entrada
     * @param string  $tipo |todos, julgados, nao_julgados
     * * @return array
     */
    public function dadosUltimaDistribuicaoProcesso($tipo = 'todos', $order = '',$idProcessoAdministrativo = null,$dhDistribuicao = null) {
        if($order==''){
            $order = 'TO_DATE(HDPA_TS_DISTRIBUICAO,\'DD/MM/YYYY hh24:mi:ss\') DESC';
        }
        if($idProcessoAdministrativo == null || $idProcessoAdministrativo == ''){
            $idProcessoAdministrativo = '';
        }else{
            $idProcessoAdministrativo = "AND HDPA_CD_PROC_ADMINISTRATIVO = $idProcessoAdministrativo";
        }
        if ($dhDistribuicao == null || $dhDistribuicao == '') {
            $dhDistribuicao = '';
        } else {
            $dhDistribuicao = "AND TO_CHAR(HDPA_TS_DISTRIBUICAO,'DD/MM/YYYY hh24:mi:ss') = '$dhDistribuicao'";
        }
        
        switch ($tipo):
            case 'julgados':
                $completa = 'AND HDPA.HDPA_DT_JULGAMENTO IS NOT NULL';
                break;
            case 'nao_julgados':
                $completa = 'AND HDPA.HDPA_DT_JULGAMENTO IS NULL';
                break;
            case 'todos':
                $completa = '';
                break;
            default :
                $completa = '';
                break;
        endswitch;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $stmt = $db->query("
            SELECT  DOCM_NR_DOCUMENTO
                    ,HDPA_ID_DISTRIBUICAO
                    ,HDPA_CD_PROC_ADMINISTRATIVO
                    ,TO_CHAR(HDPA_TS_DISTRIBUICAO,'DD/MM/YYYY hh24:mi:ss') HDPA_TS_DISTRIBUICAO
                    ,HDPA_DT_JULGAMENTO
                    ,HDPA_DS_RESUMO_DECISAO
                    ,HDPA_DT_PUBLIC_JULGAMENTO_DJ
                    ,HDPA_DT_PUBLIC_JULGAMENTO_BS
                    ,ORGJ_CD_ORGAO_JULGADOR
                    ,ORGJ_NM_ORGAO_JULGADOR
                    ,ORGJ_DS_ORGAO_JULGADOR
                    ,PMAT_CD_MATRICULA
                    ,PNAT_NO_PESSOA
            FROM    SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA
                    ,SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ
                    ,OCS_TB_PMAT_MATRICULA PMAT
                    ,OCS_TB_PNAT_PESSOA_NATURAL PNAT
                    ,SAD_TB_PRDI_PROCESSO_DIGITAL
                    ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                    ,SAD_TB_DOCM_DOCUMENTO
            WHERE HDPA.HDPA_ID_DISTRIBUICAO IN (SELECT MAX(HDPA_ID_DISTRIBUICAO)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                GROUP BY HDPA_CD_PROC_ADMINISTRATIVO)
                AND HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR
                AND (HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA
                OR HDPA.HDPA_CD_SERVIDOR = PMAT.PMAT_CD_MATRICULA)
                AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                AND HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                AND DOCM_ID_TIPO_DOC = 152
                $completa
                $idProcessoAdministrativo
                $dhDistribuicao
            ORDER BY $order
            ");
        
        return $stmt->fetchAll();
    }
    
    public function montaDadosAtaDistribuicao($codigoProcesso, $formaDistribuicao, $matricula, $orgaoJulgador){

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT PNAT_NO_PESSOA
                ,(SELECT COUNT(*)
                    FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                    WHERE HDPA_CD_PROC_ADMINISTRATIVO = '$codigoProcesso'
                            AND HDPA_IC_FORMA_DISTRIBUICAO = '$formaDistribuicao') AS QTD_DISTRIBUICAO
                ,(SELECT ORGJ_NM_ORGAO_JULGADOR 
                        FROM sad_tb_orgj_orgao_julgador
                        WHERE ORGJ_CD_ORGAO_JULGADOR = '$orgaoJulgador') AS ORGAO_JULGADOR
                ,(SELECT PRDI_DH_AUTUACAO 
                        FROM SAD_TB_PRDI_PROCESSO_DIGITAL
                        WHERE prdi_id_processo_digital = '$codigoProcesso') DATA_AUTUACAO
            FROM OCS_TB_PMAT_MATRICULA PMAT
                ,OCS_TB_PNAT_PESSOA_NATURAL PNAT
            WHERE PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                AND PMAT_DT_FIM IS NULL
                AND PMAT_CD_MATRICULA = '$matricula'");
        return $stmt->fetchAll();
    }
    public function relatorioUltimaDistribuicao($dataInicial, $dataFinal){

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $complemento = '';
        
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        
        $stmt = $db->query("
            SELECT  DOCM_NR_DOCUMENTO
                    ,PMAT_CD_MATRICULA
                    ,HDPA_TS_DISTRIBUICAO
                    ,PNAT_NO_PESSOA
                    ,HDPA_CD_PROC_ADMINISTRATIVO
                    ,HDPA_IC_FORMA_DISTRIBUICAO
                    ,ORGJ_NM_ORGAO_JULGADOR
                    ,(SELECT COUNT(*)
                                    FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                    WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                        AND HDPA.HDPA_IC_FORMA_DISTRIBUICAO = HDPA_IC_FORMA_DISTRIBUICAO
                                        AND HDPA.HDPA_TS_DISTRIBUICAO >= HDPA_TS_DISTRIBUICAO) AS QTD_DISTRIBUICAO
                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA
                    ,SAD_TB_ORGJ_ORGAO_JULGADOR
                    ,OCS_TB_PMAT_MATRICULA
                    ,OCS_TB_PNAT_PESSOA_NATURAL
                    ,SAD_TB_PRDI_PROCESSO_DIGITAL
                    ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                    ,SAD_TB_DOCM_DOCUMENTO
                WHERE HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                    AND HDPA_CD_ORGAO_JULGADOR = ORGJ_CD_ORGAO_JULGADOR
                    AND (HDPA_CD_JUIZ = PMAT_CD_MATRICULA OR HDPA_CD_SERVIDOR = PMAT_CD_MATRICULA)
                    AND PMAT_ID_PESSOA = PNAT_ID_PESSOA
                    AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                    AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                    AND DOCM_ID_TIPO_DOC = 152
                    AND HDPA_TS_DISTRIBUICAO = (SELECT MAX(HDPA_TS_DISTRIBUICAO) FROM SAD_TB_HDPA_HIST_DISTRIBUICAO WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO)
                    $complemento
                ORDER BY HDPA_TS_DISTRIBUICAO ASC
                ");
        return $stmt->fetchAll();
    }
    
    public function paraOsDesembargadoresFederais($dataInicial,$dataFinal) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $complemento = '';
        
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA, 
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            (SELECT COUNT(*)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                        AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                        AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                            ) AS QTD_DISTRIBUICAO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ
                                        ,OCS_TB_PMAT_MATRICULA PMAT
                                        ,OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                        ,SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                        ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                        ,SAD_TB_DOCM_DOCUMENTO
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                            AND HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA(+)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            AND HDPA.HDPA_CD_JUIZ IS NOT NULL
                            AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                            AND DOCM_ID_TIPO_DOC = 152
                            $complemento
                            order by HDPA_TS_DISTRIBUICAO,ORGJ_NM_ORGAO_JULGADOR
                            ");

        return $stmt->fetchAll();
    }
    public function relatorioRedistribuicao($orgao,$dataInicial,$dataFinal) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $complemento = '';
        if($orgao == 'null'){
            $orgao = '';
        }else{
            $orgao = "AND HDPA.HDPA_CD_ORGAO_JULGADOR = '$orgao'";
        }
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA, 
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            (SELECT COUNT(*)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                        AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                        AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                            ) AS QTD_DISTRIBUICAO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                        OCS_TB_PMAT_MATRICULA PMAT,
                                        OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                        ,SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                        ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                        ,SAD_TB_DOCM_DOCUMENTO
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                            AND (
                                HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA
                                OR HDPA.HDPA_CD_SERVIDOR = PMAT.PMAT_CD_MATRICULA)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            AND HDPA_IC_FORMA_DISTRIBUICAO = 'RA'
                            AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                            AND DOCM_ID_TIPO_DOC = 152
                            $orgao
                            $complemento
                            order by HDPA_TS_DISTRIBUICAO,ORGJ_NM_ORGAO_JULGADOR
                            ");

        return $stmt->fetchAll();
    }
    public function relatorioPorDesembargador($desem_federal,$dataInicial,$dataFinal) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($desem_federal == 'null'){
            $desem_federal = '';
        }else if($desem_federal == 'todos'){
            $desem_federal = '';
        }else{
            $desem_federal = "AND PMAT_CD_MATRICULA = '$desem_federal'";
        }
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA, 
                                            PMAT_CD_MATRICULA,
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            HDPA.HDPA_DT_JULGAMENTO,
                                            AQVP.AQVP_CD_PCTT ||' - '||AQAT_DS_ATIVIDADE ASSUNTO,
                                            (SELECT COUNT(*)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                        AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                        AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                            ) AS QTD_DISTRIBUICAO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                        OCS_TB_PMAT_MATRICULA PMAT,
                                        OCS_TB_PNAT_PESSOA_NATURAL PNAT,
                                        SAD_TB_PRDI_PROCESSO_DIGITAL PRDI,
                                        SAD_TB_AQVP_VIA_PCTT AQVP,
                                        SAD_TB_AQAT_ATIVIDADE AQAT,
                                        SAD_TB_DOCM_DOCUMENTO DOCM,
                                        OCS_TB_DTPD_TIPO_DOC DTPD,
                                        SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            AND (PRDI.PRDI_IC_SIGILOSO IS NULL OR PRDI.PRDI_IC_SIGILOSO = 'N')
                            AND HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA(+)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            and AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            AND AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            AND DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                            AND DOCM.DOCM_ID_DOCUMENTO = DCPR.DCPR_ID_DOCUMENTO
                            AND DCPR.DCPR_ID_PROCESSO_DIGITAL = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            AND DTPD.DTPD_ID_TIPO_DOC = 152

                
                            AND HDPA.HDPA_CD_JUIZ IS NOT NULL
                            $desem_federal
                            $complemento
                            -- AND (:P_INICIO IS NULL OR (PRDI.PRDI_DH_DISTRIBUICAO BETWEEN DECODE(:P_INICIO,NULL,TO_DATE('01/01/2001','DD/MM/YYYY'),:P_INICIO)  AND   :P_FIM+1))
                            ORDER BY HDPA.HDPA_CD_ORGAO_JULGADOR, HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                            ");

        return $stmt->fetchAll();
    }
    public function relatorioPorDesembargadorAssunto($desem_federal,$dataInicial,$dataFinal) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($desem_federal == 'null'){
            $desem_federal = '';
        }else if($desem_federal == 'todos'){
            $desem_federal = '';
        }else{
            $desem_federal = "AND PMAT_CD_MATRICULA = '$desem_federal'";
        }
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA, 
                                            PMAT_CD_MATRICULA,
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            AQVP.AQVP_CD_PCTT ||' - '||AQAT_DS_ATIVIDADE ASSUNTO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            (SELECT COUNT(*)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                        AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                        AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                            ) AS QTD_DISTRIBUICAO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                        OCS_TB_PMAT_MATRICULA PMAT,
                                        OCS_TB_PNAT_PESSOA_NATURAL PNAT,
                                        SAD_TB_PRDI_PROCESSO_DIGITAL PRDI,
                                        SAD_TB_AQVP_VIA_PCTT AQVP,
                                        SAD_TB_AQAT_ATIVIDADE AQAT,
                                        SAD_TB_DOCM_DOCUMENTO DOCM,
                                        OCS_TB_DTPD_TIPO_DOC DTPD,
                                        SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            AND (PRDI.PRDI_IC_SIGILOSO IS NULL OR PRDI.PRDI_IC_SIGILOSO = 'N')
                            AND HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA(+)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            and AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            AND AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            AND DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                            AND DOCM.DOCM_ID_DOCUMENTO = DCPR.DCPR_ID_DOCUMENTO
                            AND DCPR.DCPR_ID_PROCESSO_DIGITAL = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            AND DTPD.DTPD_ID_TIPO_DOC = 152
                            AND HDPA.HDPA_CD_JUIZ IS NOT NULL
                            $desem_federal
                            $complemento
                            -- AND (:P_JUIZ =0  OR (HDPA.HDPA_CD_JUIZ=:P_JUIZ))
                            -- AND (HDPA_DT_JULGAMENTO BETWEEN :P_INICIO AND :P_FIM OR :P_INICIO IS NULL OR :P_FIM IS NULL)
                            ORDER BY HDPA.HDPA_CD_ORGAO_JULGADOR, HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                            ");

        return $stmt->fetchAll();
    }
    public function relatorioProcessoJulgado($dataInicial, $dataFinal) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($dataInicial <> 'null' && $dataFinal <> 'null'){
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        }else{
            
            if($dataInicial <> 'null'){
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            }else{
                if($dataFinal <> 'null'){
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA,
                                            PMAT_CD_MATRICULA,
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            HDPA.HDPA_DT_JULGAMENTO,
                                            HDPA.HDPA_DS_RESUMO_DECISAO,
                                            (SELECT COUNT(*)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                        AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                        AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                            ) AS QTD_DISTRIBUICAO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                        OCS_TB_PMAT_MATRICULA PMAT,
                                        OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                        ,SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                        ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                        ,SAD_TB_DOCM_DOCUMENTO
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                            AND (PRDI.PRDI_IC_SIGILOSO IS NULL OR PRDI.PRDI_IC_SIGILOSO = 'N')
                            AND HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA(+)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            AND HDPA.HDPA_CD_JUIZ IS NOT NULL 
                            AND HDPA.HDPA_DT_JULGAMENTO IS NOT NULL
                            AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                            AND DOCM_ID_TIPO_DOC = 152
                            $complemento
                            --AND (HDPA_DT_JULGAMENTO BETWEEN :P_INICIO AND :P_FIM OR :P_INICIO IS NULL OR :P_FIM IS NULL)
                            ORDER BY HDPA.HDPA_CD_ORGAO_JULGADOR, HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                            ");

        return $stmt->fetchAll();
    }
    public function relatorioConselhosComissoes($orgao, $dataInicial,$dataFinal) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $complemento = '';
        if($orgao == 'null'){
            $orgao = 'AND HDPA.HDPA_CD_ORGAO_JULGADOR IN (7000,4000)';
        }else{
            $orgao = "AND HDPA.HDPA_CD_ORGAO_JULGADOR = '$orgao'";
        }
        if ($dataInicial <> 'null' && $dataFinal <> 'null') {
            $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') BETWEEN '$dataInicial' AND '$dataFinal'";
        } else {

            if ($dataInicial <> 'null') {
                $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') >= '$dataInicial'";
            } else {
                if ($dataFinal <> 'null') {
                    $complemento = "AND TO_DATE(TO_CHAR(HDPA.HDPA_TS_DISTRIBUICAO,'DDMMYYYY'),'DDMMYYYY') <= '$dataFinal'";
                }
            }
        }
        $stmt = $db->query("SELECT DISTINCT DOCM_NR_DOCUMENTO,
                                            PNAT_NO_PESSOA, 
                                            ORGJ_NM_ORGAO_JULGADOR,
                                            HDPA.HDPA_TS_DISTRIBUICAO,
                                            HDPA.HDPA_CD_PROC_ADMINISTRATIVO,
                                            HDPA.HDPA_IC_FORMA_DISTRIBUICAO,
                                            HDPA.HDPA_CD_ORGAO_JULGADOR,
                                            HDPA.HDPA_DT_JULGAMENTO,
                                            (SELECT COUNT(*)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                WHERE HDPA_CD_PROC_ADMINISTRATIVO = HDPA.HDPA_CD_PROC_ADMINISTRATIVO
                                                        AND HDPA_IC_FORMA_DISTRIBUICAO = HDPA.HDPA_IC_FORMA_DISTRIBUICAO
                                                        AND HDPA_TS_DISTRIBUICAO <= HDPA.HDPA_TS_DISTRIBUICAO
                                            ) AS QTD_DISTRIBUICAO
                            FROM SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA,
                                        SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                        OCS_TB_PMAT_MATRICULA PMAT,
                                        OCS_TB_PNAT_PESSOA_NATURAL PNAT,
                                        SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                        ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                        ,SAD_TB_DOCM_DOCUMENTO
                            WHERE HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR(+)
                            AND HDPA.HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                            AND (PRDI.PRDI_IC_SIGILOSO IS NULL OR PRDI.PRDI_IC_SIGILOSO = 'N')
                            AND (HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA
                                OR HDPA.HDPA_CD_SERVIDOR = PMAT.PMAT_CD_MATRICULA)
                            AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                            AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                            AND DOCM_ID_TIPO_DOC = 152
                            $orgao
                            $complemento
                            order by HDPA_TS_DISTRIBUICAO,ORGJ_NM_ORGAO_JULGADOR
                            ");

        return $stmt->fetchAll();
    }
    //essa função nao desfaz a movimentação do processo
    public function desfazDistribuicaoPorNumeroDoc(
            $idDocmDocumento,
            $dataMofaMoviFase,
            $autoCommit = true) {
        try {
            

            if ($autoCommit) {
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();

                $adapter->beginTransaction();
            }
           
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $rowMofaMoviFase = $SadTbMofaMoviFase->
                    select()
                    ->where('MOFA_ID_MOVIMENTACAO = ' . $dataMofaMoviFase['MOFA_ID_MOVIMENTACAO'] . ' AND MOFA_DH_FASE = TO_DATE(\'' . $dataMofaMoviFase['MOFA_DH_FASE'] . '\',\'dd/mm/yyyy HH24:MI:SS\')')
                    ->query()
                    ->fetch();
            if ($rowMofaMoviFase["MOFA_ID_FASE"] == 1046 || $rowMofaMoviFase["MOFA_ID_FASE"] == 1045) {

                $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                $rowProcesso = $mapperDocumento->getIdProcesso($idDocmDocumento);
                $idPocessoDigital = $rowProcesso[0]['DCPR_ID_PROCESSO_DIGITAL'];
                
                $dadosProcessoAdministrativo = $this->dadosUltimaDistribuicaoProcesso('todos', '', $idPocessoDigital,$dataMofaMoviFase['MOFA_DH_FASE']);
                
//SE FOI FEITA UMA DISTRIBUIÇÃO ENTAO APAGUE ELA
                if (count($dadosProcessoAdministrativo) != 0) {
                    
                    /* RETIRA RELATOR NA PRDI */
                    $dadosPrdi['PRDI_CD_MATR_SERV_RELATOR'] = null;
                    $dadosPrdi['PRDI_CD_JUIZ_RELATOR_PROCESSO'] = null;
                    $dadosPrdi['PRDI_DH_DISTRIBUICAO'] = null;
                    $dadosPrdi['PRDI_CD_MATR_DISTRIBUICAO'] = null;
                    $dadosPrdi['PRDI_CD_ORGAO_JULGADOR'] = null;
                    
                    $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
                   $SadTbPrdiProcessoDigital
                            ->find($idPocessoDigital)
                            ->current()
                            ->setFromArray($dadosPrdi)
                            ->save();
                       
                    /* DELETA NA HDPA */
                    $boolean = $this->delete("HDPA_ID_DISTRIBUICAO = " . $dadosProcessoAdministrativo[0]['HDPA_ID_DISTRIBUICAO']);

                    if ($boolean == true) {
                        if ($dadosProcessoAdministrativo[0]['ORGJ_CD_ORGAO_JULGADOR'] == 1000 || $dadosProcessoAdministrativo[0]['ORGJ_CD_ORGAO_JULGADOR'] == 2000 || $dadosProcessoAdministrativo[0]['ORGJ_CD_ORGAO_JULGADOR'] == 3000) {
                            /* SETA DISPONIVEL NA CDPA */
                            $SadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();
                            $SadTbCdpaContDistProcAdm
                                    ->find($dadosProcessoAdministrativo[0]['ORGJ_CD_ORGAO_JULGADOR'], $dadosProcessoAdministrativo[0]['PMAT_CD_MATRICULA'])
                                    ->current()
                                    ->setFromArray(array('CDPA_IC_DISTRIBUICAO' => 'N'))
                                    ->save();
                        } else {
                            /* SETA DISPONIVEL NA CCPA */
                            $SadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
                            $SadTbCcpaContDistComissao
                                    ->find($dadosProcessoAdministrativo[0]['ORGJ_CD_ORGAO_JULGADOR'], $dadosProcessoAdministrativo[0]['PMAT_CD_MATRICULA'])
                                    ->current()
                                    ->setFromArray(array('CCPA_IC_DISTRIBUICAO' => 'N'))
                                    ->save();
                        }
                    }
                }
            }
            
            if ($autoCommit) {
                $adapter->commit();
            }
            return true;
        } catch (Exception $exc) {
            if ($autoCommit) {
                $adapter->rollBack();
            }
            return $exc->getMessage();
        }
    }
}