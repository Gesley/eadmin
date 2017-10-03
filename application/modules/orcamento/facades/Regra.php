<?php

/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre regra, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Regra
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Regra extends Orcamento_Facade_Base
{

    /**
     * Método construtor da classe
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Regra ();
        
        // Define a controle desta action
        $this->_controle = 'regra';
    }

    /**
     * Método genérico de exclusão
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluir ( $chave )
    {
        // Exclui registro definitivamente do banco
        $resultado = $this->exclusaoFisica ( $chave );
        
        // Devolve o resultado da operação
        return $resultado;
    }

    public function aplicar ( $chave )
    {
        // Retorna opções para criação do grid
        $resultado = $this->_negocio->aplicarRegra ( $chave );
        
        // Devolve o resultado
        return $resultado;
    }

}