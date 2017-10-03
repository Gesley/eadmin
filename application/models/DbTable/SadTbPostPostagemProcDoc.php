<?php
class Application_Model_DbTable_SadTbPostPostagemProcDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_POST_POSTAGEM_PROC_DOC';
    protected $_primary = 'POST_ID_POSTAGEM_PROC_DOC';
    protected $_sequence = 'SAD_SQ_ID_POSTAGEM_PROC_DOC';
    
    
    /*
     * Função que retorna todas as postagens a serem entregues ao Protocolo
     * de um determinada lotação.
     * 
     * Utilização 1:
     * - Módulo: Caixa Unidade
     * - Controller: Protocolar
     */
    public function getPostagens($secOrigem, $lotacaoOrigem)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(PRDC_ID_PROT_DOC_PROCESSO) QTD,
                                   PRDC_ID_POSTAGEM_PROC_DOC,
                                   TPPO_DS_TIPO_POSTAGEM,
                                   POST_NM_DESTINATARIO_EXTERNO,
                                   PJUR_NO_FANTASIA,
                                   POST_DS_ENDERECO_DESTINO,
                                   POST_DS_BAIRRO_DESTINO,
                                   POST_DS_CIDADE_DESTINO,
                                   POST_CD_UF_DESTINO,
                                   POST_CD_CEP_DESTINO,
                                   POST_DS_PAIS_DESTINO
                              FROM SAD_TB_PRDC_PROT_DOC_PROCESSO,
                                   SAD_TB_POST_POSTAGEM_PROC_DOC,
                                   OCS_TB_PJUR_PESSOA_JURIDICA,
                                   SAD_TB_TPPO_TIPO_POSTAGEM
                             WHERE POST_ID_POSTAGEM_PROC_DOC = PRDC_ID_POSTAGEM_PROC_DOC
                               AND POST_CD_PESSOA_DESTINO = PJUR_ID_PESSOA
                               AND POST_ID_TIPO_POSTAGEM = TPPO_ID_TIPO_POSTAGEM
                               AND POST_SG_SECAO_ORIGEM = '$secOrigem'
                               AND POST_CD_LOTACAO_ORIGEM = $lotacaoOrigem
                               AND PRDC_ID_PROTOCOLO IS NULL
                               AND PRDC_DH_FIM_PROTOCOLO IS NULL
                          GROUP BY PRDC_ID_POSTAGEM_PROC_DOC,
                                   TPPO_DS_TIPO_POSTAGEM,
                                   POST_NM_DESTINATARIO_EXTERNO,
                                   PJUR_NO_FANTASIA,
                                   POST_DS_ENDERECO_DESTINO,
                                   POST_DS_BAIRRO_DESTINO,
                                   POST_DS_CIDADE_DESTINO,
                                   POST_CD_UF_DESTINO,
                                   POST_CD_CEP_DESTINO,
                                   POST_DS_PAIS_DESTINO");
        return $stmt->fetchAll();
    }
    
    
    /*
     * Funcão que traz o ID do Processo e do Documentos de acordo com o ID de sua 
     * postagem
     * 
     * Utilização 1:
     * - Modulo: Caixa Unidade
     * - Controller: Protocolar
     */
    public function getDadosDocumentosPostagem($idPostagem)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PRDC_ID_PROT_DOC_PROCESSO,
                                   PRDC_ID_DOCUMENTO 
                              FROM SAD_TB_PRDC_PROT_DOC_PROCESSO 
                             WHERE PRDC_ID_POSTAGEM_PROC_DOC = $idPostagem");
        return $stmt->fetchAll();
    }
}
