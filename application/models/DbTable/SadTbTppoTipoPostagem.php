<?php
class Application_Model_DbTable_SadTbTppoTipoPostagem extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_TPPO_TIPO_POSTAGEM';
    protected $_primary = array('TPPO_ID_TIPO_POSTAGEM');
 
    public function getTipoPostagem(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TPPO_ID_TIPO_POSTAGEM,
                                   TPPO_DS_TIPO_POSTAGEM
                              FROM SAD_TB_TPPO_TIPO_POSTAGEM
                             WHERE TPPO_IC_ATIVO = 'S'
                          ORDER BY TPPO_DS_TIPO_POSTAGEM");
        return $stmt->fetchAll();
    }
    
    public function getTipoPostagemByID($idPostagem){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TPPO_DS_TIPO_POSTAGEM
                              FROM SAD_TB_TPPO_TIPO_POSTAGEM
                             WHERE TPPO_IC_ATIVO = 'S'
                             AND   TPPO_ID_TIPO_POSTAGEM = $idPostagem
                          ORDER BY TPPO_DS_TIPO_POSTAGEM");
        return $stmt->fetchAll();
    }
    
    public function getTipoPostagemUsuario(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TPPO_ID_TIPO_POSTAGEM,
                                   TPPO_DS_TIPO_POSTAGEM
                              FROM SAD_TB_TPPO_TIPO_POSTAGEM
                             WHERE TPPO_IC_ATIVO = 'S'
                               AND TPPO_ID_TIPO_POSTAGEM NOT IN(SELECT TPPO_ID_TIPO_POSTAGEM
                              FROM SAD_TB_TPPO_TIPO_POSTAGEM
                             WHERE TPPO_IC_ATIVO = 'S'
                               AND TPPO_DS_TIPO_POSTAGEM LIKE '%SEDEX%')
                          ORDER BY TPPO_DS_TIPO_POSTAGEM");
        return $stmt->fetchAll();
    }
}