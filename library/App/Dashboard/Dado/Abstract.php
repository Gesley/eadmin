<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author tr17286ps
 */
abstract class App_Dashboard_Dado_Abstract implements App_Dashboard_Dado_Interface
{

    protected $_items  = array();
    protected $_titulo = 'Titulo Default';
    protected $_cor    = 'cinza';
    protected $_rodape = 'rodape';
    /*
    $itens = array(
        array(
            'TESTE' => 'abedj dçfl',
            'abe'   => 'abedj dçfl',
        ),
        array(
            'TESTE' => 'abedj dçfl',
            'abe'   => 'abedj dçfl',
        ),
        array(
            'TESTE' => 'abedj dçfl',
            'abe'   => 'abedj dçfl',
        ),
    );
     *
     * 
      $dados = array(
            'tipo' => 'barras',
            'titulo' => " Barras",
            'dados' => array(
                array('name' => 'No prazo', 'data' => array(100)),
                array('name' => 'Falta 25%', 'data' => array(30))
            ),
            'legenda' => 'Total de chamados até o momento da construção, mostrando os atendimentos concluídos(baixados), os não atendidos(não baixados e não encaminhados) e os encaminhados',
            'link' => 'url',
            'cor' => 'cinza'
        );
    */
    
    public function __construct(array $itens = array())
    {
        $this->setItens($itens);
    }
    
    protected function _setItens ($itens)
    {
        
        $aux = array();
        foreach($itens as $i=>$item)
        {
            //Zend_debug::dump($item);
            $aux['dados'][] = $this->retornaItemValido($item);
        }
        $aux['tipo'] = $this->getTipoGrafico();
        $aux['titulo'] = $this->getTitulo();
        $this->_items = $aux;
        return $this;
    }

    protected function _toJson ()
    {
        return json_encode($this->_items);
    }
    
    public function sendJson()
    {
        $json = Zend_Controller_Action_HelperBroker::getHelper('json');
        $json->sendJson($this->_items);
    }

    protected function _toArray ()
    {
        return $this->_items;
    }
    /*
     $dados = array(
            'tipo' => 'barras',
            'titulo' => " Barras",
            'dados' => array(
                array('name' => 'No prazo', 'data' => array(100)),
                array('name' => 'Falta 25%', 'data' => array(30))
            ),
            'legenda' => 'Total de chamados até o momento da construção, mostrando os atendimentos concluídos(baixados), os não atendidos(não baixados e não encaminhados) e os encaminhados',
            'link' => 'url',
            'cor' => 'cinza'
        );
    */
    
    /*
    array(
        'TESTE' => 'abedj dçfl',
        'abe'   => 'abedj dçfl',
    ),
    */
    protected function retornaItemValido($item = array())
    {
        if ( !array_key_exists('label', $item) ||  !array_key_exists('valor', $item)) {
            throw new Exception('a chave LABEL ou a chave VALOR não foram encontradas.');
        }
        return $item;
        /*
        $item_aux = array(
            'label' => '',
            'valor' => '',
        );
        foreach($item as $i=>$valor)
        {
            $item_aux = array('label' => $i, 'valor' => $valor);
        }
        return $item_aux;
         */
        
    }

    public function getTitulo()
    {
        return $this->_titulo;
    }

    public function setTitulo($titulo)
    {
        $this->_titulo = $titulo;
        return $this;
    }
    
    public function getCor()
    {
        return $this->_cor;
    }

    public function setCor($cor)
    {
        $this->_cor = $cor;
        return $this;
    }
    
    public function getRodape()
    {
        return $this->_rodape;
    }

    public function setRodape($rodape)
    {
        $this->_rodape = $rodape;
        return $this;
    }
}

?>
