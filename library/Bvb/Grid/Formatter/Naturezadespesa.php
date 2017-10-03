<?php
/**
 * @category	Bvb
 * @package		Bvb_Grid_Formatter_Naturezadespesa
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe para formatação da Natureza da Despesa (3.3.90.30.00) dentro do grid (controle Bvb)
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
class Bvb_Grid_Formatter_Naturezadespesa implements Bvb_Grid_Formatter_FormatterInterface {
	/**
	 * Classe construtora 
	 */
	public function __construct($options = array()) {
		// não faz nada!
	}
	
	/**
	 * Formata um número
	 * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
	 */
	public function format($natureza) {
		if (strlen ( $natureza ) != 8) {
			return $natureza;
		}
		
		$naturezaFormatada = '';
		$naturezaFormatada .= substr($natureza, 0, 1) . '.';
		$naturezaFormatada .= substr($natureza, 1, 1) . '.';
		$naturezaFormatada .= substr($natureza, 2, 2) . '.';
		$naturezaFormatada .= substr($natureza, 4, 2) . '.';
		$naturezaFormatada .= substr($natureza, 6, 2);
		
		return $naturezaFormatada;
	}

}