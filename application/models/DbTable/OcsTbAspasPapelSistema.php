<?php
class Application_Model_DbTable_OcsTbAspasPapelSistema extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_APAS_PAPEL_SISTEMA';
    protected $_primary = 'ASPAS_ID_PAPEL_SISTEMA';

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
    
    public function getResourcesAclNivelController()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT LOWER(M.MODL_NM_MODULO) MODL_NM_MODULO, LOWER(C.CTRL_NM_CONTROLE_SISTEMA) CTRL_NM_CONTROLE_SISTEMA
                            FROM OCS_TB_CTRL_CONTROLE_SISTEMA C
                            INNER JOIN OCS_TB_MODL_MODULO M
                            ON M.MODL_ID_MODULO = C.CTRL_ID_MODULO
                            ORDER BY 1, 2");
        return $stmt->fetchAll();
    }
    
    public function getResourcesAclNivelAction()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT LOWER(MODL.MODL_NM_MODULO) MODL_NM_MODULO, LOWER(CTRL.CTRL_NM_CONTROLE_SISTEMA) CTRL_NM_CONTROLE_SISTEMA, LOWER(ACAO_NM_ACAO_SISTEMA) ACAO_NM_ACAO_SISTEMA
                            FROM  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO
                            WHERE CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            ORDER BY 1, 2, 3 ");
        return $stmt->fetchAll();
    }
    
    public function getPermissionsAclNivelController($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT MODL.MODL_NM_MODULO, CTRL.CTRL_NM_CONTROLE_SISTEMA
                            FROM  SISTEMAS_TRF SIS,
                                  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                  OCS_TB_PAPL_PAPEL PAPL,
                                  OCS_TB_PSPA_PERFIL_PAPEL PSPA,
                                  OCS_TB_PERF_PERFIL  PERF,
                                  OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                  OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
                            WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   PAPL.PAPL_ID_PAPEL =  PSPA.PSPA_ID_PAPEL
                            AND   PSPA.PSPA_ID_PERFIL = PERF.PERF_ID_PERFIL
                            AND   PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                            AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                            AND   PUPE.PUPE_CD_MATRICULA = '$matricula'
                            ORDER BY 1, 2");
        return $stmt->fetchAll(); 
    }
    
    
    public function getPermissionsAclNivelAction($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT LOWER(MODL.MODL_NM_MODULO) MODL_NM_MODULO, LOWER(CTRL.CTRL_NM_CONTROLE_SISTEMA) CTRL_NM_CONTROLE_SISTEMA, LOWER(ACAO_NM_ACAO_SISTEMA) ACAO_NM_ACAO_SISTEMA
                            FROM  SISTEMAS_TRF SIS,
                                  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                  OCS_TB_PAPL_PAPEL PAPL,
                                  OCS_TB_PSPA_PERFIL_PAPEL PSPA,
                                  OCS_TB_PERF_PERFIL  PERF,
                                  OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                  OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
                            WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   PAPL.PAPL_ID_PAPEL =  PSPA.PSPA_ID_PAPEL
                            AND   PSPA.PSPA_ID_PERFIL = PERF.PERF_ID_PERFIL
                            AND   PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                            AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                            AND   PUPE.PUPE_CD_MATRICULA = '$matricula'
                            ORDER BY 1, 2, 3");
        return $stmt->fetchAll(); 
    }

    public function getNotAccess($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MODL.MODL_NM_MODULO, CTRL.CTRL_NM_CONTROLE_SISTEMA, ACAO.ACAO_NM_ACAO_SISTEMA
                            FROM  SISTEMAS_TRF SIS,
                                  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                  OCS_TB_PAPL_PAPEL PAPL
                            WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   (MODL.MODL_NM_MODULO, CTRL.CTRL_NM_CONTROLE_SISTEMA, ACAO.ACAO_NM_ACAO_SISTEMA) NOT IN
                            (SELECT MODL.MODL_NM_MODULO, CTRL.CTRL_NM_CONTROLE_SISTEMA, ACAO.ACAO_NM_ACAO_SISTEMA
                            FROM  SISTEMAS_TRF SIS,
                                  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                  OCS_TB_PAPL_PAPEL PAPL,
                                  OCS_TB_PSPA_PERFIL_PAPEL PSPA,
                                  OCS_TB_PERF_PERFIL  PERF,
                                  OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                  OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
                            WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   PAPL.PAPL_ID_PAPEL =  PSPA.PSPA_ID_PAPEL
                            AND   PSPA.PSPA_ID_PERFIL = PERF.PERF_ID_PERFIL
                            AND   PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                            AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                            AND   PUPE.PUPE_CD_MATRICULA = '$matricula')
                            AND   (MODL.MODL_NM_MODULO)  IN
                            (SELECT DISTINCT MODL.MODL_NM_MODULO
                            FROM SISTEMAS_TRF SIS,
                                  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO,
                                  OCS_TB_PAPL_PAPEL PAPL,
                                  OCS_TB_PSPA_PERFIL_PAPEL PSPA,
                                  OCS_TB_PERF_PERFIL  PERF,
                                  OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                  OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
                            WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                            AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                            AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                            AND   PAPL.PAPL_ID_PAPEL =  PSPA.PSPA_ID_PAPEL
                            AND   PSPA.PSPA_ID_PERFIL = PERF.PERF_ID_PERFIL
                            AND   PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                            AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                            AND   PUPE.PUPE_CD_MATRICULA = '$matricula')");
        return $stmt->fetchAll(); 
    }
    
    public function getAppsCadastradas()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MODL.MODL_NM_MODULO, CTRL.CTRL_NM_CONTROLE_SISTEMA, ACAO.ACAO_NM_ACAO_SISTEMA
                            FROM  OCS_TB_MODL_MODULO MODL,
                                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                  OCS_TB_ACAO_ACAO_SISTEMA ACAO
                            WHERE ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                            AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO");
        $retorno = $stmt->fetchAll();
        return $retorno; 
    }

}