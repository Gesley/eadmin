<?php

/**
 * @category	Services
 * @package		Services_Sisad_Confidencialidade
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço sobre confidencialidade de documento no sisad
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
class Services_Sisad_Confidencialidade {

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        
    }

    /**
     * Retorna os tipos de confidencialidade no sisad
     * @param string $utilidade
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getConfidencialidades($tipo = 'administrativas') {
        $rn_confidencialidade = new Trf1_Sisad_Negocio_Confidencialidade();
        $confidencialidades = null;
        if ($tipo == 'administrativas') {
            $confidencialidades = $rn_confidencialidade->getConfidencialidadesAdministrativas();
        }elseif($tipo == 'judiciais'){
            //ainda não implementado a possibilidade de ter documentos judiciais no e-admin
            //quando for implementada a possibilidade tratar as telas
            $confidencialidades = $rn_confidencialidade->getConfidencialidadesJudiciais();
        }
        return $confidencialidades;
    }

}