<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SadTbCdpaContDistProcAdm
 *
 * @author TR17358PS
 */

class Application_Model_DbTable_SadTbCdpaContDistProcAdm extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_CDPA_CONT_DIST_PROC_ADM';
    protected $_primary = array('CDPA_CD_ORGAO_JULGADOR','CDPA_CD_JUIZ');
  
    public function sorteioProcessoAdm($orgao, $promocao, $id_processo=null){
            
        //busca matricula do excluido da distribuicao
        $matExcluido = null;
        if($id_processo!=null){
            $hdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
            $arrayMatRelatores = $hdpaHistDistribuicao->relatoresProcesso($id_processo);
            
            if($arrayMatRelatores!=null){
                $arrayMatRelador = array_pop($arrayMatRelatores);
                if ($arrayMatRelador['HDPA_CD_ORGAO_JULGADOR'] == 1000 || $arrayMatRelador['HDPA_CD_ORGAO_JULGADOR'] == 2000 || $arrayMatRelador['HDPA_CD_ORGAO_JULGADOR'] == 3000) {
                    $matExcluido = $arrayMatRelador['HDPA_CD_JUIZ'];
                }else{
                    $matExcluido = $arrayMatRelador['HDPA_CD_SERVIDOR'];
                }
            }
        }
        if ($orgao == 1000 || $orgao == 2000 || $orgao == 3000) {
            $candidatos = $this->getDesembargadoresSorteio($orgao, $promocao, $matExcluido) ;    
            
        } else {
            $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
            $candidatos = $sadTbCcpaContDistComissao->getServComissoesSorteio($orgao, $id_processo, $matExcluido);
        }
        
        $candidato = $candidatos[array_rand($candidatos)];    
        if($matExcluido==null){
            $candidato['HDPA_IC_FORMA_DISTRIBUICAO'] = 'DA';
        }else{
            $candidato['HDPA_IC_FORMA_DISTRIBUICAO'] = 'RA';
        }
        return $candidato;
    } 
    
    public function getMembros($order,$cdpa_cd_orgao_julgador)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PMAT.PMAT_CD_MATRICULA, 
                                    PNAT.PNAT_NO_PESSOA,
                                    CDPA.CDPA_IC_DISTRIBUICAO,
                                    CDPA.CDPA_IC_ATIVO,
                                    ORGJ.ORGJ_NM_ORGAO_JULGADOR,
                                    CDPA.CDPA_IC_PLENARIO
                             FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM CDPA, 
                                    SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ,
                                    OCS_TB_PMAT_MATRICULA PMAT,
                                    OCS_TB_PNAT_PESSOA_NATURAL PNAT
                             WHERE CDPA.CDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA
                                    AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                    AND PMAT.PMAT_DT_FIM IS NULL
                                    AND CDPA.CDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR
                                    AND CDPA.CDPA_CD_ORGAO_JULGADOR = '$cdpa_cd_orgao_julgador'
                             ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getDesembargadoresSorteio($cdpa_cd_orgao_julgador,$cdpa_ic_plenario, $matExcluido=null)
    {
        if($cdpa_ic_plenario==null){
            $cdpa_ic_plenario='N';
        }
        if($this->getDisponivelDistribuicao($cdpa_cd_orgao_julgador,$cdpa_ic_plenario) == 0){
            
            $this->getModificaDistribuicao($cdpa_cd_orgao_julgador,$cdpa_ic_plenario);
            
        }
        
        $completa='';
        if($matExcluido!=null && $matExcluido=!''){
            $completa = "AND A.CDPA_CD_JUIZ<>'$matExcluido'";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        if($cdpa_ic_plenario == 'S'){
        $stmt = $db->query("SELECT A.CDPA_CD_JUIZ MATRICULA,
                                   P.PNAT_NO_PESSOA NOME, 
                                   M.PMAT_CD_UNIDADE_LOTACAO COD_LOTACAO,
                                   M.PMAT_SG_SECSUBSEC_LOTACAO SIGLA_SECAO
                              FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM A,
                                   OCS_TB_PMAT_MATRICULA M,
                                   OCS_TB_PNAT_PESSOA_NATURAL P
                             WHERE M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA 
                               $completa
                               AND M.PMAT_CD_MATRICULA = A.CDPA_CD_JUIZ
                               AND A.CDPA_IC_ATIVO = 'S'
                               AND A.CDPA_IC_DISTRIBUICAO = 'N'
                               AND M.PMAT_DT_FIM IS NULL
                               AND RH_RETORNA_INDISPONIVEL(A.CDPA_CD_JUIZ,SYSDATE) = 0
                               AND A.CDPA_CD_ORGAO_JULGADOR = '$cdpa_cd_orgao_julgador'
                               AND A.CDPA_IC_PLENARIO = '$cdpa_ic_plenario'
                               AND NVL(A.CDPA_QT_DEVOLVIDO_JUIZ,0) = (SELECT MAX(NVL(B.CDPA_QT_DEVOLVIDO_JUIZ,0))
                                                                      FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM B
                                                                     WHERE B.CDPA_CD_ORGAO_JULGADOR = A.CDPA_CD_ORGAO_JULGADOR
                                                                       AND B.CDPA_IC_ATIVO = 'S')");
        } else {
            $stmt = $db->query("SELECT A.CDPA_CD_JUIZ MATRICULA,
                                   P.PNAT_NO_PESSOA NOME, 
                                   M.PMAT_CD_UNIDADE_LOTACAO COD_LOTACAO,
                                   M.PMAT_SG_SECSUBSEC_LOTACAO SIGLA_SECAO
                              FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM A,
                                   OCS_TB_PMAT_MATRICULA M,
                                   OCS_TB_PNAT_PESSOA_NATURAL P
                             WHERE M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA 
                               $completa
                               AND M.PMAT_CD_MATRICULA = A.CDPA_CD_JUIZ
                               AND A.CDPA_IC_ATIVO = 'S'
                               AND A.CDPA_IC_DISTRIBUICAO = 'N'
                               AND M.PMAT_DT_FIM IS NULL
                               AND RH_RETORNA_INDISPONIVEL(A.CDPA_CD_JUIZ,SYSDATE) = 0
                               AND A.CDPA_CD_ORGAO_JULGADOR = '$cdpa_cd_orgao_julgador'
                               AND NVL(A.CDPA_QT_DEVOLVIDO_JUIZ,0) = (SELECT MAX(NVL(B.CDPA_QT_DEVOLVIDO_JUIZ,0))
                                                                      FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM B
                                                                     WHERE B.CDPA_CD_ORGAO_JULGADOR = A.CDPA_CD_ORGAO_JULGADOR
                                                                       AND B.CDPA_IC_ATIVO = 'S')");

        
        }
        
        return $stmt->fetchAll();
    }
    
    private function getModificaDistribuicao($cdpa_cd_orgao_julgador,$cdpa_ic_plenario = "N")
    {
        
        if($cdpa_ic_plenario == "S"){
          $where = "CDPA_IC_PLENARIO = $cdpa_ic_plenario AND CDPA_CD_ORGAO_JULGADOR = $cdpa_cd_orgao_julgador";
        }else{
          $where = "CDPA_CD_ORGAO_JULGADOR = $cdpa_cd_orgao_julgador";
        }
        
          $data = array('CDPA_IC_DISTRIBUICAO' => "N");
          $this->update($data, $where);

    }

    private function getDisponivelDistribuicao($cdpa_cd_orgao_julgador,$cdpa_ic_plenario = "N")
    {
        
         $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         
         if ($cdpa_ic_plenario == "S"){
         $stmt = $db->query("SELECT COUNT(*) TOTAL 
                               FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM, OCS_TB_PMAT_MATRICULA
                              WHERE PMAT_CD_MATRICULA = CDPA_CD_JUIZ
                                AND PMAT_DT_FIM IS NULL
                                AND CDPA_CD_ORGAO_JULGADOR = '$cdpa_cd_orgao_julgador'
                                AND CDPA_IC_PLENARIO = '$cdpa_ic_plenario'
                                AND CDPA_IC_DISTRIBUICAO = 'N'
                                AND CDPA_IC_ATIVO = 'S'");
             
         }else{
         $stmt = $db->query("SELECT COUNT(*) TOTAL 
                               FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM, OCS_TB_PMAT_MATRICULA
                              WHERE PMAT_CD_MATRICULA = CDPA_CD_JUIZ
                                AND PMAT_DT_FIM IS NULL
                                AND CDPA_CD_ORGAO_JULGADOR = '$cdpa_cd_orgao_julgador'
                                AND CDPA_IC_DISTRIBUICAO = 'N'
                                AND CDPA_IC_ATIVO = 'S'");
         }
         
         $row = $stmt->fetch();
         
         return $row[TOTAL];
         
    }
    
    private function getRetiraDaDistribuicao($cdpa_cd_orgao_julgador,$cdpa_cd_juiz){
                
          $where = "CDPA_CD_JUIZ = $cdpa_cd_juiz AND CDPA_CD_ORGAO_JULGADOR = $cdpa_cd_orgao_julgador";
          $data = array('CDPA_IC_DISTRIBUICAO' => "S");
          $this->update($data, $where);
    }
    
}

?>