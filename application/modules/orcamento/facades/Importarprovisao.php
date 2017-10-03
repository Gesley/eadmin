<?php

/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers.
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as funcionalidades disponíveis através de camada intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Importarprovisao
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Importarprovisao extends Orcamento_Facade_Base {

    /**
     * Método construtor da classe
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Define a controle desta action
        $this->_controle = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();
        
        $upperClass = ucfirst($this->_controle);
        $classControle = "Orcamento_Business_Negocio_{$upperClass}";
        
        // Instancia a classe negocial
        $this->_negocio = new $classControle();

    }

}
