<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbRepeRespPermissao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_REPE_RESP_PERMISSAO';
    protected $_primary = 'REPE_ID_RESP_PERMISSAO';
    protected $_sequence = 'OCS_SQ_REPE_RESP_PERMISSAO';
   
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
    
    public function getPerfisAssociados($cod_lotacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  UNPE_ID_UNIDADE_PERFIL,UNPE_SG_SECAO,UNPE_CD_LOTACAO,UNPE_ID_PERFIL,PERF_DS_PERFIL
                                FROM  OCS_TB_UNPE_UNIDADE_PERFIL A, OCS_TB_PERF_PERFIL B
                                WHERE A.UNPE_ID_PERFIL = B.PERF_ID_PERFIL
                                AND A.UNPE_CD_LOTACAO = $cod_lotacao");
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