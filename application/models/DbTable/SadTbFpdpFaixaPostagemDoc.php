<?php
class Application_Model_DbTable_SadTbFpdpFaixaPostagemDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_FPDP_FAIXA_POSTAGEM_DOC';
    protected $_primary = array('FPDP_ID_TIPO_POSTAGEM',
                                'FPDP_NR_NUMERO_INICIAL');
    
    public function getFaixasSecao($faixaPostagem,$sgSecao){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.FPDP_ID_TIPO_POSTAGEM, A.FPDP_NR_NUMERO_INICIAL,
                                   A.FPDP_NR_NUMERO_FINAL, A.FPDP_DS_LETRA_INICIAL,
                                   A.FPDP_DS_LETRA_FINAL, A.FPDP_CD_MATRICULA_INCLUSAO,
                                   A.FPDP_IC_POSTAGEM_ATIVO, A.FPDP_DH_INCLUSAO_POSTAGEM,
                                   A.FPDP_NR_ULTIMO_NUMERO, A.FPDP_NR_SEGURANCA_POSTAGEM,
                                   A.FPDP_CD_MATRICULA_GESTOR, A.FPDP_SG_SIGLA_SECAO_INCLUSAO,
                                   A.FPDP_CD_LOTACAO_INCLUSAO
                              FROM SAD.SAD_TB_FPDP_FAIXA_POSTAGEM_DOC A     
                            WHERE  A.FPDP_ID_TIPO_POSTAGEM = $faixaPostagem
                             AND   A.FPDP_SG_SIGLA_SECAO_INCLUSAO = '$sgSecao'");
        return $stmt->fetchAll();
    }
}