<?php

class Application_Model_DbTable_Orcamento_CeoTbAnoExercicio extends Zend_Db_Table_Abstract
{

    protected $_name = 'CEO_TB_ANOE_ANO_EXERCICIO';
    protected $_primary = 'ANOE_AA_ANO';
    protected $_sequence = 'CEO_SQ_ANOE';

    /**
     * Retorna a chave primária
     *
     * @return	mixed		Primary key (atring ou array)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria()
    {
        return array($this->_primary);
    }

}
