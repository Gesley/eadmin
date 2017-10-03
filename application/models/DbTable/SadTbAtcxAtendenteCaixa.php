<?php
class Application_Model_DbTable_SadTbAtcxAtendenteCaixa extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_ATCX_ATENDENTE_CAIXA';
    protected $_primary = array('ATCX_ID_CAIXA_ENTRADA', 'ATCX_CD_MATRICULA');
 
    
//    public function getAtendentesCaixaTodos()
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT ATCX_ID_CAIXA_ENTRADA,
//                                   ATCX_CD_MATRICULA,
//                                   CXEN_DS_CAIXA_ENTRADA DSC_CAIXA_ENTRADA,
//                                   PMAT_CD_MATRICULA||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PMAT_CD_MATRICULA) ATENDENTE,
//                                   DECODE(ATCX_IC_ATIVIDADE,'S','SIM','N','NÃO') IC_ATIVIDADE
//                            FROM SAD_TB_ATCX_ATENDENTE_CAIXA,
//                                 SAD_TB_CXEN_CAIXA_ENTRADA,
//                                 OCS_TB_PMAT_MATRICULA
//                            WHERE ATCX_ID_CAIXA_ENTRADA = CXEN_ID_CAIXA_ENTRADA
//                            AND ATCX_CD_MATRICULA = PMAT_CD_MATRICULA
//                            ORDER BY ATCX_ID_CAIXA_ENTRADA,
//                                     SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PMAT_CD_MATRICULA)");
//        return $stmt->fetchAll();
//    }
    
    public function getAtendentesCaixa($idCaixa = null, $codMat = null, $ordem = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT ATCX_ID_CAIXA_ENTRADA,
                         ATCX_CD_MATRICULA,
                         CXEN_DS_CAIXA_ENTRADA DSC_CAIXA_ENTRADA,
                         PMAT_CD_MATRICULA||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PMAT_CD_MATRICULA) ATENDENTE,
                         SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PMAT_CD_MATRICULA)||' - '||PMAT_CD_MATRICULA NOME_ATENDENTE,
                         DECODE(ATCX_IC_ATIVIDADE,'S','SIM','N','NÃO ') IC_ATIVIDADE
                  FROM SAD_TB_ATCX_ATENDENTE_CAIXA,
                       SAD_TB_CXEN_CAIXA_ENTRADA,
                       OCS_TB_PMAT_MATRICULA
                  WHERE ATCX_ID_CAIXA_ENTRADA = CXEN_ID_CAIXA_ENTRADA
                  AND ATCX_CD_MATRICULA = PMAT_CD_MATRICULA";
     
      if (!empty($codMat)){
        $query .= " AND ATCX_CD_MATRICULA = '$codMat' ";
      } 
      if (!empty($idCaixa)){
        $query .= " AND ATCX_ID_CAIXA_ENTRADA = $idCaixa ";
      }
      if (!empty($ordem)){
        $query .= " ORDER BY $ordem";
      }else {
        $query .= " ORDER BY NOME_ATENDENTE ASC ";
      } 
      
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    }
    
    /*função utilizada nos encaminhamentos*/
    public function getPessoasCaixa($idCaixa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT ATCX_ID_CAIXA_ENTRADA,
                                   ATCX_CD_MATRICULA,
                                   CXEN_DS_CAIXA_ENTRADA DSC_CAIXA_ENTRADA,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PMAT_CD_MATRICULA) ATENDENTE,
                                   DECODE(ATCX_IC_ATIVIDADE,'S','SIM','N','NÃO') IC_ATIVIDADE,
                                   PMAT_CD_MATRICULA
                            FROM SAD_TB_ATCX_ATENDENTE_CAIXA,
                                 SAD_TB_CXEN_CAIXA_ENTRADA,
                                 OCS_TB_PMAT_MATRICULA
                            WHERE ATCX_ID_CAIXA_ENTRADA = CXEN_ID_CAIXA_ENTRADA
                            AND ATCX_CD_MATRICULA = PMAT_CD_MATRICULA
                            AND ATCX_IC_ATIVIDADE = 'S'
                            AND CXEN_ID_CAIXA_ENTRADA = $idCaixa
                            ORDER BY ATCX_ID_CAIXA_ENTRADA,
                                     SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PMAT_CD_MATRICULA)");
        return $stmt->fetchAll();
    }
    
    public function getNomeAtendenteAjax($matriculanome, $idCaixa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL 
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      M.PMAT_CD_MATRICULA||P.PNAT_NO_PESSOA LIKE UPPER('%$matriculanome%')
                            AND        M.PMAT_DT_FIM IS NULL
                            AND M.PMAT_CD_MATRICULA NOT IN (SELECT ATCX_CD_MATRICULA
                                                            FROM SAD_TB_ATCX_ATENDENTE_CAIXA,
                                                                 SAD_TB_CXEN_CAIXA_ENTRADA
                                                            WHERE ATCX_ID_CAIXA_ENTRADA = CXEN_ID_CAIXA_ENTRADA
                                                            AND ATCX_ID_CAIXA_ENTRADA = $idCaixa)
                            ORDER BY   P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }
 }
?>
