<?php
class Application_Model_DbTable_RhCentralLotacaoTelefone extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SARH';
    protected $_name = 'RH_CENTRAL_LOTACAO_TELEFONE';
    protected $_primary = array('LOTE_LOTA_SIGLA_SECAO','LOTE_LOTA_COD_LOTACAO','LOTE_FONE') ;

    public function getTelefones($siglaSecao, $codSecao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT LOTE_FONE
                  FROM RH_CENTRAL_LOTACAO_TELEFONE
                  WHERE LOTE_LOTA_SIGLA_SECAO = '$siglaSecao'
                  AND LOTE_LOTA_COD_LOTACAO = $codSecao";
                
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    }
}
