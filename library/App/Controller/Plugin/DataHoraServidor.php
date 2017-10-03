<?php
/**
 * Data e hora atual do servidor onde está instalado o Apache.
 */
class App_Controller_Plugin_DataHoraServidor extends Zend_Controller_Plugin_Abstract {
    public static function now()
    {
        $date = new Zend_Date();
        $hora = $date->get(Zend_Date::HOUR);
        $minuto = $date->get(Zend_Date::MINUTE);
        $segundo = $date->get(Zend_Date::SECOND);
        $dia = $date->get(Zend_Date::DAY);
        $mes = $date->get(Zend_Date::MONTH);
        $ano = $date->get(Zend_Date::YEAR);
        return "$dia/$mes/$ano $hora:$minuto:$segundo";
    }
}