<?php
/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados 
 * DbTable e o criar o objeto Model.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_DataMapper_Tarefa extends Zend_Db_Table_Abstract
{
   
    protected $_dbTable;
    protected $_userNs;

    public function __construct() 
    {
        $this->setDbTable(new Tarefa_Model_DbTable_SosTbTareTarefa);
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    private function setDbTable(Tarefa_Model_DbTable_SosTbTareTarefa $dbtable) 
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
            'TARE_ID_TIPO_TAREFA' => (int) $data['TARE_ID_TIPO_TAREFA'],
            'TARE_DS_TAREFA'      => $data['TARE_DS_TAREFA'],
            'TARE_CD_MATRICULA'   => strtoupper($this->_userNs->matricula),
            'TARE_IC_ATIVO'       => 'S',
            'TARE_DH_CADASTRO'    => new Zend_Db_Expr('SYSDATE')
        );
        return $this->getDbTable()->insert($dataInsert);
    }

    public function delete($ids) 
    {
        $this->getDbTable()->delete('TARE_ID_TAREFA IN ('.$ids.')');
    }

    public function listAll($solicitacao, $order)
    {
        $rows = $this->getDbTable()->fetchAll(
            $this->getDbTable()
                 ->select()
                 ->from(array('TA' => 'SOS_TB_TARE_TAREFA'))
                 ->joinInner(array('TS' => 'SOS_TB_TASO_TAREFA_SOLICIT'),
                        'TA.TARE_ID_TAREFA = TS.TASO_ID_TAREFA')
                 ->joinInner(array('TT' => 'SOS_TB_TPTA_TIPO_TAREFA'), 
                        'TT.TPTA_ID_TIPO_TAREFA = TA.TARE_ID_TIPO_TAREFA')
                 ->setIntegrityCheck(false)
                 ->where('TS.TASO_ID_DOCUMENTO = ?', $solicitacao)
                 ->order($order)
        );
        $entries = array();
        $nome = new Guardiao_Model_DataMapper_UserId();
        foreach ($rows as $row) {
            $entriesNome = $nome->listAllByMatricula($row->TASO_CD_MATR_ATEND_TAREFA ?: $row->TARE_CD_MATRICULA);
            $model = new Tarefa_Model_Entity_Tarefa();
            $model->setId($row->TARE_ID_TAREFA)
                  ->setTipo($row->TPTA_NM_TAREFA)
                  ->setDescricao($row->TARE_DS_TAREFA)
                  ->setMatricula($row->TARE_CD_MATRICULA)
                  ->setNomeAtendente($entriesNome->getNome())
                  ->setStatus($row->TASO_IC_SITUACAO_NEGOCIACAO);
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
    
    public function edit($data) 
    {
        return $this->getDbTable()->update($data, 'TARE_ID_TAREFA = ' . (int) $data['TARE_ID_TAREFA']);
    }
    
    public function getById($id)
    {
        $row = $this->getDbTable()->fetchAll(
            $this->getDbTable()
                ->select()
                ->from(array('TA' => 'SOS_TB_TARE_TAREFA'),
                   array('TARE_DH_CADASTRO'=> new Zend_Db_Expr("TO_CHAR(TA.TARE_DH_CADASTRO ,'dd/mm/yyyy HH24:MI:SS') "),
                         'TARE_ID_TAREFA' => 'TA.TARE_ID_TAREFA', 'TARE_ID_TIPO_TAREFA' => 'TA.TARE_ID_TIPO_TAREFA',
                         'TARE_DS_TAREFA' => 'TA.TARE_DS_TAREFA', 'TARE_CD_MATRICULA' => 'TA.TARE_CD_MATRICULA',
                         'TARE_IC_ATIVO' => 'TA.TARE_IC_ATIVO'))
                ->joinInner(array('TS' => 'SOS_TB_TASO_TAREFA_SOLICIT'),
                       'TA.TARE_ID_TAREFA = TS.TASO_ID_TAREFA')
                ->joinInner(array('TT' => 'SOS_TB_TPTA_TIPO_TAREFA'), 
                       'TT.TPTA_ID_TIPO_TAREFA = TA.TARE_ID_TIPO_TAREFA')
                ->setIntegrityCheck(false)
                ->where('TS.TASO_ID_TAREFA = ?', $id)
            )->toArray();
        return $row[0];
    }

}
