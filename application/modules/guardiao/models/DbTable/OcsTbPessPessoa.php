<?php
/**
 * O Model DbTable é a Classe responsável pelo acesso ao banco de dados, onde 
 * são mapeados os relacionamentos das tabelas
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_DbTable_OcsTbPessPessoa extends Zend_Db_Table_Abstract 
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PESS_PESSOA';
    protected $_primary = 'PESS_ID_PESSOA';

}
