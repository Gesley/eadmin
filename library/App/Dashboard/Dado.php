<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pie
 *
 * @author tr17286ps
 */
class App_Dashboard_Dado
{
    //put your code here
    
    
    //receber um array contendo vari[aveis + fecth
    
    //fecth
    //validar qtde min e max de colunas
    //validar qtde min e max de linhas
    //outras valida;óes
    
    
     //put your code here
    
    protected static $_adapter = array();
    
    const PIZZA  = 'pizza';
    const BARRA  = 'barra';
    const COLUNA = 'coluna';
    const TABELA = 'grid';
    
    private $_tipos   = array(
        self::PIZZA,   self::PIZZA, 
        self::COLUNA , self::TABELA
    );
    protected $_tipo  = self::PIZZA;
   /**
    *
    * @param array $itens
    * @param type $tipo 
    */
    public function __construct(array $itens, $tipo = self::PIZZA)
    {
        $this->setTipo($tipo);
        $this->setItens($itens);
    }
    
    public function setItens($itens)
    {
        return $this->getAdapter()->setItens($itens);
    }
    
    public function setTipo($tipo)
    {
        if ( !in_array($tipo, $this->_tipos) ) {
            throw new Exception('Tipo de dado inválido. Os tipos válidos são: [' . implode(',', $this->_tipos) . ']');
        }
        $this->_tipo = $tipo;
        $this->setAdapter();
        return $this;
    }
    
    public function toJson()
    {
        return $this->getAdapter()->toJson();
    }
    
    public function toArray()
    {
        return $this->getAdapter()->toArray();
    }
    
    public function setAdapter()
    {
        if ( isset(self::$_adapter[$this->_tipo])) {
            return self::$_adapter[$this->_tipo];
        }
        $class = 'App_DashBoard_Dado_' . ucfirst($this->_tipo);
        self::$_adapter[$this->_tipo] = new $class;
        return self::$_adapter[$this->_tipo];
    }
    
    public function getAdapter()
    {
        return self::$_adapter[$this->_tipo];
    }
    
    public function sendJson()
    {
        return $this->getAdapter()->sendJson();
    }
}

?>
