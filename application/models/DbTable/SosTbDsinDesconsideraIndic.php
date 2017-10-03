<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class Application_Model_DbTable_SosTbDsinDesconsideraIndic extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_DSIN_DESCONSIDERA_INDIC';
    protected $_primary = array('DSIN_ID_MOVIMENTACAO', 'DSIN_ID_INDICADOR');
    
    public function setRetiraSla($dataDesinDesconsideraIndic, $dataDesinDesconsideraAudit)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            /**
             * Inclui na tabela SOS_TB_DSIN_DESCONSIDERA_INDIC para retirar do SLA
             */
            $rowRetiraSla = $this->createRow($dataDesinDesconsideraIndic);
            $rowRetiraSla->save();
            /**
             * Realiza auditoria da retirada do SLA incluindo na tabela SOS_TB_DSIN_AUDITORIA
             */
            $auditoria = new Application_Model_DbTable_SosTbDsinAuditoria();
            $rowRetiraSlaAuditoria =  $auditoria->createRow($dataDesinDesconsideraAudit);
            $rowRetiraSlaAuditoria->save();
            $db->commit();
          } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
    }

}