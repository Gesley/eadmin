<?php
/**
 * A Facade esconde as partes complexas dos Business, pode conter tambem 
 * DataMappers e gerencia as conexões com o banco de dados e o controle de transação
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Facade_TipoTarefa
{

    protected $_mapper = "";
    protected $_db = "";
    protected $_userNs = "";

    public function __construct() 
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_mapper = new Tarefa_Model_DataMapper_TipoTarefa();
    }

    public function listAll($order)
    {
        return $this->_mapper->listAll($order);
    }
    
    public function getById($id)
    {
        return $this->_mapper->getById($id);
    }

    public function adicionar($data)
    {
        $this->_db->beginTransaction();
        try {
            $this->_mapper->add($data);
            return $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }
    
    public function editar($data)
    {
        $this->_db->beginTransaction();
        try {
            $this->_mapper->edit($data);
            return $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }

    public function excluir($id) 
    {
        $this->_db->beginTransaction();
        try {
            $this->_mapper->delete($id);
            return $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        } 
    }
    
    public function __destruct() 
    {
        $this->_db->closeConnection();
    }
}
