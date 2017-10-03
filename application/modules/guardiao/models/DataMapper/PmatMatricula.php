<?php
/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados 
 * DbTable e o criar o objeto Model.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_DataMapper_PmatMatricula extends Zend_Db_Table_Abstract
{
   
    protected $_dbTable;

    public function __construct() 
    {
        $this->setDbTable(new Guardiao_Model_DbTable_OcsTbPmatMatricula);
    }

    private function setDbTable(Guardiao_Model_DbTable_OcsTbPmatMatricula $dbtable) 
    {
        $this->_dbTable = $dbtable;
    }

    private function getDbTable() 
    {
        return $this->_dbTable;
    }
    
    public function add(
        $matricula,
        $idPessoa,
        $secaoSubsecaoLotacao,
        $unidadeLotacao,
        $dataInicio,
        $dataFim,
        $emailInterno,
        $nomeGuerra,
        $idTipoMatricula)
    {
        $data = array(
            'PMAT_CD_MATRICULA'         => $matricula,
            'PMAT_ID_PESSOA'            => $idPessoa,
            'PMAT_SG_SECSUBSEC_LOTACAO' => $secaoSubsecaoLotacao,
            'PMAT_CD_UNIDADE_LOTACAO'   => $unidadeLotacao,
            'PMAT_DT_INICIO'            => $dataInicio,
            'PMAT_DT_FIM'               => $dataFim,
            'PMAT_DS_EMAIL_INTERNO'     => $emailInterno,
            'PMAT_NO_GUERRA'            => $nomeGuerra,
            'PMAT_ID_TIPO_MATRICULA'    => $idTipoMatricula
        );
        $this->getDbTable()->insert($data);
    }

    public function listAll() 
    {
        $rs = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($rs as $row) {
            $model = new Guardiao_Model_Entity_PmatMatricula();
            $model->setId($row->PMAT_CD_MATRICULA)
                  ->setIdDocumento($row->PMAT_ID_PESSOA)
                  ->setIdFase($row->PMAT_SG_SECSUBSEC_LOTACAO)
                  ->setMatricula($row->PMAT_CD_UNIDADE_LOTACAO)
                  ->setDataMovimentacao($row->PMAT_DT_INICIO)
                  ->setDataMovimentacao($row->PMAT_DT_FIM)
                  ->setDataMovimentacao($row->PMAT_DS_EMAIL_INTERNO)
                  ->setDataMovimentacao($row->PMAT_NO_GUERRA)
                  ->setDataMovimentacao($row->PMAT_ID_TIPO_MATRICULA);
            $entries[] = $model;
        }
        return $entries;
    }
}
