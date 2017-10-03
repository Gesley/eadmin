<?php

/**
 * Classe que centraliza todas as constantes do ImportBuffer.
 * 
 * e-Admin
 * Core
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Classe para seleção das constantes do ImportBuffer.
 *
 * @category ImportBuffer
 * @package ImportBuffer_Constants
 * @tutorial Adicionar nessa classe todos os padrões de modelo em constantes.
 *          Exemplo: const PADRAO1 = "padrao1";
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class ImportBuffer_Constants {

    /**
     * Constantes utilizadas na tipagem dos campos do buffer.
     * OBS.: não alterá-las, pois faz parte do core.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    const INT = "INT";
    const DECIMAL = "DECIMAL";
    const STRING = "STRING";
    const LOOP = "LOOP";
    const SEQUENCIAL = "SEQUENCIAL";
    const OBJETO = "OBJETO";
    const POSITIVO = "POSITIVO";
    const NEGATIVO = "NEGATIVO";
    
    /**
     * Constates com os nomes dos modelos de JSON.
     * 
     * OBS.: o valor da constante necessariamente precisa ser o mesmo do nome do
     * arquivo .JSON.
     * 
     * PS.: pode adicionar quantas constantes forem necessárias.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    const PADRAO1 = "padrao1";
    const PADRAO2 = "padrao2";
    const PADRAO3 = "padrao3";

}
