<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbPerfPerfil extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PERF_PERFIL';
    protected $_primary = 'PERF_ID_PERFIL';
    protected $_sequence = 'OCS_SQ_PERF_PERFIL';
    
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
    
    public function getPerfisCriados()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF_ID_PERFIL, PERF_DS_PERFIL
                                   FROM OCS_TB_PERF_PERFIL 
                                   ORDER BY 2");
        return $stmt->fetchAll();
    }
    
    public function getDeletar($papl_id_papel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_PERF_PERFIL
                                WHERE PERF_ID_PAPEL = $papl_id_papel");
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