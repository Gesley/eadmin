<?php

/**
 * @category	Services
 * @package		Services_Sisad_TipoConfidencialidade
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de servico sobre tipos de confidencialidade
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
class Services_Sisad_TipoConfidencialidade {

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        
    }

    /**
     * Retorna um array com os tipos de confidencialidade
     * 
     * @param array $excluidos
     * @return array
     */
    public function retornaComboAdministrativa() {
        $rn_tipoConfidencialidade = new Trf1_Sisad_Negocio_TipoConfidencialidade();
        return $rn_tipoConfidencialidade->retornaCacheTipoConfidencialidade();
    }

}