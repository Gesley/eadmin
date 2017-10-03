<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class Application_Model_DbTable_SadTbPediPermissaoDivulg extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_PEDI_PERMISSAO_DIVULG';
    protected $_primary = 'PEDI_ID_TIPO_DOC';
//    protected $_sequence = 'SAD_SQ_GRDV';/*Não tem SEQUENCE, é uma FK*/


   public function getUnidadesDivulgadoras($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UNPE_SG_SECAO,
                                   UNPE_CD_LOTACAO,
                                   RH_DESCRICAO_CENTRAL_LOTACAO(UNPE_SG_SECAO,UNPE_CD_LOTACAO) LOTA_DSC_LOTACAO
                            FROM OCS_TB_UNPE_UNIDADE_PERFIL
                            INNER JOIN OCS_TB_PERF_PERFIL
                            OCS_TB_PERF_PERFIL
                            ON UNPE_ID_PERFIL = PERF_ID_PERFIL
                            INNER JOIN sad_tb_pedi_permissao_divulg
                            ON unpe_sg_secao = pedi_sg_secao
                            AND UNPE_CD_LOTACAO = pedi_cd_lotacao
                            WHERE PERF_ID_PERFIL = 8/*DESENVOLVEDOR E-ADMIN*/");
        return $stmt->fetchAll();
    }
    
   public function getPermission($sg_secao,$cd_lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) AS TEM_PERMISSAO
                            FROM OCS_TB_UNPE_UNIDADE_PERFIL
                            INNER JOIN OCS_TB_PERF_PERFIL
                            OCS_TB_PERF_PERFIL
                            ON UNPE_ID_PERFIL = PERF_ID_PERFIL
                            INNER JOIN SAD_TB_PEDI_PERMISSAO_DIVULG
                            ON UNPE_SG_SECAO = PEDI_SG_SECAO
                            AND UNPE_CD_LOTACAO = PEDI_CD_LOTACAO
                            WHERE PERF_ID_PERFIL = 8 /*DESENVOLVEDOR E-ADMIN*/
                            AND PEDI_SG_SECAO = '$sg_secao'
                            AND PEDI_CD_LOTACAO = $cd_lotacao");
        return $stmt->fetch();
    }
    
   public function getListaDocumentosPermitidos($sg_secao,$cd_secao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT PEDI_SG_SECAO,
                                    PEDI_CD_LOTACAO,
                                    RH_DESCRICAO_CENTRAL_LOTACAO(PEDI_SG_SECAO,PEDI_CD_LOTACAO) LOTA_DSC_LOTACAO,
                                    PEDI_ID_TIPO_DOC,
                                    DTPD_NO_TIPO
                             FROM SAD_TB_PEDI_PERMISSAO_DIVULG
                             INNER JOIN OCS_TB_DTPD_TIPO_DOC
                             ON PEDI_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                             WHERE PEDI_SG_SECAO = '$sg_secao'
                             AND PEDI_CD_LOTACAO = $cd_secao");
        return $stmt->fetchAll();
    }
    
   public function setNewDocumentoDivulgacao($data)
    {
        $tabelaPermissaoDivulgacao = new Application_Model_DbTable_SadTbPediPermissaoDivulg();

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            /* Verifica se o usuario incluiu um Documento para Divulgacao */
            if (isset($data["tipo_doc"]) && $data["tipo_doc"] != NULL) {
                foreach ($data["tipo_doc"] as $doc) {
                    $tipo_doc = explode(" - ", $doc);
                    $dadosPediPermDivu['PEDI_ID_TIPO_DOC'] = $tipo_doc[0];
                    $dadosPediPermDivu['PEDI_SG_SECAO'] = $data['sgsecao'];
                    $dadosPediPermDivu['PEDI_CD_LOTACAO'] = $data['codlotacao'];
                    $rowPediPermDivu = $tabelaPermissaoDivulgacao->createRow($dadosPediPermDivu);
                    $rowPediPermDivu->save();
                }
            }
            $db->commit();
        } catch (Zend_Exception $e) {
            $db->rollBack();
            return $e->getMessage();
        }
    }
    
   public function setDelDocumentoDivulgacao($id,$sgsecao,$codlotacao)
    {
        $tabelaPermissaoDivulgacao = new Application_Model_DbTable_SadTbPediPermissaoDivulg();

//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $db->beginTransaction();
        try {
            $linha = $tabelaPermissaoDivulgacao->fetchRow("PEDI_ID_TIPO_DOC = $id AND PEDI_SG_SECAO = '$sgsecao' AND PEDI_CD_LOTACAO = $codlotacao");
            $linha->delete();
//            $db->commit();
        } catch (Zend_Exception $e) {
//            $db->rollBack();
            return $e->getMessage();
        }
    }

}