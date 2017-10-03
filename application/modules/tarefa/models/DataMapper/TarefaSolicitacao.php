<?php
/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados 
 * DbTable e o criar o objeto Model.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_DataMapper_TarefaSolicitacao extends Zend_Db_Table_Abstract
{
   
    protected $_dbTable;
    protected $_userNs;

    public function __construct() 
    {
        $this->setDbTable(new Tarefa_Model_DbTable_SosTbTasoTarefaSolicit());
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    private function setDbTable(Tarefa_Model_DbTable_SosTbTasoTarefaSolicit $dbtable) 
    {
        $this->_dbTable = $dbtable;
    }

    private function getDbTable()
    {
        return $this->_dbTable;
    }
    
    public function add($data)
    {
        $dataInsert = array(
            'TASO_ID_DOCUMENTO'           => (int) $data['TASO_ID_DOCUMENTO'],
            'TASO_ID_TAREFA'              => (int) $data['TASO_ID_TAREFA'],
            'TASO_IC_SITUACAO_NEGOCIACAO' => (int) $data['TASO_IC_SITUACAO_NEGOCIACAO']
        );
        return $this->getDbTable()->insert($dataInsert);
    }

    public function delete($ids) 
    {
        $this->getDbTable()->delete('TASO_ID_TAREFA IN ('.$ids.')');
    }

    public function listAll($order)
    {
        $q = "SELECT *
              FROM SOS_TB_TASO_TAREFA_SOLICIT ";
        $q .= $order != '' ? "ORDER BY ".$order : '';
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query($q);
        $rows = $stmt->fetchAll();
        $entries = array();
        foreach ($rows as $row) {
            $model = new Os_Model_Entity_Tarefa();
            $model->setId($row['TASO_ID_TAREFA_SOLICIT'])
                  ->setStatus($row['TASO_ID_DOCUMENTO'])
                  ->setTipo($row['TASO_ID_TAREFA']);
            $entries[] = $model;
        }
        return $entries;
    }
    
    public function listPorTipoTarefa($tipoTarefa) 
    {
        return $this->getDbTable()
                    ->fetchAll('TARE_ID_TIPO_TAREFA IN ('.$tipoTarefa.')')
                    ->toArray();
    }
    
    public function fetchAll($where = null, $order = null, $count = null, $offset = null) 
    {
        return parent::$this->getDbTable()->fetchAll($where, $order, $count, $offset);
    }
    
    public function getById($id)
    {
        $row = $this->getDbTable()->find($id)->toArray();
        return $row[0];
    }
    
    public function getDefeitosSolicitacoesSla($idSolicitacao)
    {
        $countRows = $this->getDbTable()->fetchRow(
            $this->getDbTable()->select()
            ->from(array('TASO' => 'SOS_TB_TASO_TAREFA_SOLICIT'),
                array('COUNT' => 'COUNT(*)'))
            ->where("TASO.TASO_IC_ACEITE_ATENDENTE = ?", 'S')
            ->where("TASO.TASO_ID_DOCUMENTO = ?", $idSolicitacao)
        );
        return $countRows->COUNT;
    }
    
    public function edit($data) 
    {
        return $this->getDbTable()->update($data, 'TASO_ID_TAREFA = ' . (int) $data['TASO_ID_TAREFA']);
    }
    
    public function getAnexoTarefaSolicitacao($idTarefa, $list)
    {
        /** Lista os anexos de acordo com o input */
        $listInput = explode('-', $list);
        $arrayInputAnex = array(
            'tarefa'  => 'C',
            'fabrica' => 'F',
            'gestao'  => 'T'
        );
        $row = $this->getDbTable()->fetchAll(
            $this->getDbTable()
                ->select()
                ->from(array('TS' => 'SOS_TB_ANTS_ANEX_TAREFA_SOLIC'))
                ->joinInner(array('AA' => 'SAD_TB_ANEX_ANEXO'), 
                    'TS.ANTS_ID_DOCUMENTO_INTERNO = AA.ANEX_ID_ANEXO')
                ->joinInner(array('TT' => 'SOS_TB_TASO_TAREFA_SOLICIT'),
                    'TT.TASO_ID_TAREFA_SOLICIT = TS.ANTS_ID_TAREFA_SOLICIT')
                ->setIntegrityCheck(false)
                ->where('TS.ANTS_IC_INPUT_ANEXO = ?', $arrayInputAnex[$listInput[1]])
                ->where('TT.TASO_ID_TAREFA = ?', $idTarefa)
        );
        return $row;
    }
}