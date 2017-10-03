<?php

/**
 * @category            Application_Model_DbTable
 * @package		Application_Model_DbTable_SadTbImdiImpedeDistribui
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author              Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 */
class Application_Model_DbTable_SadTbImdiImpedeDistribui extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name     = 'SAD_TB_IMDI_IMPEDE_DISTRIBUI';
    protected $_primary  = 'IMDI_ID_IMPEDIMENTO';
    protected $_sequence = 'SAD_SQ_IMDI';

    /**
    * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
    * @param array  $arrayDados ('IMDI_ID_PROCESSO_DIGITAL'=>'', 'IMDI_CD_COMISSAO'=>'', 'IMDI_CD_MATRICULA_SERVIDOR'=>'')
    * @return boolean
    */
    public function verificaImpedimento(array $arrayImpedimento) {

        $db   = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) COUNT
                            FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                            WHERE IMDI_ID_PROCESSO_DIGITAL       = ?
                                  AND IMDI_CD_COMISSAO           = ?
                                  AND IMDI_CD_MATRICULA_SERVIDOR = ?"
                            , array(
                                $arrayImpedimento[IMDI_ID_PROCESSO_DIGITAL]
                                ,$arrayImpedimento[IMDI_CD_COMISSAO]
                                ,$arrayImpedimento[IMDI_CD_MATRICULA_SERVIDOR]
                            ));
        return $stmt->fetchAll();
    }

}