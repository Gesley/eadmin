<?php

class Application_Model_DbTable_PLocalidade extends Zend_Db_Table_Abstract
{
    protected $_name = 'P_LOCALIDADE';
    protected $_primary = 'LOCD_CD_LOCALIDADE';

    public function getLocalByUF($uf){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("        SELECT DISTINCT
                                      CASE locd_cap_uf WHEN 'DF' THEN 'Brasília'
                                                       WHEN 'MA' THEN 'São Luís'
                                                       WHEN 'AM' THEN 'Manaus'
                                                       WHEN 'MT' THEN 'Cuiabá'
                                                       WHEN 'GO' THEN 'Goiânia'
                                                       WHEN 'MG' THEN 'Belo Horizonte'
                                                       WHEN 'RR' THEN 'Boa Vista'
                                                       WHEN 'RO' THEN 'Porto Velho'
                                                       WHEN 'PA' THEN 'Belém'
                                                       WHEN 'PI' THEN 'Teresina'
                                                       WHEN 'AP' THEN 'Macapá'
                                                       WHEN 'BA' THEN 'Salvador'
                                                       WHEN 'AC' THEN 'Rio Branco'
                                                       WHEN 'TO' THEN 'Palmas'
                                                      ELSE 'Brasília'
                                      END CIDADE
                                    FROM P_LOCALIDADE
                                    WHERE locd_cap_uf = '$uf'");
        return $stmt->fetch();
    }
}