<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SadTbCcpaContDistComissao
 *
 * @author TR17358PS
 */
class Application_Model_DbTable_SadTbCcpaContDistComissao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_CCPA_CONT_DIST_COMISSAO';
    protected $_primary = array('CCPA_CD_ORGAO_JULGADOR','CCPA_CD_SERVIDOR');
    
    public function getMembros($order,$ccpa_cd_orgao_julgador)
    {
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PMAT.PMAT_CD_MATRICULA, 
                                   PNAT.PNAT_NO_PESSOA,
                                   CCPA.CCPA_IC_DISTRIBUICAO,
                                   CCPA.CCPA_IC_ATIVO,
                                   ORGJ.ORGJ_NM_ORGAO_JULGADOR    
                            FROM SAD_TB_CCPA_CONT_DIST_COMISSAO CCPA, 
                                   SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                   OCS_TB_PMAT_MATRICULA PMAT,
                                   OCS_TB_PNAT_PESSOA_NATURAL PNAT
                            WHERE CCPA.CCPA_CD_SERVIDOR = PMAT.PMAT_CD_MATRICULA
                                   AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                   AND CCPA.CCPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR
                                   AND PMAT.PMAT_DT_FIM IS NULL
                                   AND CCPA.CCPA_CD_ORGAO_JULGADOR = '$ccpa_cd_orgao_julgador'
                            ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getServComissoesSorteio($ccpa_cd_orgao_julgador,$id_processo, $matExcluido=null)
    {
        if($this->getDisponivelDistribuicao($ccpa_cd_orgao_julgador, $id_processo) == 0){
            
            $this->getModificaDistribuicao($ccpa_cd_orgao_julgador, $id_processo);
            
        }
        $completa = '';
        if($matExcluido!=null && $matExcluido=!''){
            $completa = "AND A.CCPA_CD_SERVIDOR<>'$matExcluido'";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        
        $stmt = $db->query("SELECT A.CCPA_CD_SERVIDOR MATRICULA,
                                   P.PNAT_NO_PESSOA NOME, 
                                   M.PMAT_CD_UNIDADE_LOTACAO COD_LOTACAO,
                                   M.PMAT_SG_SECSUBSEC_LOTACAO SIGLA_SECAO
                              FROM SAD_TB_CCPA_CONT_DIST_COMISSAO A,
                                   OCS_TB_PMAT_MATRICULA M,
                                   OCS_TB_PNAT_PESSOA_NATURAL P
                             WHERE M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA 
                               $completa
                               AND M.PMAT_CD_MATRICULA = A.CCPA_CD_SERVIDOR
                               AND A.CCPA_IC_ATIVO = 'S'
                               AND A.CCPA_IC_DISTRIBUICAO = 'N'
                               AND PMAT_DT_FIM IS NULL
                               AND RH_RETORNA_INDISPONIVEL(A.CCPA_CD_SERVIDOR,SYSDATE) = 0
                               AND A.CCPA_CD_ORGAO_JULGADOR = '$ccpa_cd_orgao_julgador'
                               AND NVL(A.CCPA_QT_DEVOLVIDO_SERVIDOR,0) = (SELECT MAX(NVL(B.CCPA_QT_DEVOLVIDO_SERVIDOR,0))
                                                                            FROM SAD_TB_CCPA_CONT_DIST_COMISSAO B
                                                                           WHERE B.CCPA_CD_ORGAO_JULGADOR = A.CCPA_CD_ORGAO_JULGADOR
                                                                             AND B.CCPA_IC_ATIVO = 'S')
                               AND A.CCPA_CD_SERVIDOR NOT IN (
                                   SELECT IMDI_CD_MATRICULA_SERVIDOR
                                   FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                                   WHERE IMDI_ID_PROCESSO_DIGITAL = '$id_processo'
                                         AND IMDI_CD_COMISSAO = '$ccpa_cd_orgao_julgador')
                                ");
        
        return $stmt->fetchAll();
    }
    
    private function getModificaDistribuicao($ccpa_cd_orgao_julgador, $id_processo)
    {
        
          $where = "CCPA_CD_ORGAO_JULGADOR = '$ccpa_cd_orgao_julgador' 
                    AND CCPA_CD_SERVIDOR NOT IN (
                        SELECT IMDI_CD_MATRICULA_SERVIDOR
                        FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                        WHERE IMDI_ID_PROCESSO_DIGITAL = '$id_processo'
                              AND IMDI_CD_COMISSAO = '$ccpa_cd_orgao_julgador'
                    )";
          $data = array('CCPA_IC_DISTRIBUICAO' => "N");
          $this->update($data, $where);

    }
       
    private function getDisponivelDistribuicao($ccpa_cd_orgao_julgador, $id_processo)
    {
        
         $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         $stmt = $db->query("SELECT COUNT(*) 
                               FROM SAD_TB_CCPA_CONT_DIST_COMISSAO
                               , OCS_TB_PMAT_MATRICULA
                               , SAD_TB_IMDI_IMPEDE_DISTRIBUI
                              WHERE PMAT_CD_MATRICULA = CCPA_CD_SERVIDOR
                                AND IMDI_CD_MATRICULA_SERVIDOR = CCPA_CD_SERVIDOR
                                AND IMDI_CD_COMISSAO = CCPA_CD_ORGAO_JULGADOR
                                AND PMAT_DT_FIM IS NULL 
                                AND CCPA_CD_ORGAO_JULGADOR = '$ccpa_cd_orgao_julgador'                                                                             
                                AND CCPA_IC_DISTRIBUICAO = 'N'
                                AND CCPA_CD_SERVIDOR NOT IN (
                                    SELECT IMDI_CD_MATRICULA_SERVIDOR
                                    FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                                    WHERE IMDI_ID_PROCESSO_DIGITAL = '$id_processo'
                                          AND IMDI_CD_COMISSAO = '$ccpa_cd_orgao_julgador'
                                )");
         
    }
    
}
?>
