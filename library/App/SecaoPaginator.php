<?php

class App_SecaoPaginator  {
	

    protected $ns;
    protected  $params = array();
    protected $defaults = array(
        'ordem'        => 'TEMPO_TOTAL',
        'page'         => 1,
        'itemsperpage' => 20,
        'direcao'      => 'ASC',
    );
  

    public function __construct ($ns, $defaults)
    {
    	$this->ns = new Zend_Session_Namespace($ns);
    	
    	
        $sessionVar = (array)$this->ns->getIterator();
        
    	$request = Zend_Controller_Front::getInstance()->getRequest();
    	$this->params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        
        if (!isset($this->ns->existFilter)) {
    		$this->defaults = array_merge($this->defaults,$defaults);
    		$this->params = array_merge($this->defaults,$this->params);	
    	} 
        $this->params = array_merge($sessionVar,$this->params);
        
        
       
        
        $this->inicializar();
        $this->ns->existFilter = true;
    }
    
    public function getSessionVars()
    {
    	return array(
	        'ordem'        => $this->ns->ordem,
	        'page'         => $this->ns->page,
	        'itemsperpage' => $this->ns->itemsperpage,
	        'direcao'      => $this->ns->direcao,
	    );
    }
    
    public function paginatorKey($key)
    {
        return array_key_exists($key, $this->defaults);
    }

    public function inicializar ()
    {
        //validar
        
        foreach($this->params as $key=>$value)
        {
            if (array_key_exists($key, $this->defaults)) {
            	$metodo = 'set' . ucfirst($key);
            	$this->$metodo($value);
                //$this->ns->$key = $value;
            }
            //$this->ns->$key = $value;
        }
        //$this->popularSessao();
    }
    
    public function popularSessao()
    {
        foreach($this->params as $key=>$value)
        {
            if (array_key_exists($key, $this->defaults)) {
                $this->$key = $value;
            }
        }
        $this->sessionVar = $this->ns->getIterator();
        Zend_Debug::dump($this->sessionVar,'sessionVar');
    }

    /**
     * @return the $page
     */
    public function getPage ()
    {
        return $this->ns->page;
    }

    /**
     * Retorna o nome da Coluna a ser ordenada 
     * @return the $ordem
     */
    public function getOrdem ()
    {
        return $this->ns->ordem;
    }

    /**
     * Retorna a direcao do ordenação.
     * @return the $direcao
     */
    public function getDirecao ()
    {
        return $this->ns->direcao;
    }

    /**
     * retorna o número de item pra visualizar quantidades de linhas na página.
     * @return the $itemsperpage
     */
    public function getItemsperpage ()
    {
        return $this->ns->itemsperpage;
    }

    /**
     * @param field_type $page
     */
    public function setPage ($page)
    {
        $this->ns->page = $page;
    }

    /**
     * @param field_type $ordem
     */
    public function setOrdem ($ordem)
    {
        
    	$this->ns->ordem = $ordem;
    }

    /**
     * @param field_type $direcao
     */
    public function setDirecao ($direcao)
    {
         if (!in_array(strtolower($direcao), array('asc','desc'))) {
            throw new Exception('[' . __CLASS__ . '] direcao invalida');
        }
        $this->ns->direcao = $direcao;
    }

    /**
     * @param field_type $itemsperpage
     */
    public function setItemsperpage ($itemsperpage)
    {
        if (!in_array($itemsperpage, array(15,30,50,60,100,120,150,200,240,250,1001))) {
            throw new Exception('[' . __CLASS__ . '] Itens por pagina fora dos limites[15 - 240]');
        }
        $this->ns->itemsperpage = $itemsperpage;
    }

}
	


?>