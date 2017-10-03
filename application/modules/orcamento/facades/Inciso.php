<?php

/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre esfera, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Inciso
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Inciso extends Orcamento_Facade_Base {

    /**
     * Método construtor da classe
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Inciso();

        // Define a controle desta action
        $this->_controle = 'inciso';
    }

    /**
     * Retorna dados para combo da inciso
     *
     * @author Gesley Batista Rodrigues
     */
    public function retornaComboInciso () {
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Combo('in');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
           
            $sql = "
SELECT
    INCI_ID_INCISO,
    INCI_VL_INCISO ||' - '|| INCI_DS_INCISO
FROM
    CEO.CEO_TB_INCI_INCISO
ORDER BY
    INCI_ID_INCISO ASC ";

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchPairs($sql);
        }

        return $dados;
    }    

}
