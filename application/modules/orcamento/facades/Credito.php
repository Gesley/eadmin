<?php
/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre créditos, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Credito
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Credito extends Orcamento_Facade_Base
{

    /**
     * Método construtor da classe
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Credito ();
        
        // Define a controle desta action
        $this->_controle = 'credito';
    }

    /**
     * Retorna todos os registros a serem apresentando na listagem de
     * inconsistencias
     *
     * @param integer $ano
     *        Deve-se informar o ano para restringir os registros resultantes
     * @param boolean $filtra
     *        Filtra, ou não, registros manualmente acertados
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaInconsistencias ( $ano, $filtra = true )
    {
        // Retorna os dados para exibição na listagem de inconsistencias
        $dados = $this->_negocio->retornaInconsistencias ( $ano, $filtra );
        
        // Devolve os dados
        return $dados;
    }

}