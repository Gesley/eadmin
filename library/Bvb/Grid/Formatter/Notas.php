<?php
/**
 * @category	Bvb
 * @package		Bvb_Grid_Formatter_Notas
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe para formatação do campo Nota de Crédito e/ou Nota de Empenho dentro do grid (controle Bvb), removendo os 6 primeiros caracteres da UG
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * ====================================================================================================
 * TUTORIAL
 * ====================================================================================================
 * Descrever...
 * 
 */
class Bvb_Grid_Formatter_Notas implements Bvb_Grid_Formatter_FormatterInterface {
	/**
	 * Classe construtora 
	 */
	public function __construct($options = array()) {
		// não faz nada!
	}
	
	/**
	 * Formata campo informado, removendo a UG (nos 6 primeiros caracteres)
	 * 
	 * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
	 * @param	string	$nota
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function format ( $nota ) {
	    $retorno = $nota;
	    if ( $nota != 'NE original') {
	        $retorno = substr($nota, 0, -6);
	    }
	    
	    return $retorno;
	}

}