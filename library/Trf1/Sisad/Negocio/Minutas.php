<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Documento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Sisad
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
class Trf1_Sisad_Negocio_Minutas extends Trf1_Sisad_Negocio_Documento {

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
     * Busca minuta do documento
     * 
     * @param	int	$idDocumento	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getMinutaDoDocumento($idDocumento) {
        /* VIDC_ID_DOC_PRINCIPAL = documento */
        /* VIDC_ID_DOC_VINCULADO = minuta */
        $sql = '
SELECT
    DOCM.DOCM_NR_DOCUMENTO
FROM
    SAD_TB_DOCM_DOCUMENTO       DOCM
INNER JOIN
    SAD_TB_VIDC_VINCULACAO_DOC  VIDC ON
        VIDC.VIDC_ID_DOC_VINCULADO  = DOCM.DOCM_ID_DOCUMENTO        AND
        VIDC.VIDC_ID_DOC_PRINCIPAL  = ?                             AND
        VIDC.VIDC_ID_TP_VINCULACAO  = ?
';
        return $this->_db->query($sql, array($idDocumento, Trf1_Sisad_Definicoes::ID_VINCULACAO_MINUTA))->fetch();
    }

    /**
     * Busca documento da minuta 
     * 
     * @param	int	$idMinuta	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumentoDaMinuta($idMinuta) {
        /* VIDC_ID_DOC_PRINCIPAL = documento */
        /* VIDC_ID_DOC_VINCULADO = minuta */
        $sql = '
SELECT
    DOCM.DOCM_NR_DOCUMENTO
FROM 
    SAD_TB_DOCM_DOCUMENTO DOCM
INNER JOIN 
    SAD_TB_VIDC_VINCULACAO_DOC VIDC ON
        VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
        AND VIDC.VIDC_ID_DOC_VINCULADO = ?
        AND VIDC.VIDC_ID_TP_VINCULACAO = ?
';
        return $this->_db->query($sql, array($idMinuta, Trf1_Sisad_Definicoes::ID_VINCULACAO_MINUTA))->fetch();
    }

}