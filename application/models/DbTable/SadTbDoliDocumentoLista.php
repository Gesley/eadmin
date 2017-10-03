<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SadTbDoliDocumentoLista extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_DOLI_DOCUMENTO_LISTA';
    protected $_primary = 'DOLI_ID_LISTA_DIVULGACAO';


    public function setDivulgarDocumento($data,$documento)
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $tabelaListaDivulgacao = new Application_Model_DbTable_SadTbListListaDivulgacao();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        /* Verifica se todos os documentos tem confidencialidade pública */
        foreach ($documento['documento'] as $doc_aviso) {
            $decoded = Zend_Json_Decoder::decode($doc_aviso);
            if ($decoded['DOCM_ID_CONFIDENCIALIDADE']!="0")
                return "nao_publico";
        }
        try {
            if (isset($data['list_id_componente']) && $data['list_id_componente'] != NULL) {
                foreach ($documento['documento'] as $docDivulgar) {
                    foreach ($data['list_id_componente'] as $dados) {
                        $decode = Zend_Json_Decoder::decode($docDivulgar);
                        $id_grupo_divulgacao = explode(" - ", $dados);
                        $dadosListaDivul['LIST_ID_DOCUMENTO_DIVULGADO'] = $decode['DOCM_ID_DOCUMENTO'];
                        $dadosListaDivul['LIST_ID_GRUPO_DIVULGACAO'] = $id_grupo_divulgacao[0];
                        $dadosListaDivul['LIST_QT_DIAS_DIVULGACAO'] = 0;
                        $dadosListaDivul['LIST_SG_SECAO_DIVULGADORA'] = $userNs->siglasecao;
                        $dadosListaDivul['LIST_CD_LOTACAO_DIVULGADORA'] = $userNs->codlotacao;
                        $dadosListaDivul['LIST_CD_MATRICULA_DIVULGADORA'] = $userNs->matricula;
                        $dadosListaDivul['LIST_DT_INICIO_DIVULGACAO'] = new Zend_Db_Expr("TO_DATE('" . $data['LIST_DT_INICIO_DIVULGACAO'] . "','dd/mm/yyyy HH24:MI:SS')");
                        $dadosListaDivul['LIST_DT_FIM_DIVULGACAO'] = new Zend_Db_Expr("TO_DATE('" . $data['LIST_DT_FIM_DIVULGACAO'] . "','dd/mm/yyyy HH24:MI:SS')");

                        $rowListaDivulgacao = $tabelaListaDivulgacao->createRow($dadosListaDivul);
                        $id_lista_divulgacao = $rowListaDivulgacao->save();
                        
                        $id_documento = $decode['DOCM_ID_DOCUMENTO'];
                        $dadosDocumentoLista['DOLI_ID_DOCUMENTO'] = $id_documento;
                        $dadosDocumentoLista['DOLI_ID_LISTA_DIVULGACAO'] = $id_lista_divulgacao;
                        $rowDocumentoLista = $this->createRow($dadosDocumentoLista);
                        $rowDocumentoLista->save();
                    }
                    /* FINALIZA A MOVIMENTAÇÃO INDIVIDUAL DO DOCUMENTO.*/
                    $where = "DOCM_ID_DOCUMENTO = $id_documento";
                    $dataUpdt = array(
                        "DOCM_IC_MOVI_INDIVIDUAL" => "N"
                    );
                    $tabelaSadTbDocmDocumento->update($dataUpdt, $where);
                }
            }
            $db->commit();
            return TRUE;
        } catch (Exception $exc) {
            $db->rollBack();
            return $exc->getMessage();
        }
    }

    public function getGruposAjax($nomeGrupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UPPER(GRDV_DS_GRUPO_DIVULGACAO) AS LABEL,
                                   GRDV_ID_GRUPO_DIVULGACAO||' - '||GRDV_DS_GRUPO_DIVULGACAO AS VALUE
                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
                            WHERE UPPER(GRDV_ID_GRUPO_DIVULGACAO||' - '||GRDV_DS_GRUPO_DIVULGACAO) LIKE UPPER('%$nomeGrupo%')
                            AND GRDV_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }
    
    public function getGruposAjaxEspecial($nomeGrupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UPPER(GRDV_DS_GRUPO_DIVULGACAO) AS LABEL,
                                   GRDV_ID_GRUPO_DIVULGACAO||' - '||GRDV_DS_GRUPO_DIVULGACAO AS VALUE
                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
                            WHERE UPPER(GRDV_ID_GRUPO_DIVULGACAO||' - '||GRDV_DS_GRUPO_DIVULGACAO) LIKE UPPER('%$nomeGrupo%')
                            AND GRDV_ID_GRUPO_DIVULGACAO NOT IN (48,49)
                            AND GRDV_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }
    
    public function getComponenteGrupoAjax($nomeGrupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UPPER(GRDV_DS_GRUPO_DIVULGACAO) AS LABEL,
                                   GRDV_ID_GRUPO_DIVULGACAO||' - '||GRDV_DS_GRUPO_DIVULGACAO AS VALUE
                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
                            WHERE UPPER(GRDV_ID_GRUPO_DIVULGACAO||' - '||GRDV_DS_GRUPO_DIVULGACAO) LIKE UPPER('%$nomeGrupo%')");
        return $stmt->fetchAll();
    }
    
    public function getCaixaAvisosUnidade($sg_secao,$cd_secao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM_NR_DOCUMENTO,
                                 DOCM_ID_DOCUMENTO,
                                 DTPD_NO_TIPO,
                                 DOCM_DS_ASSUNTO_DOC,
                                 LIST_SG_SECAO_DIVULGADORA,
                                 LIST_CD_LOTACAO_DIVULGADORA,
                                 LIST_CD_MATRICULA_DIVULGADORA,
                                 LIST_DT_INICIO_DIVULGACAO,
                                 LIST_DT_FIM_DIVULGACAO,
                                 DOCM_NR_DOCUMENTO_RED
                            FROM SAD_TB_DOLI_DOCUMENTO_LISTA
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO
                            ON DOLI_ID_DOCUMENTO  = DOCM_ID_DOCUMENTO
                            INNER JOIN SAD_TB_LIST_LISTA_DIVULGACAO
                            ON DOLI_ID_LISTA_DIVULGACAO = list_id_lista_divulgacao
                            INNER JOIN SAD_TB_GRDV_GRUPO_DIVULGACAO
                            ON LIST_ID_GRUPO_DIVULGACAO = GRDV_ID_GRUPO_DIVULGACAO
                            INNER JOIN SAD_TB_COMP_COMPONENTE_GRUPO
                            ON  COMP_ID_GRUPO_DIVULGACAO = GRDV_ID_GRUPO_DIVULGACAO
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC
                            ON DTPD_ID_TIPO_DOC = DOCM_ID_TIPO_DOC
                            INNER JOIN rh_central_lotacao
                            ON COMP_SG_SECAO = LOTA_SIGLA_SECAO
                            AND COMP_CD_LOTACAO = lota_cod_lotacao
                            WHERE COMP_SG_SECAO = '$sg_secao' 
                            AND COMP_CD_LOTACAO = '$cd_secao'
                            AND TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE(LIST_DT_INICIO_DIVULGACAO,'DD/MM/YYYY HH24:MI:SS') AND TO_DATE(LIST_DT_FIM_DIVULGACAO,'DD/MM/YYYY HH24:MI:SS')");
        return $stmt->fetchAll();
    }
    
    public function getCaixaAvisosPessoais($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM_NR_DOCUMENTO,
                                 DOCM_ID_DOCUMENTO,
                                 DTPD_NO_TIPO,
                                 DOCM_DS_ASSUNTO_DOC,
                                 LIST_SG_SECAO_DIVULGADORA,
                                 LIST_CD_LOTACAO_DIVULGADORA,
                                 LIST_CD_MATRICULA_DIVULGADORA,
                                 LIST_DT_INICIO_DIVULGACAO,
                                 LIST_DT_FIM_DIVULGACAO,
                                 DOCM_NR_DOCUMENTO_RED
                            FROM SAD_TB_LIST_LISTA_DIVULGACAO
                            INNER JOIN SAD_TB_DOLI_DOCUMENTO_LISTA
                            ON LIST_ID_LISTA_DIVULGACAO = DOLI_ID_LISTA_DIVULGACAO
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO
                            ON LIST_ID_DOCUMENTO_DIVULGADO = DOCM_ID_DOCUMENTO
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC
                            ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE LIST_ID_GRUPO_DIVULGACAO IN (SELECT COMP_ID_GRUPO_DIVULGACAO
                                                            FROM SAD_TB_COMP_COMPONENTE_GRUPO
                                                            WHERE COMP_CD_MATRICULA_TRF = '$matricula')
                            AND TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE(LIST_DT_INICIO_DIVULGACAO,'DD/MM/YYYY HH24:MI:SS') AND TO_DATE(LIST_DT_FIM_DIVULGACAO,'DD/MM/YYYY HH24:MI:SS')");
        return $stmt->fetchAll();
    }
    
    public function getCaixaAvisosPessoaisCount($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) AS COUNT
                            FROM SAD_TB_LIST_LISTA_DIVULGACAO
                            INNER JOIN SAD_TB_DOLI_DOCUMENTO_LISTA
                            ON LIST_ID_LISTA_DIVULGACAO = DOLI_ID_LISTA_DIVULGACAO
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO
                            ON LIST_ID_DOCUMENTO_DIVULGADO = DOCM_ID_DOCUMENTO
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC
                            ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE LIST_ID_GRUPO_DIVULGACAO IN (SELECT COMP_ID_GRUPO_DIVULGACAO
                                                              FROM SAD_TB_COMP_COMPONENTE_GRUPO
                                                              WHERE COMP_CD_MATRICULA_TRF = '$matricula')
                            AND TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS') BETWEEN TO_DATE(LIST_DT_INICIO_DIVULGACAO,'DD/MM/YYYY HH24:MI:SS') AND TO_DATE(LIST_DT_FIM_DIVULGACAO,'DD/MM/YYYY HH24:MI:SS')");
        return $stmt->fetch();
    }
}