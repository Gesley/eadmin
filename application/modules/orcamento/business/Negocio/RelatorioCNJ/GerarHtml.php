<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Sandro Maceno <smaceno@stefanini.com>
 */

/**
 * Contém as regras negociais sobre regra
 *
 * @category Orcamento
 * @package GerarHtml
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_RelatorioCNJ_GerarHtml {

    public function gerarAnexoI($dados) {

        $manipular = new Orcamento_Business_Negocio_RelatorioCNJ_ManipularDados();
        $matriz = $manipular->manipularAnexoI($dados);
        
        return $matriz;
        
    }

}
