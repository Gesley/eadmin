<?php

class Application_Model_DbTable_RhOcupaGabinete extends Zend_Db_Table_Abstract {

    protected $_name = 'RH_OCUPA_GABINETE';
    protected $_primary = array(
        'OCGA_FUNC_SIGLA_SECAO_SUBSECAO'
        , 'OCGA_FUNC_COD_FUNCIONARIO'
        , 'OCGA_LOTA_COD_LOTACAO'
        , 'OCGA_DT_INICIO');

    public function getGabinete($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MIN(OCGA_LOTA_COD_LOTACAO) CODIGO_LOTACAO,
                                    RH_DESCRICAO_LOTACAO(MIN(OCGA_LOTA_COD_LOTACAO)) DESCRICAO
                            FROM RH_OCUPA_GABINETE 
                            WHERE OCGA_DT_FIM IS NULL
                                AND OCGA_FUNC_SIGLA_SECAO_SUBSECAO||OCGA_FUNC_COD_FUNCIONARIO = '$matricula'
                            GROUP BY OCGA_FUNC_SIGLA_SECAO_SUBSECAO,OCGA_FUNC_COD_FUNCIONARIO
                            ");
        return $stmt->fetchAll();
    }

}