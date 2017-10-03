<?php

/**
 * Realiza ações na tabela que registra as inserções dos documentos no Red.
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_AnexoRed extends Zend_Db_Table_Abstract
{
    protected $_dbTable;

    public function __construct() 
    {
        $this->setDbTable(new Tarefa_Model_DbTable_SosTbAntsAnexTarefaSolicit());
    }
    
    private function setDbTable(Tarefa_Model_DbTable_SosTbAntsAnexTarefaSolicit $dbtable) 
    {
        $this->_dbTable = $dbtable;
    }

    private function getDbTable()
    {
        return $this->_dbTable;
    }
    
    /**
     * Grava na tabela de anexos das tarefas das solicitações
     */
    public function setGravaAnexTarefaSolicit($data = array())
    {
        return $this->getDbTable()->insert($data);
    }
    
    public function setGavaRed(
        $nrDocsRed = array(), $idDocmDocumento, $idMoviMovimentacao
    )
    {
        $q = "SELECT * FROM (
                SELECT TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE
                FROM SAD.SAD_TB_MOFA_MOVI_FASE
                WHERE MOFA_ID_MOVIMENTACAO = $idMoviMovimentacao
                ORDER BY MOFA_DH_FASE DESC
             ) WHERE ROWNUM = 1";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dataFase = $db->fetchRow($q);
        try {
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE'] = new Zend_Db_Expr("TO_CHAR('$dataFase->MOFA_DH_FASE','dd/mm/yyyy HH24:MI:SS')");
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            if ($nrDocsRed) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $arrayResult['save'][] = $rowAnexAnexo;
                    $rowAnexAnexo->save();
                }
            }
            return $arrayResult;
        } catch (Exception $ex) {
            $arrayResult['exception'] = $ex->getMessage();
        }
        return $arrayResult;
    }
    
    public function setDeleteTarefaSolicit($ids) 
    {
        return $this->getDbTable()->delete('ANTS_ID_TAREFA_SOLICIT IN ('.$ids.')');
    }
}