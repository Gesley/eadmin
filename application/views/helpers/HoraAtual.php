<?php

class Zend_View_Helper_HoraAtual extends Zend_View_Helper_Abstract
{
    public function horaAtual()
    {
        return date('h:i:s');
    }

}
?>
