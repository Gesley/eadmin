<?php
/**
 * Serviços das solicitações de TI.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Sosti_Model_DataMapper_ServicoSolicitacao extends Zend_Db_Table_Abstract
{
    public function getAtual($idSolic)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $SQL = "SELECT SOS_P.PKG_SOLIC.SERVICO_ATUAL($idSolic) SRV FROM DUAL";
        $stmt = $db->query($SQL);
        $res = $stmt->fetch();
        return $res['SRV'];
    }
}