<?php
/**
 * Implementação do Design pattern Factory que é utilizado para criar novas
 * instancias das Facades.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class App_Factory_FactoryFacade 
{

    public static function createInstance($facade)
    {
        if ($facade != '') {
            return new $facade;
        } else {
            throw new Exception ('Facade não encontrada.');
        }
    }
}
