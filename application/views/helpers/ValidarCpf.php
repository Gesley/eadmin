<?php

class Zend_View_Helper_ValidarCpf extends Zend_View_Helper_Abstract
{
    public function validarCpf($CPF)
    {
        if (strlen($CPF) == 14) {
            return "CPF Certo <br />";
        } else {
            return "CPF Errado <br />";
        }
    }
}
?>
