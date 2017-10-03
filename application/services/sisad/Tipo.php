<?php

/**
 * @category	Services
 * @package		Services_Sisad_Tipo
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre qualquer tipo de algo
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
class Services_Sisad_Tipo {

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        
    }

    /**
     * Retorna os tipos de documentos
     * 
     * @param array $excluidos
     * @return array
     */
    public function getTipoDocumento($excluidos = array()) {
        $rn_tipo = new Trf1_Sisad_Negocio_Tipo();
        return $rn_tipo->getTipoDocumento($excluidos);
    }

    /**
     * Monta a combo do tipo de juntada de acordo com o parametro $tipoJuntada
     * Ex: $tipoJuntada = 'documentoaprocesso'
     * 
     * @param type $tipoJuntada
     */
    public function getTipoJuntada($tipoJuntada, $tp_vinculo = null) {
        $rn_tipo = new Trf1_Sisad_Negocio_Tipo();
        $tipos = array();
        if ($tipoJuntada == 'documentoaprocesso') {
            $tipos = $rn_tipo->getTipoJuntadaDocumentoProcesso();
        } elseif ('processoaprocesso') {
            $tipos = $rn_tipo->getTipoJuntadaProcessoProcesso();
        } elseif ('documentoadocumento') {
            $tipos = $rn_tipo->getTipoJuntadaDocumentoDocumento();
        }
        return (is_null($tp_vinculo) ? $tipos : $tipos[$tp_vinculo]);
    }

}