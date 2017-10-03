<?php

/**
 * @category	Services
 * @package		Services_Rh_Lotacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço sobre lotações no rh
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
class Services_Rh_Lotacao
{

    /**
     * Armazena a quantidade de vinculos
     *
     * @var Trf1_Rh_Negocio_Lotacao
     */
    private $_rnLotacao;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct()
    {
        $this->_rnLotacao = new Trf1_Rh_Negocio_Lotacao();
    }

    /**
     * Retorna a família da lotacao
     * 
     * @param string $sigla
     * @param int $cod
     * @return string
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getFamiliaLotacao($sigla, $cod)
    {
        return $this->_rnLotacao->getFamiliaLotacao($sigla, $cod);
    }

    /**
     * Retorna um array contendo o tribunal e as secoes judiciarias
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboTrfSecao()
    {
        return $this->_rnLotacao->retornaCacheTrfSecao();
    }

    /**
     * Retorna um array contendo todas as subsecoes agrupadas por tribunal ou secao judiciaria
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboSubsecoesAgrupadas()
    {
        return $this->_rnLotacao->retornaCacheSubsecao();
    }

    /**
     * Retorna um array contendo todas as subsecoes de uma seção
     * @param string $siglaSecao
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboSubsecoes($siglaSecao)
    {
        $siglaSecao = mb_strtoupper($siglaSecao, 'UTF-8');
        $subsecoesAgrupadas = $this->_rnLotacao->retornaCacheSubsecao();
        return (isset($subsecoesAgrupadas[$siglaSecao]) ? $subsecoesAgrupadas[$siglaSecao] : array());
    }

    /**
     * Retorna um array contendo todas as unidades agrupadas por subsecao judiciária ou tribunal
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboUnidadesAgrupadasPorSubsecao()
    {
        return $this->_rnLotacao->retornaCacheUnidadePorSubsecao();
    }

    /**
     * Retorna um array contendo todas as unidades agrupadas pela sigla da secao ou TRF
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboUnidadesAgrupadasPorSiglaSecao()
    {
        return $this->_rnLotacao->retornaCacheUnidadePorSiglaSecao();
    }

    /**
     * Retorna um array contendo todas as unidades de uma determiniada subsecao
     * @param string $siglaSubsecao
     * @return array
     */
    public function retornaComboUnidadesPorSubsecao($siglaSubsecao)
    {
        $unidades = $this->_rnLotacao->retornaCacheUnidadePorSubsecao();
        return (isset($unidades[$siglaSubsecao]) ? $unidades[$siglaSubsecao] : array());
    }

    /**
     * Retorna um array contendo todas as unidades do sistema
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboUnidades()
    {
        return $this->_rnLotacao->retornaCacheUnidade();
    }

    public function retornaComboUnidadesDaMinhaSecao()
    {
        return $this->_rnLotacao->retornaCacheUnidadeDaMinhaSecao();
    }

}
