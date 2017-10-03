<?php
/**
 * Gerencia os tipos de tarefas.
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_DataMapper_TipoTarefa extends Zend_Db_Table_Abstract
{
   
    protected $_dbTable;
    protected $_userNs;

    public function __construct()
    {
        $this->setDbTable(new Tarefa_Model_DbTable_SosTbTptaTipoTarefa);
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    private function setDbTable(Tarefa_Model_DbTable_SosTbTptaTipoTarefa $dbtable)
    {
        $this->_dbTable = $dbtable;
    }

    private function getDbTable()
    {
        return $this->_dbTable;
    }
    
    public function add($data)
    {
        $data['TPTA_DH_CADASTRO']  = new Zend_Db_Expr('SYSDATE');
        $data['TPTA_CD_MATRICULA'] = $this->_userNs->matricula;
        $data['TPTA_IC_EXCLUIDO']  = 'N';
        unset($data['Salvar']);
        unset($data['TPTA_ID_TIPO_TAREFA']);
        $this->getDbTable()->insert($data);
    }
    
    public function edit($data) 
    {
        $dataArray = array(
            'TPTA_ID_TIPO_TAREFA' => (int) $data['TPTA_ID_TIPO_TAREFA'],
            'TPTA_NM_TAREFA'      => $data['TPTA_NM_TAREFA'],
            'TPTA_DS_TAREFA'      => $data['TPTA_DS_TAREFA'],
            'TPTA_DH_CADASTRO'    => new Zend_Db_Expr('SYSDATE'),
            'TPTA_CD_MATRICULA'   => $this->_userNs->matricula,
            'TPTA_IC_EXCLUIDO'    => 'N'
        );
        if ($data['TPTA_ID_TIPO_TAREFA'] != 1) {
            $this->getDbTable()->update($dataArray, 'TPTA_ID_TIPO_TAREFA = ' . (int) $data['TPTA_ID_TIPO_TAREFA']);
        } else {
            return 'Não é possível alterar o tipo de tarefa defeito.';
        }
    }

    public function delete($ids) 
    {
        $tarefa = new Tarefa_Model_DataMapper_Tarefa();
        $tarefaArray = $tarefa->listPorTipoTarefa($ids);
        if (count($tarefaArray) > 0) {
            /**
             * Realizar a exclusão lógica
             */
            $tipoTarefaArray = $this->getById($ids);
            $tipoTarefaArray['TPTA_DH_CADASTRO']  = new Zend_Db_Expr('SYSDATE');
            $tipoTarefaArray['TPTA_CD_MATRICULA'] = $this->_userNs->matricula;
            $tipoTarefaArray['TPTA_IC_EXCLUIDO']  = 'S';
            $this->getDbTable()->update($tipoTarefaArray, 'TPTA_ID_TIPO_TAREFA = ' . (int) $tipoTarefaArray['TPTA_ID_TIPO_TAREFA']);
        } else {
            if($ids != 1) {
                $this->getDbTable()->delete('TPTA_ID_TIPO_TAREFA IN ('.$ids.')');
            } else {
                return 'Não é possível excluir o tipo de tarefa defeito.';
            }
        }        
    }
    
    public function getById($id)
    {
        $row = $this->getDbTable()->find($id)->toArray();
        return $row[0];
    }

    public function listAll($order)
    {
        $rows = $this->getDbTable()->fetchAll(
            $this->getDbTable()
                 ->select()
                 ->where("TPTA_IC_EXCLUIDO = 'N'")
                 ->order($order)
        );
        $entries = array();
        foreach ($rows as $r) {
            $model = new Tarefa_Model_Entity_TipoTarefa();
            $model->setId($r['TPTA_ID_TIPO_TAREFA'])
                  ->setNome($r['TPTA_NM_TAREFA'])
                  ->setDescricao($r['TPTA_DS_TAREFA']);
            $entries[] = $model;
        }
        return $entries;
    }
    
    public function fetchPairs()
    {
        $pairs = $this->getDbTable()->fetchAll(
            $this->getDbTable()
                 ->select('TPTA_ID_TIPO_TAREFA, TPTA_NM_TAREFA')
                 ->where("TPTA_IC_EXCLUIDO = 'N'")
                 ->order('TPTA_NM_TAREFA ASC')
        );
        $fetchArray = array();
        foreach ($pairs as $p) {
            $fetchArray[$p['TPTA_ID_TIPO_TAREFA']] = $p['TPTA_NM_TAREFA'];
        }
        return $fetchArray;
    }

}
