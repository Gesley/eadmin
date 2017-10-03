<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbSipaSistemaPapel extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_SIPA_SISTEMA_PAPEL';
    protected $_primary = 'SIPA_ID_SISTEMA_PAPEL';
    protected $_sequence = 'OCS_SQ_SIPA_SISTEMA_PAPEL';
    
//    /* Permissão de cadastro de solicitação */
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
    /* */
    public function getPessoa($lota_cod_lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA,  B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA 
					FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
					WHERE A.PMAT_CD_UNIDADE_LOTACAO = $lota_cod_lotacao
					AND  A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
					ORDER BY B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }
    
    
    public function getSistemas()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'SOSTI' 
                                OR NOME_SISTEMA ='SISAD'");
        return $stmt->fetchAll();
    }
    
    public function getPapeisCriados($sistema)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PAPL_ID_PAPEL, PAPL_NM_PAPEL, PAPL_DS_FINALIDADE, SIPA_ID_SISTEMA_PAPEL
                                   FROM  OCS_TB_PAPL_PAPEL A,OCS_TB_SIPA_SISTEMA_PAPEL B
                                   WHERE A.PAPL_ID_PAPEL = B.SIPA_ID_PAPEL
                                   AND   UPPER(B.SIPA_DS_MODULE_SISTEMA) = UPPER('$sistema')");
        return $stmt->fetchAll();
    }
    
    
    public function getInserir($apas_id_papel, $apas_sg_sistema)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("INSERT INTO OCS_TB_APAS_PAPEL_SISTEMA(APAS_ID_PAPEL,APAS_SG_SISTEMA)
		  		VALUES ('$apas_id_papel','$apas_sg_sistema')");
    }
    
    public function getDeletar($apas_id_papel, $apas_sg_sistema)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_APAS_PAPEL_SISTEMA
	  			   WHERE APAS_ID_PAPEL = $apas_id_papel
	  			   AND   APAS_SG_SISTEMA = '$apas_sg_sistema'");
    }
}