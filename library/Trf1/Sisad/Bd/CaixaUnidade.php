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
class Trf1_Sisad_Bd_CaixaUnidade {

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
    public function verificaDocumentoCaixaUnidade($docm_id_documento, $mode_sg_secao_unid_destino, $mode_cd_secao_unid_destino) {
       $sql = "
SELECT     
     count(*) QTDREGISTROS
FROM SAD_TB_DOCM_DOCUMENTO DOCM
     INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
     INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
     INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
WHERE 
      MOVI.MOVI_DH_ENCAMINHAMENTO = (SELECT MAX(MOVI_1.MOVI_DH_ENCAMINHAMENTO)
            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
      AND  DOCM_IC_ARQUIVAMENTO = 'N'
      AND  DOCM_IC_ATIVO = 'S'
      AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N'
      AND DOCM_ID_DOCUMENTO =                    ?
      AND MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = ?
      AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = ?
      ";

        $dados = $this->_db->fetchRow($sql, array($docm_id_documento, $mode_sg_secao_unid_destino, $mode_cd_secao_unid_destino));
        return $dados['QTDREGISTROS'] != 0;
    }

}