<?php

/**
 * @category	TRF1
 * @package		Trf1_Rh_Negocio_Gabinete
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Rh
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
class Trf1_Rh_Negocio_Gabinete {

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        
    }

    /**
     * Efetua consulta do gabinete de um desembargador especifico
     * 
     * @author Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     * @param string $matricula
     * @return array
     */
    public function getGabineteDesembargador($matricula) {
        $bd_gabiente = new Trf1_Rh_Bd_Gabinete();
        return $bd_gabiente->getGabineteDesembargador($matricula);
    }

}