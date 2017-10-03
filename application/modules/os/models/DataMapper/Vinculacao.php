<?php
/**
 * Validações para vinculações de OS's e solicitações.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Os_Model_DataMapper_Vinculacao extends Zend_Db_Table_Abstract
{
    
    public static function osAberta($arrayOs)
    {
        $osTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $documento = new Application_Model_DbTable_SadTbDocmDocumento();
         /** Quando o ator solicitar vincular 2 ou mais solicitações que possuam OS, 
          * o sistema deverá Impedir a ação a apresentar a MSG040.
          */
         if (count($arrayOs) > 1) {
             return true;
        /** Se a OS estiver avaliada positivamente, o sistema deverá permitir a vinculação normalmente. */
         } else if (count($arrayOs) == 1) {
            $id = $documento->fetchRow('DOCM_NR_DOCUMENTO = '.$arrayOs[0]);
            $historico = $osTbSsolSolicitacao->getHistoricoSolicitacao($id['DOCM_ID_DOCUMENTO']);
            if ($historico[0]['FADM_ID_FASE'] == '1014') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
   
}