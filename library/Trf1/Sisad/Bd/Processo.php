<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Bd_Distribuicao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de persistencia de dados
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Trf1_Sisad_Bd_Processo {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * Verifica se Documento está na caixa da unidade
     * 
     * @param
     * @author Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getProcessoPorIdDocumento($idDocumento) {
        return $this->_db->fetchRow('
            SELECT  PRDI_ID_PROCESSO_DIGITAL,
                    PRDI_DS_TEXTO_AUTUACAO
            FROM SAD_TB_DOCM_DOCUMENTO
            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                ON DOCM_ID_DOCUMENTO        = DCPR_ID_DOCUMENTO
            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
            WHERE DOCM_ID_DOCUMENTO     = ? '
                                    , array($idDocumento));
    }

    public function alteraProcesso($idProcesso, $dadosAlteracao) {
        $sadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
        return $sadTbPrdiProcessoDigital->find($idProcesso)
                        ->current()
                        ->setFromArray($dadosAlteracao)
                        ->save();
    }

}