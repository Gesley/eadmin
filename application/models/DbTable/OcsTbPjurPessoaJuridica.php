<?php
class Application_Model_DbTable_OcsTbPjurPessoaJuridica extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PJUR_PESSOA_JURIDICA';
    protected $_primary = 'PJUR_ID_PESSOA';

    /*
     * Retorna os endereços cadastrados para determinado Destinatário Pessoa Juridica.
     * Busca de acordo com o ID da da tabela OCS_TB_PESS_PESSOA
     * 
     * Utilização 1:
     * Modulo: SISAD
     * Controller: Protocolo
     * Action: add
     * 
     */
    public function getEnderecosDestinatarios($idPjur)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  A.PJUR_NO_RAZAO_SOCIAL,
                                    A.PJUR_NR_CNPJ,
                                    A.PJUR_NO_FANTASIA,
                                    A.PJUR_IC_PORTE, 
                                    B.PTEN_NO_TP_ENDERECO, 
                                    C.PEND_NR_CEP, 
                                    C.PEND_DS_ENDERECO,
                                    C.PEND_CD_LOCALIDADE
                                    FROM OCS_TB_PJUR_PESSOA_JURIDICA A,
                                    OCS_TB_PTEN_TP_ENDERECO B,
                                    OCS_TB_PEND_ENDERECO_PESSOA C
                                    WHERE A.PJUR_ID_PESSOA = C.PEND_ID_PESSOA
                                    AND B.PTEN_ID_TP_ENDERECO = C.PEND_ID_TP_ENDERECO
                                    AND C.PEND_ID_PESSOA = $idPjur");
        return $stmt->fetchAll();
    }
    
    /*
     * Retorna os tipos de endereços cadastrados na tabela ocs_tb_pten_tp_endereco
     * 
     * Utilização 1:
     * 
     * Modulo:SISAD
     * Controller: Protocolo
     * Form: AddProtocolo
     */
    public function getTipoEndereco()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PTEN_ID_TP_ENDERECO,
                                   PTEN_NO_TP_ENDERECO 
                              FROM OCS_TB_PTEN_TP_ENDERECO 
                          ORDER BY PTEN_NO_TP_ENDERECO");
        return $stmt->fetchAll();
    }
    
    public function getNomeDestinatarioAjax($nomeDestinatario)
    {
        
       
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT B.PJUR_ID_PESSOA||' - '||B.PJUR_NO_RAZAO_SOCIAL AS VALUE,
                                   B.PJUR_ID_PESSOA||' - '||B.PJUR_NO_RAZAO_SOCIAL AS LABEL,
                                   B.PJUR_ID_PESSOA AS ID
                            FROM OCS_TB_PJUR_PESSOA_JURIDICA B
                            WHERE B.PJUR_NO_RAZAO_SOCIAL LIKE UPPER('%$nomeDestinatario%')");
        return $stmt->fetchAll();
    }
 }