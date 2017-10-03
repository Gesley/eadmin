<?php

/**
 * @category	Services
 * @package		Services_Sisad_CaixaUnidade
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
class Services_Sisad_CaixaUnidade {

    /**
     * Armazena a quantidade de vinculos
     *
     * @var Trf1_Sisad_Negocio_CaixaUnidade $_rnJuntada
     */
    private $_rnCaixaUnidade;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_rnCaixaUnidade = new Trf1_Sisad_Negocio_CaixaUnidade();
    }

    /**
     * Busca todos os documentos da caixa que não seja autuado, arquivado e seja ativo
     * 
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumentos(array $configuracao){
        return $this->_rnCaixaUnidade->getDocumentos($configuracao);
    }
    
    /**
     * Busca todos os processos da caixa
     * 
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getProcessos(array $configuracao){
        return $this->_rnCaixaUnidade->getProcessos($configuracao);
    }
}