<?php
/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados 
 * DbTable e o criar o objeto Model.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_DataMapper_TarefaStatusSolicitacao extends Zend_Db_Table_Abstract
{
   
    public static function getStatus()
    {
        return array(
            1 => 'Para homologação', 
            2 => 'Em execução', 
            3 => 'Homologado', 
            4 => 'Recusado', 
            5 => 'Aberto'
        );
    }

}
