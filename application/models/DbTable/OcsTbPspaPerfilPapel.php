<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbPspaPerfilPapel extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PSPA_PERFIL_PAPEL';
    protected $_primary = 'PSPA_ID_PERFIL_PAPEL';
    protected $_sequence = 'OCS_SQ_PSPA_PERFIL_SIST_PAPEL';
    
    /* Permissão de cadastro de solicitação */
//    public function getPermissaoCadastroSolicitacao($user, $uf)
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT APSU_CD_UNIDADE
//		            FROM   OCS_TB_APAS_PAPEL_SISTEMA, 
//	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
//	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
//	            	           OCS_TB_PMAT_MATRICULA,
//	            	           RH_CENTRAL_LOTACAO	             
//		            WHERE  APAS_ID_PAPEL_SISTEMA = 1
//		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
//		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
//		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
//		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
//	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
//		            AND    PMAT_CD_MATRICULA     = '$user'
//		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
//		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
//        $unidade = $stmt->fetchAll();
//        return $unidade[0]["APSU_CD_UNIDADE"];
//    }
//
//    /* Permissão de help-desk */
//    public function getPermissaoHelpDesk($user, $uf)
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT APSU_CD_UNIDADE
//		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
//	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
//	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
//	            	           OCS_TB_PMAT_MATRICULA,
//	            	           RH_CENTRAL_LOTACAO
//		            WHERE  APAS_ID_PAPEL_SISTEMA = 3
//		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
//		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
//		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
//		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
//	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
//		            AND    PMAT_CD_MATRICULA     = '$user'
//		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
//		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
//        $unidade = $stmt->fetchAll();
//        return $unidade[0]["APSU_CD_UNIDADE"];
//    }
//
//    /* Permissão de cadastro de tabelas básicas */
//    public function getPermissaoCadastroTabelasBasicas($user, $uf)
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT APSU_CD_UNIDADE
//		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
//	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
//	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
//	            	           OCS_TB_PMAT_MATRICULA,
//	            	           RH_CENTRAL_LOTACAO
//		            WHERE  APAS_ID_PAPEL_SISTEMA = 4
//		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
//		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
//		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
//		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
//	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
//		            AND    PMAT_CD_MATRICULA     = '$user'
//		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
//		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
//        $unidade = $stmt->fetchAll();
//        return $unidade[0]["APSU_CD_UNIDADE"];
//    }
//
//    /* Permissão de gestor */
//    public function getPermissaoGestor($user, $uf)
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT APSU_CD_UNIDADE
//		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
//	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
//	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
//	            	           OCS_TB_PMAT_MATRICULA,
//	            	           RH_CENTRAL_LOTACAO
//		            WHERE  APAS_ID_PAPEL_SISTEMA = 6
//		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
//		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
//		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
//		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
//	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
//		            AND    PMAT_CD_MATRICULA     = '$user'
//		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
//		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
//        $unidade = $stmt->fetchAll();
//        return $unidade[0]["APSU_CD_UNIDADE"];
//    }
//
//    /* Permissão de preposto */
//    public function getPermissaoPreposto($user, $uf)
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT APSU_CD_UNIDADE
//		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
//	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
//	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
//	            	           OCS_TB_PMAT_MATRICULA,
//	            	           RH_CENTRAL_LOTACAO
//		            WHERE  APAS_ID_PAPEL_SISTEMA = 7
//		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
//		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
//		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
//		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
//	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
//		            AND    PMAT_CD_MATRICULA     = '$user'
//		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
//		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
//        $unidade = $stmt->fetchAll();
//        return $unidade[0]["APSU_CD_UNIDADE"];
//    }    
       
    public function getPapelbyPerfil($id,$modulo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT papl_id_papel, 
                                   papl_nm_papel,
                                   papl_ds_finalidade, 
                                   pspa_id_perfil_papel,
                                   modl.modl_nm_modulo,
                                   ctrl.ctrl_nm_controle_sistema,
                                   acao.acao_nm_acao_sistema
                            FROM   SISTEMAS_TRF SIS,
                                   OCS_TB_MODL_MODULO MODL,
                                   OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                   OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                   OCS_TB_PAPL_PAPEL PAPL,
                                   OCS_TB_PSPA_PERFIL_PAPEL PSPA,
                                   OCS_TB_PERF_PERFIL  PERF
                            WHERE  PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   PAPL.papl_id_papel =  PSPA.pspa_id_papel
                            AND   PSPA.pspa_id_perfil = PERF.perf_id_perfil
                            AND   PERF.perf_id_perfil = $id
                            AND   MODL.modl_id_modulo = $modulo

                            UNION

                            SELECT papl_id_papel, 
                                   papl_nm_papel, 
                                   papl_ds_finalidade, 
                                   pspa_id_perfil_papel,
                                   modl.modl_nm_modulo,
                                   ctrl.ctrl_nm_controle_sistema,
                                   acao.acao_nm_acao_sistema
                            FROM   OCS_TB_PAPL_PAPEL PAPL,
                                   SISTEMAS_TRF SIS,
                                   OCS_TB_MODL_MODULO MODL,
                                   OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                   OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                            (SELECT NULL AS pspa_id_perfil_papel
                            FROM DUAL )
                            WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   MODL.modl_id_modulo = $modulo
                            AND   papl_id_papel NOT IN 
                            (
                            SELECT papl_id_papel
                            FROM   SISTEMAS_TRF SIS,
                                   OCS_TB_MODL_MODULO MODL,
                                   OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                   OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                   OCS_TB_PAPL_PAPEL PAPL,
                                   OCS_TB_PSPA_PERFIL_PAPEL PSPA,
                                   OCS_TB_PERF_PERFIL  PERF
                            WHERE  PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   PAPL.papl_id_papel =  PSPA.pspa_id_papel
                            AND   PSPA.pspa_id_perfil = PERF.perf_id_perfil
                            AND   PERF.perf_id_perfil = $id
                            AND   MODL.modl_id_modulo = $modulo)
                            ORDER BY modl_nm_modulo, ctrl_nm_controle_sistema
                            ");
        return $stmt->fetchAll();
    }
    
    
    public function getPapeisCriados()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PAPL.PAPL_ID_PAPEL, 
                               PAPL.PAPL_CD_MATRICULA_EXCLUSAO,
                               PAPL.PAPL_DT_EXCLUSAO,
                               PAPL.PAPL_NM_PAPEL, 
                               PAPL.PAPL_DS_FINALIDADE,
                               PAPL.PAPL_SG_SISTEMA AS SISTEMA,
                               TEMP.MODULO,
                               TEMP.CONTROLE,
                               TEMP.ACAO
                         FROM  OCS_TB_PAPL_PAPEL PAPL,
                                     SISTEMAS_TRF SIS,
                                     (SELECT NULL AS MODULO, NULL AS CONTROLE, NULL AS ACAO
                         FROM DUAL) TEMP
                         WHERE PAPL.PAPL_SG_SISTEMA = SIS.NOME_SISTEMA
                         UNION
                         SELECT PAPL.PAPL_ID_PAPEL,
                                PAPL.PAPL_CD_MATRICULA_EXCLUSAO,
                                PAPL.PAPL_DT_EXCLUSAO, 
                                PAPL.PAPL_NM_PAPEL, 
                                PAPL.PAPL_DS_FINALIDADE,
                                MODL.MODL_NM_SISTEMA AS SISTEMA,
                                MODL.MODL_NM_MODULO AS MODULO,
                                CTRL.CTRL_NM_CONTROLE_SISTEMA AS CONTROLE,
                                ACAO.ACAO_NM_ACAO_SISTEMA AS ACAO

                          FROM  OCS_TB_PAPL_PAPEL PAPL,
                                SISTEMAS_TRF SIS,
                                OCS_TB_MODL_MODULO MODL,
                                OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                OCS_TB_ACAO_ACAO_SISTEMA ACAO
                          WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                          AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                          AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                          AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                          AND   PAPL.PAPL_DT_EXCLUSAO IS NULL
                          AND   PAPL.PAPL_CD_MATRICULA_EXCLUSAO IS NULL");
        return $stmt->fetchAll();
    }
    
    
    public function getModulos()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT a.modl_id_modulo, a.modl_nm_modulo
                                FROM ocs_tb_modl_modulo a");
        return $stmt->fetchAll();
    }
    
    public function getDeletar($papl_id_papel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_PAPL_PAPEL
                                WHERE PAPL_ID_PAPEL = $papl_id_papel");
    }
    
    public function getUpdate($papl_nm_papel, $papl_ds_finalidade, $papl_id_papel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("UPDATE OCS_TB_PAPL_PAPEL
                                SET PAPL_NM_PAPEL = '$papl_nm_papel',
                                    PAPL_DS_FINALIDADE = '$papl_ds_finalidade'
                                    WHERE PAPL_ID_PAPEL = $papl_id_papel");
    }

}