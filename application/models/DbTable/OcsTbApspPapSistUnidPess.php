<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbApspPapSistUnidPess extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_APSP_PAP_SIST_UNID_PESS';
    protected $_primary = 'APSP_ID_PAPEL_SIST_UNID_PESS';
    protected $_sequence = 'OCS_SQ_APSP';
    
    /* Permissão de cadastro de solicitação */
    public function getPermissaoCadastroSolicitacao($user, $uf)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT APSU_CD_UNIDADE
		            FROM   OCS_TB_APAS_PAPEL_SISTEMA, 
	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
	            	           OCS_TB_PMAT_MATRICULA,
	            	           RH_CENTRAL_LOTACAO	             
		            WHERE  APAS_ID_PAPEL_SISTEMA = 1
		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
		            AND    PMAT_CD_MATRICULA     = '$user'
		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
        $unidade = $stmt->fetchAll();
        return $unidade[0]["APSU_CD_UNIDADE"];
    }

    /* Permissão de help-desk */
    public function getPermissaoHelpDesk($user, $uf)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT APSU_CD_UNIDADE
		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
	            	           OCS_TB_PMAT_MATRICULA,
	            	           RH_CENTRAL_LOTACAO
		            WHERE  APAS_ID_PAPEL_SISTEMA = 3
		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
		            AND    PMAT_CD_MATRICULA     = '$user'
		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
        $unidade = $stmt->fetchAll();
        return $unidade[0]["APSU_CD_UNIDADE"];
    }

    /* Permissão de cadastro de tabelas básicas */
    public function getPermissaoCadastroTabelasBasicas($user, $uf)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT APSU_CD_UNIDADE
		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
	            	           OCS_TB_PMAT_MATRICULA,
	            	           RH_CENTRAL_LOTACAO
		            WHERE  APAS_ID_PAPEL_SISTEMA = 4
		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
		            AND    PMAT_CD_MATRICULA     = '$user'
		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
        $unidade = $stmt->fetchAll();
        return $unidade[0]["APSU_CD_UNIDADE"];
    }

    /* Permissão de gestor */
    public function getPermissaoGestor($user, $uf)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT APSU_CD_UNIDADE
		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
	            	           OCS_TB_PMAT_MATRICULA,
	            	           RH_CENTRAL_LOTACAO
		            WHERE  APAS_ID_PAPEL_SISTEMA = 6
		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
		            AND    PMAT_CD_MATRICULA     = '$user'
		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
        $unidade = $stmt->fetchAll();
        return $unidade[0]["APSU_CD_UNIDADE"];
    }

    /* Permissão de preposto */
    public function getPermissaoPreposto($user, $uf)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT APSU_CD_UNIDADE
		            FROM   OCS_TB_APAS_PAPEL_SISTEMA,
	    	                   OCS_TB_APSU_PAPEL_SIST_UNIDADE,
	        	           OCS_TB_APSP_PAP_SIST_UNID_PESS,
	            	           OCS_TB_PMAT_MATRICULA,
	            	           RH_CENTRAL_LOTACAO
		            WHERE  APAS_ID_PAPEL_SISTEMA = 7
		            AND    APAS_ID_PAPEL_SISTEMA = APSU_ID_PAPEL_SISTEMA
		            AND    APSU_ID_PAPEL_SISTEMA_UNIDADE = APSP_ID_PAPEL_SIST_UNID
		            AND    APSU_SG_SECAO_UNIDADE = LOTA_SIGLA_SECAO
		            AND    APSU_CD_UNIDADE       = LOTA_COD_LOTACAO
	    	            AND    APSP_ID_PESSOA        = PMAT_ID_PESSOA
		            AND    PMAT_CD_MATRICULA     = '$user'
		            AND    APSU_SG_SECAO_UNIDADE = '$uf'
		            ORDER BY LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO");
        $unidade = $stmt->fetchAll();
        return $unidade[0]["APSU_CD_UNIDADE"];
    }
    /* */
    public function getSistemas()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'SOSTI' 
                                OR NOME_SISTEMA ='SISAD'");
        return $stmt->fetchAll();
    }
    
   public function getUnidadePessoa($lota_cod_lotacao, $pmat_id_pessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT C.APSU_ID_PAPEL_SISTEMA_UNIDADE,B.APAS_SG_SISTEMA, A.APAP_NM_PAPEL,A.APAP_DS_FINALIDADE, D.APSP_ID_PAPEL_SIST_UNID  
                               FROM  OCS_TB_APAP_PAPEL A, OCS_TB_APAS_PAPEL_SISTEMA B, OCS_TB_APSU_PAPEL_SIST_UNIDADE C, OCS_TB_APSP_PAP_SIST_UNID_PESS D
	 		   WHERE A.APAP_ID_PAPEL = B.APAS_ID_PAPEL
                               AND B.APAS_ID_PAPEL_SISTEMA = C.APSU_ID_PAPEL_SISTEMA
                               AND C.APSU_ID_PAPEL_SISTEMA_UNIDADE = D.APSP_ID_PAPEL_SIST_UNID(+)
                               AND C.APSU_CD_UNIDADE = $lota_cod_lotacao 
                               AND D.APSP_ID_PESSOA(+) = $pmat_id_pessoa
                           ORDER BY B.APAS_SG_SISTEMA");
        return $stmt->fetchAll();       
    }
    
    public function getUnidadePessoaBySistema($lota_cod_lotacao, $pmat_id_pessoa, $sigla)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT C.APSU_ID_PAPEL_SISTEMA_UNIDADE,B.APAS_SG_SISTEMA, A.APAP_NM_PAPEL,A.APAP_DS_FINALIDADE, D.APSP_ID_PAPEL_SIST_UNID  
                               FROM  OCS_TB_APAP_PAPEL A, OCS_TB_APAS_PAPEL_SISTEMA B, OCS_TB_APSU_PAPEL_SIST_UNIDADE C, OCS_TB_APSP_PAP_SIST_UNID_PESS D
	 		   WHERE A.APAP_ID_PAPEL = B.APAS_ID_PAPEL
                               AND B.APAS_ID_PAPEL_SISTEMA = C.APSU_ID_PAPEL_SISTEMA
                               AND C.APSU_ID_PAPEL_SISTEMA_UNIDADE = D.APSP_ID_PAPEL_SIST_UNID(+)
                               AND C.APSU_CD_UNIDADE = $lota_cod_lotacao 
                               AND D.APSP_ID_PESSOA(+) = $pmat_id_pessoa
                               AND B.APAS_SG_SISTEMA = '$sigla'
                            ORDER BY B.APAS_SG_SISTEMA");
        return $stmt->fetchAll();       
    }
    
    public function getPessoa($lota_cod_lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA,  B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA 
					FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
					WHERE A.PMAT_CD_UNIDADE_LOTACAO = $lota_cod_lotacao
					AND  A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
                                        AND A.PMAT_DT_FIM IS NULL
					ORDER BY B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }
    
    public function getUnidade($lota_cod_lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO, LOTA_COD_LOTACAO 
						FROM  RH_CENTRAL_LOTACAO
						ORDER BY 1");
        return $stmt->fetchAll();
    }
    
    public function getDeletar($apsp_id_papel_sist_unid,$pmat_id_pessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_APSP_PAP_SIST_UNID_PESS
                		WHERE APSP_ID_PAPEL_SIST_UNID = $apsp_id_papel_sist_unid
               			AND APSP_ID_PESSOA = $pmat_id_pessoa");
    }
    
    public function getInserir($apsp_id_papel_sist_unid,$pmat_id_pessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("INSERT INTO OCS_TB_APSP_PAP_SIST_UNID_PESS(APSP_ID_PAPEL_SIST_UNID,APSP_ID_PESSOA)
           				VALUES($apsp_id_papel_sist_unid,$pmat_id_pessoa)");
    }

}