<?php

/**
 * @category	Services
 * @package		Services_Sisad_Pctt
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço sobre pctt no sisad
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
class Services_Sisad_Pctt
{

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct()
    {
        
    }

    /**
     * Retorna os pctts do sisad da forma especificada. A utilidade pode ser em um ajax ou normal
     * @param string $utilidade
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getPctts($utilidade = 'normal')
    {
        $rn_pctt = new Trf1_Sisad_Negocio_Pctt();
        $pctts = null;
        //se for para usar em um ajax
        if ($utilidade == 'ajax') {
            $pctts = $rn_pctt->getPcttsAjax();
        } else {
            $pctts = $rn_pctt->getPctts();
        }
        return $pctts;
    }

    public function retornaCombo()
    {
        $rn_pctt = new Trf1_Sisad_Negocio_Pctt();
        return $rn_pctt->retornaCachePctt();
    }

}
