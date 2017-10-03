<?php
/**
 * Exemplo de uma classe Business que acessa o DataMapper e manipular os dados 
 * aplicando as regras de negócio
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br> 
 */

class Sosti_Business_ControleConcorrencia
{

    public $_mapper;
    public $_populaCombo;

    public function __construct() 
    {
        $this->_mapper = new Sosti_Model_DataMapper_ControleConcorrencia();
    }

    public function listAllBusiness($sigla, $codigo, $nivel, $order) 
    {
        $data = $this->_mapper->listAll($sigla, $codigo, $nivel, $order);
        $arrayRowset = array();
        foreach($data as $k=>$d) {
            $arrayRowset[$k]['ID'] = $d->getId(); 
            $arrayRowset[$k]['NUMERO_DOCUMENTO'] = $d->getNumeroDocumento();
            $arrayRowset[$k]['DESCRICAO_FASE'] = $d->getFase();
            $arrayRowset[$k]['NOME_PESSOA'] = $d->getMatricula().' - '.$d->getNomeAtendente();
            $arrayRowset[$k]['DATA_ACAO'] = $d->getDataMovimentacao();
        }
        return $arrayRowset;
    }

    public function addBusiness($idDocumento, $idFase, $matricula) 
    {
        $this->_mapper->add($idDocumento, $idFase, $matricula);
        return true;
    }

    public function deleteBusiness($id) 
    {
        if (empty($id)) {
            return false;
        }
        $this->_mapper->delete($id);
        return true;
    }
    
}
