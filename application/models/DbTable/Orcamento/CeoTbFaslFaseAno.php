<?php

class Application_Model_DbTable_Orcamento_CeoTbFaslFaseAno extends Zend_Db_Table_Abstract
{

    protected $_name = 'CEO_TB_FANE_FASE_ANO_EXERCICIO';
    protected $_primary = 'FANE_ID_FASE_ANO_EXERCICIO';
    protected $_sequence = 'CEO_SQ_FANE';
    /*
      protected $_dependentTables = array(
      'Application_Model_DbTable_CeoTbRecfRecursoFase'
      );
     */

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
