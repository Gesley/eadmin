<?php

/**
 * @category	Services
 * @package		Services_Sisad_Situacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço sobre situação de documentos no sisad
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
class Services_Sisad_Situacao {

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        
    }

    /**
     * Retorna os tipos de situações de documentos no sisad
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getSituacoes() {
        $rn_confidencialidade = new Trf1_Sisad_Negocio_Situacao();
        return $rn_confidencialidade->getSituacoes();
    }

}