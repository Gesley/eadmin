<?php
/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados 
 * DbTable e o criar o objeto Model.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_DataMapper_UserId extends Zend_Db_Table_Abstract
{
   
    protected $_dbTable;

    public function __construct() 
    {
        $this->setDbTable(new Guardiao_Model_DbTable_CoUserId);
    }

    private function setDbTable(Guardiao_Model_DbTable_CoUserId $dbtable) 
    {
        $this->_dbTable = $dbtable;
    }

    private function getDbTable() 
    {
        return $this->_dbTable;
    }
    
    public function add(
        $nomeBanco,
        $codigoSecao,
        $matricula,
        $password,
        $nome,
        $status)
    {
        $data = array(
            'COU_NM_BANCO'      => $nomeBanco,
            'COU_COD_SECAO'     => $codigoSecao,
            'COU_COD_MATRICULA' => $matricula,
            'COU_COD_PASSWORD'  => $password,
            'COU_COD_NOME'      => $nome,
            'COU_ST_STATUS'     => $status
        );
        $this->getDbTable()->insert($data);
    }

    public function listAll() 
    {
        $rs = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($rs as $row) {
            $model = new Guardiao_Model_Entity_UserId();
            $model->setNomeBanco($row->COU_NM_BANCO)
                  ->setCodigoSecao($row->COU_COD_SECAO)
                  ->setMatricula($row->COU_COD_MATRICULA)
                  ->setPassword($row->COU_COD_PASSWORD)
                  ->setNome($row->COU_COD_NOME);
            $entries[] = $model;
        }
        return $entries;
    }
    
    public function listAllByMatricula($matricula) 
    {
        $rs = $this->getDbTable()->fetchAll("COU_COD_MATRICULA = '".$matricula."'");
        $entries = array();
        foreach ($rs as $row) {
            $model = new Guardiao_Model_Entity_UserId();
            $model->setNomeBanco($row->COU_NM_BANCO)
                  ->setCodigoSecao($row->COU_COD_SECAO)
                  ->setMatricula($row->COU_COD_MATRICULA)
                  ->setPassword($row->COU_COD_PASSWORD)
                  ->setNome($row->COU_COD_NOME);
            $entries[] = $model;
        }
        return $entries[0];
    }
}
