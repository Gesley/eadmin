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
class App_Dashboard_Dado_Pizza extends App_Dashboard_Dado_Abstract
{
    const TIPO_GRAFICO = 'pie';

    public function isValid ()
    {
        return true;
    }

    public function toArray ()
    {
        return $this->_toArray();
    }

    public function toJson ()
    {
        return $this->_toJson();
    }

    public function setItens ($itens)
    {
        return $this->_setItens($itens);
    }

    public function getTipoGrafico ()
    {
        return self::TIPO_GRAFICO;
    }
}

?>
