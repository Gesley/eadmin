<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_DbTable_CoUserId extends Zend_Db_Table_Abstract 
{
    protected $_schema = 'OCS';
    protected $_name = 'CO_USER_ID';
    protected $_primary = array('COU_NM_BANCO', 'COU_COD_SECAO', 'COU_COD_MATRICULA');

}
