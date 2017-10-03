<?php

/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Sandro Maceno <smaceno@stefanini.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre esfera, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Alinea
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Alinea extends Orcamento_Facade_Base {

    /**
     * Método construtor da classe
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function init() {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Alinea();

        // Define a controle desta action
        $this->_controle = 'alinea';
    }

    /**
     * Retorna dados para combo da inciso
     *
     * @author Gesley Batista Rodrigues
     */
    public function retornaComboAlinea ($codInciso = null) {
  
    if($codInciso) {
        $strWhereInciso = "AND ALIN_ID_INCISO = {$codInciso}";
    }

    $sql = "
            SELECT
                ALIN_ID_ALINEA,
                UPPER(ALIN_VL_ALINEA) ||' - '|| SUBSTR (ALIN_DS_ALINEA, 0, 140)
            FROM
                CEO.CEO_TB_ALIN_ALINEA

            WHERE 1=1
                $strWhereAlinea
                $strWhereInciso
            ORDER BY
                ALIN_ID_ALINEA ASC 
    ";

    $banco = Zend_Db_Table::getDefaultAdapter();
    $dados = $banco->fetchPairs($sql);
    
    $combo = "";
    foreach ($dados as $key => $value) {
        $combo .= '<option value="'.$key.'">'.$value.'</option>';
    }

    return $combo;
    }

    public function retornaAlinea ($cod)
    {
        $sql = "
                SELECT 
                    IMPO_ID_ALINEA
                FROM 
                    CEO_TB_IMPO_IMPORTACAO
                WHERE 
                    IMPO_ID_IMPORTACAO = '$cod'
        ";
    
        $banco = Zend_Db_Table::getDefaultAdapter();
        $dados = $banco->fetchOne($sql);

        return $dados;

    }

}
