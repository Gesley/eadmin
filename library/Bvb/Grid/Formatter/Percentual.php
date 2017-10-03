<?php
/**
 * Contém formatação de número percentual dentro do grid (controle Bvb)
 * 
 * Bvb
 * Grid
 * Formatter
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe para manipulação genérica dos grids do e-Orçamento
 *
 * @category Bvb
 * @package Bvb_Grid_Formatter_Percentual
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Bvb_Grid_Formatter_Percentual implements Bvb_Grid_Formatter_FormatterInterface {
    /**
     * Classe construtora
     */
    public function __construct($options = array()) {
        // não faz nada!
    }

    /**
     * Formata número como percentual
     *
     * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
     * @param	numeric	$numero
     * @return	string
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function format ( $numero ) {
        $valor = new Trf1_Orcamento_Valor ();
        $numero = $valor->retornaNumeroFormatadoPercentual ( $numero );
        
        return $numero;
    }

}