<?php
/**
 * Realiza as manutenções das tarefas.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Facade_Tarefa
{

    protected $_mapperTarefa = "";
    protected $_db = "";
    protected $_anexoRed = "";

    public function __construct() 
    {
        $this->_anexoRed = new Sosti_Model_DataMapper_AnexoRed();
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_mapperTarefa = new Tarefa_Model_DataMapper_Tarefa();
        $this->_mapperTarefaSolicitacao = new Tarefa_Model_DataMapper_TarefaSolicitacao();
    }

    public function listAll($solicitcao, $order)
    {
        return $this->_mapperTarefa->listAll($solicitcao, $order);
    }
    
    public function getById($id)
    {
        return $this->_mapperTarefa->getById($id);
    }

    public function adicionar($data)
    {
        $this->_db->beginTransaction();
        try {
            $dataTarefaSolicit = array();
            $anexTarefaSolicit = array();
            /**
             * Insere na tabela da tarefa
             */
            $idTarefa = $this->_mapperTarefa->add($data);
            /**
             * Insere na tabela que vincula as tarefas as solicitações
             */
            $dataTarefaSolicit['TASO_ID_DOCUMENTO'] = $data['ID_DOCUMENTO'];
            $dataTarefaSolicit['TASO_ID_TAREFA'] = $idTarefa;
            $dataTarefaSolicit['TASO_IC_SITUACAO_NEGOCIACAO'] = 5;
            $arrayStatus['add'] = $this->_mapperTarefaSolicitacao->add($dataTarefaSolicit);
            /**
             * Grava na tabela do Red os documentos que foram inseridos.
             */
            $arrayStatus['gravaRed'] = $this->_anexoRed->setGavaRed($data["NR_DOCS_RED"], $data["ID_DOCUMENTO"], $data["MOFA_ID_MOVIMENTACAO"]);
            /**
             * Grava na tabela de anexos das tarefas das solicitações.
             */
            foreach ($arrayStatus['gravaRed']['save'] as $ts) {
                $anexTarefaSolicit['ANTS_ID_TAREFA_SOLICIT'] = $arrayStatus['add'];
                $anexTarefaSolicit['ANTS_ID_DOCUMENTO_INTERNO'] = $ts['ANEX_ID_ANEXO'];
                $anexTarefaSolicit['ANTS_IC_INPUT_ANEXO'] = 'C';
                $arrayStatus['anexTarefa'][] = $this->_anexoRed->setGravaAnexTarefaSolicit($anexTarefaSolicit);
            }
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            $arrayStatus['exception'] = $e->getMessage();
        }
        return $arrayStatus;
    }
    
    public function editar($data)
    {
        $this->_db->beginTransaction();
        try {
            $arrayStatus = array();
            if ($data["PERFIL_USER"] == "gestao") {
                /** Dados da avaliação do solicitante */
                $data["TASO_IC_SITUACAO_NEGOCIACAO"] != "" ? $dataTarefaSolicit["TASO_IC_SITUACAO_NEGOCIACAO"] = $data["TASO_IC_SITUACAO_NEGOCIACAO"] : "";
                $data["TASO_IC_ACEITE_SOLICITANTE"] != "" ? $dataTarefaSolicit["TASO_IC_ACEITE_SOLICITANTE"] = $data["TASO_IC_ACEITE_SOLICITANTE"] : "";
                $data["TASO_IC_ACEITE_SOLICITANTE"] != "" ? $dataTarefaSolicit["TASO_DH_AVAL_SOLICITANTE"] = new Zend_Db_Expr("SYSDATE") : "";
                $data["TASO_DS_JUSTIF_SOLICITANTE"] != "" ? $dataTarefaSolicit["TASO_DS_JUSTIF_SOLICITANTE"] = $data["TASO_DS_JUSTIF_SOLICITANTE"] : "";
                $data["TASO_NR_DCMTO_ANEXO"] != "" ? $dataTarefaSolicit["TASO_NR_DCMTO_ANEXO"] = $data["TASO_NR_DCMTO_ANEXO"] : "";
            } else {
                /** Dados da avaliação do atendente */
                $data["TASO_IC_SITUACAO_NEGOCIACAO"] != "" ? $dataTarefaSolicit["TASO_IC_SITUACAO_NEGOCIACAO"] = $data["TASO_IC_SITUACAO_NEGOCIACAO"] : "";
                $data["TASO_CD_MATR_ATEND_TAREFA"] != "" ? $dataTarefaSolicit["TASO_CD_MATR_ATEND_TAREFA"] = $data["TASO_CD_MATR_ATEND_TAREFA"] : "";
                $data["TASO_IC_ACEITE_ATENDENTE"] != "" ? $dataTarefaSolicit["TASO_IC_ACEITE_ATENDENTE"] = $data["TASO_IC_ACEITE_ATENDENTE"] : "";
                $data["TASO_IC_ACEITE_ATENDENTE"] != "" ? $dataTarefaSolicit["TASO_DH_AVAL_DEFEITO"] = new Zend_Db_Expr("SYSDATE") : "";
                $data["TASO_DS_JUSTIF_ATENDENTE"] != "" ? $dataTarefaSolicit["TASO_DS_JUSTIF_ATENDENTE"] = $data["TASO_DS_JUSTIF_ATENDENTE"] : "";
                $data["TASO_NR_DCMTO_ANEXO"] != "" ? $dataTarefaSolicit["TASO_NR_DCMTO_ANEXO"] = $data["TASO_NR_DCMTO_ANEXO"] : "";
            }
            $data["TARE_ID_TAREFA"] != "" ? $dataTarefaSolicit["TASO_ID_TAREFA"] = $data["TARE_ID_TAREFA"] : "";
            $arrayStatus['edit'] = $this->_mapperTarefaSolicitacao->edit($dataTarefaSolicit);
            /**
             * Grava na tabela do Red os documentos que foram inseridos.
             */
            $arrayStatus['gravaRed'] = $this->_anexoRed->setGavaRed($data["NR_DOCS_RED"], $data["ID_DOCUMENTO"], $data["MOFA_ID_MOVIMENTACAO"]);
            /**
             * Grava na tabela de anexos das tarefas das solicitações.
             */
            $idTarefaSolicit = $this->_mapperTarefaSolicitacao->fetchAll(array(
                'TASO_ID_DOCUMENTO = '.$data['ID_DOCUMENTO'], 'TASO_ID_TAREFA = '.$data["TARE_ID_TAREFA"])
            );
            $inputAnexo = $data["ANEXOS_NEGOCIACAO_FABRICA"] != null ? 'F' : 'T';
            foreach ($arrayStatus['gravaRed']['save'] as $ts) {
                $anexTarefaSolicit['ANTS_ID_TAREFA_SOLICIT'] = $idTarefaSolicit[0]['TASO_ID_TAREFA_SOLICIT'];
                $anexTarefaSolicit['ANTS_ID_DOCUMENTO_INTERNO'] = $ts['ANEX_ID_ANEXO'];
                $anexTarefaSolicit['ANTS_IC_INPUT_ANEXO'] = $inputAnexo;
                $arrayStatus['anexTarefa'][] = $this->_anexoRed->setGravaAnexTarefaSolicit($anexTarefaSolicit);
            }
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            $arrayStatus['exception'] = $e->getMessage();
        }
        return $arrayStatus;
    }

    public function excluir($id) 
    {
        $this->_db->beginTransaction();
        try {
            $idTarefaSolicit = $this->_mapperTarefaSolicitacao->fetchAll(array(
                'TASO_ID_TAREFA = '.$id)
            );
            $this->_anexoRed->setDeleteTarefaSolicit($idTarefaSolicit[0]['TASO_ID_TAREFA_SOLICIT']);
            $this->_mapperTarefaSolicitacao->delete($id);
            $this->_mapperTarefa->delete($id);
            return $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        } 
    }
    
    public function listPorTarefa($id)
    {
        return $this->_mapperTarefa->listPorTipoTarefa($id);
    }

        public function __destruct() 
    {
        $this->_db->closeConnection();
    }
}
