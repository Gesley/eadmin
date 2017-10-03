<?php
/**
 * Verificações nas fsses das solicitações de TI..
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Sosti_Model_DataMapper_HistoricoSolicitacao extends Zend_Db_Table_Abstract
{
    /**
     * Pega a última fase da solicitação para identificar se está finalizada:
     * 1000 - BAIXA SOLICITAÇÃO TI
     * 1014 - AVALIAÇÃO DE SERVIÇO DE TI
     * 1026 - CANCELAMENTO DE SOLICITAÇÃO
     * @param type $idSolic
     * @return boolean
     */
    public static function getFinalizada($idSolic)
    {
        $solicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $ultimaFase = array_shift($solicitacao->getHistoricoSolicitacao($idSolic));
        $arrayFase = in_array($ultimaFase["FADM_ID_FASE"], array(1000, 1014, 1026));
        return $arrayFase;

    }
    
}