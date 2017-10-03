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
 * Contém as funcionalidades disponíveis sobre informativo matricula, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_InformativoMatri
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_InformativoMatri extends Orcamento_Facade_Base
{
    /**
     * Método construtor da classe
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_InformativoMatri();

    }

}
