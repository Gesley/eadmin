<?php
class Application_Model_DbTable_SadTbMapoMalotePostagem extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_MAPO_MALOTE_POSTAGEM';
    protected $_primary = array('MAPO_ID_MALOTE',
                                'MAPO_NR_MALOTE',
                                'MAPO_SG_CENTRAL_ORIGEM',
                                'MAPO_SG_SECSUBSEC_ORIGEM',
                                'MAPO_SG_CENTRAL_DESTINO',
                                'MAPO_SG_SECSUBSEC_DESTINO');
    
    public function getMalotes($codSecaoDestino,$codSubsecDestino){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.MAPO_ID_MALOTE, 
                                   A.MAPO_NR_MALOTE, 
                                   A.MAPO_SG_CENTRAL_ORIGEM,
                                   A.MAPO_SG_SECSUBSEC_ORIGEM, 
                                   A.MAPO_SG_CENTRAL_DESTINO,
                                   A.MAPO_SG_SECSUBSEC_DESTINO, 
                                   A.MAPO_ID_ORGAO_EXTERNO,
                                   A.MAPO_DS_ORGAO_EXTERNO, 
                                   A.MAPO_CD_MATRICULA,
                                   A.MAPO_DH_ALTERACAO, 
                                   A.MAPO_IC_TIPO_MALOTE, 
                                   A.MAPO_IC_ATIVO 
                              FROM SAD_TB_MAPO_MALOTE_POSTAGEM A
                             WHERE A.MAPO_SG_CENTRAL_DESTINO = '$codSecaoDestino'
                               AND A.MAPO_SG_SECSUBSEC_DESTINO = '$codSubsecDestino'
                          ORDER BY A.MAPO_NR_MALOTE");
        return $stmt->fetchAll();
    }
    
    public function getMaloteCadastrado($nrMalote)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MAPO_ID_MALOTE,
                                   MAPO_SG_CENTRAL_ORIGEM,
                                   MAPO_SG_SECSUBSEC_ORIGEM,
                                   MAPO_SG_CENTRAL_DESTINO,
                                   MAPO_SG_SECSUBSEC_DESTINO 
                            FROM SAD_TB_MAPO_MALOTE_POSTAGEM 
                            WHERE MAPO_NR_MALOTE = $nrMalote");
        return $stmt->fetchAll();
    }
    
    public function getProximoNumeroMalote()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MAX(MAPO_ID_MALOTE)
                            FROM SAD_TB_MAPO_MALOTE_POSTAGEM");
        return $stmt->fetchAll();
    }
}