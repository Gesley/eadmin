<?php

/**
 * @category	Services
 * @package		Services_Sisad_TipoJuntada
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre qualquer tipo de Vinculação de documentos no Sisad
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
class Services_Sisad_TipoJuntada {

    /**
     * Armazena a quantidade de vinculos
     *
     * @var Trf1_Sisad_Negocio_TipoJuntada $_rnJuntada
     */
    private $_rnTipoJuntada;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_rnTipoJuntada = new Trf1_Sisad_Negocio_TipoJuntada();
    }

    /**
     * Monta a combo do tipo de juntada de acordo com o parametro $tipoJuntada
     * Ex: $tipoJuntada = 'documentoaprocesso'
     * 
     * @param type $tipoJuntada
     */
    public function getTipoJuntada($tipoJuntada, $tp_vinculo = null) {
        $tipos = array();
        if ($tipoJuntada == 'documentoaprocesso') {
            $tipos = $this->_rnTipoJuntada->getTipoJuntadaDocumentoProcesso();
        } elseif ('processoaprocesso') {
            $tipos = $this->_rnTipoJuntada->getTipoJuntadaProcessoProcesso();
        } elseif ('documentoadocumento') {
            $tipos = $this->_rnTipoJuntada->getTipoJuntadaDocumentoDocumento();
        }
        return (is_null($tp_vinculo) ? $tipos : $tipos[$tp_vinculo]);
    }

}