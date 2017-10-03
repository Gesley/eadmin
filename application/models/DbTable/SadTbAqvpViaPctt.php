<?php
class Application_Model_DbTable_SadTbAqvpViaPctt extends Zend_Db_Table_Abstract
{
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_AQVP_VIA_PCTT';
    protected $_primary = 'AQVP_ID_PCTT';

    public function getPCTT() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = $db->query("SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT DESCRICAO_PCTT
                              FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                              WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                               AND AQVP_ID_AQVP_ATUAL IS NULL
                               AND AQVP_DH_FIM IS NULL
                               ORDER BY DESCRICAO_PCTT");

        return $stmt->fetchAll();

    }
    public function getPCTTAjax($assunto) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT AS LABEL
                          FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                         WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                           AND AQVP_ID_AQVP_ATUAL IS NULL
                           AND AQVP_DH_FIM IS NULL
                           AND UPPER(AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT) LIKE UPPER('%$assunto%')");
        return $stmt->fetchAll();
    }

        public function getPCTTbyId($id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE, B.AQVP_CD_PCTT
                          FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                         WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                           AND AQVP_ID_PCTT = $id");
        return $stmt->fetch();
    }
    
        public function getPCTTbyCodigo($codigo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT B.AQVP_ID_PCTT
                          FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                         WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                           AND AQVP_CD_PCTT = '$codigo'");
        return $stmt->fetch();
    }
}