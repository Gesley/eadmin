<?php
/**
 * Exemplo de uma classe Business que acessa o DataMapper e manipular os dados 
 * aplicando as regras de negócio das fases administrativas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br> 
 */

class Sosti_Business_CaixasAtendimento
{

    public $_mapper;

    public function __construct() 
    {
        $this->_mapper = new Sosti_Model_DataMapper_CaixaAtendimento();
    }
    
    public function caixasAtendimentoPorUsuarioBusiness()
    {
        $usuario = new Zend_Session_Namespace('userNs');
        $array = $this->_mapper->listaCaixas(strtoupper($usuario->matricula), 'SOSTI');
        $fetchPairs = array();
        foreach ($array as $kv) {
            $fetchPairs[$kv->getId().'|'.$kv->getSiglaSecao().'|'.$kv->getCodigoLotacao()] = $kv->getDescricao();
        }
        return $fetchPairs;
    }
    
    public function niveisAtendimentoPorCaixaBusiness($param)
    {
        $caixas = explode('|', $param);
        $niveis = $this->_mapper->listaNiveis($caixas[1], $caixas[2]);
        if($niveis) {  
            foreach ($niveis as $v) { 
                $data[$v->getId()] = $v->getDescricao().' - '.$v->getSigla();
            }
        } else {
            $data[0] = ':: ESTA CAIXA NÃO POSSUI NÍVEL ::';
        }
        return $data;
    }
}
