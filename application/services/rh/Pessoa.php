<?php

/**
 * @category            Services
 * @package		Services_Rh_Pessoa
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Daniel Rodrigues
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial            Tutorial abaixo
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
class Services_Rh_Pessoa
{

    /**
     * Armazena a quantidade de vinculos
     *
     * @var Trf1_Rh_Negocio_Pessoa
     */
    private $_rnPessoa;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Daniel Rodrigues
     */
    public function __construct()
    {
        $this->_rnPessoa = new Trf1_Rh_Negocio_Pessoa();
    }

    /**
     * Retorna um array todas as pessoas fisicas cadastradas no TRF 1
     * @param $termo
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboPessoasFisicasTrf1($termo = null)
    {
        if (is_null($termo)) {
            return $this->_rnPessoa->retornaCachePessoaFisicaTrf1();
        } else {
            return $this->_rnPessoa->retornaComboPessoasFisicasTrf1($termo);
        }
    }

    public function retornaComboPessoasFisicasExterna($termo = null)
    {
        if (is_null($termo)) {
            return array();
        } else {
            return $this->_rnPessoa->retornaComboPessoasFisicasExternas($termo);
        }
    }

    /**
     * Retorna um array todas as pessoas fisicas cadastradas no TRF 1
     * @param $termo
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboPessoasJuridicasTrf1($termo = null)
    {
        if (is_null($termo)) {
            return $this->_rnPessoa->retornaCachePessoasJuricasTrf1();
        } else {
            return $this->_rnPessoa->retornaComboPessoasJuridicasTrf1($termo);
        }
    }

    /**
     * Retorna um array todas as pessoas fisicas cadastradas no TRF 1
     * agrupadas por lotacao
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboPessoasFisicasTrf1AgrupadasPorLotacao()
    {
        return $this->_rnPessoa->retornaCachePessoaFisicaTrf1AgrupadasPorLotacao();
    }

    /**
     * Retorna um array todas as pessoas fisicas cadastradas no TRF 1
     * agrupadas por lotacao
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaComboPessoasFisicasTrf1AgrupadasPorMinhasUnidades()
    {
        return $this->_rnPessoa->retornaCachePessoaFisicaTrf1AgrupadasPorMinhasUnidades();
    }

    /**
     * Retorna as unidades da minha Secao
     * @author Daniel Rodrigues
     * @return array
     */
    public function retornaComboUnidadesDaMinhaSecao()
    {
        return $this->_rnPessoa->retornaCacheUnidadeDaMinhaSecao();
    }

    /**
     * Retorna o agrupamento de responsáveis,divididos por Unidade
     * @author Daniel Rodrigues
     * @return array
     */
    public function retornaComboResponsaveisAgrupadosPorUnidade()
    {
        return $this->_rnPessoa->retornaCacheResponsaveisAgrupadosPorUnidade();
    }

    /**
     * Retorna o agrupamento de responsáveis,agrupados por minhas unidades
     * @author Daniel Rodrigues
     * @return array
     */
    public function retornaComboResponsaveisAgrupadosPorMinhasUnidade()
    {
        return $this->_rnPessoa->retornaCacheResponsaveisAgrupadosPorMinhasUnidade();
    }

    /**
     * Retorna o agrupamento de Unidades,divididos por pessoas responsáveis pela mesma
     * @author Daniel Rodrigues
     * @return array
     */
    public function retornaComboUnidadesAgrupadasPorResponsavel()
    {
        return $this->_rnPessoa->retornaCacheUnidadesAgrupadasPorResponsavel();
    }

}