<?php
/**
 * @category	Bvb
 * @package		Bvb_Grid_Formatter_Numerocor
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe para formatação de número (padrão brasileiro) dentro do grid (controle Bvb), incluindo cor conforme o valor
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
class Bvb_Grid_Formatter_Numerocor implements Bvb_Grid_Formatter_FormatterInterface {
	/**
	 * Classe construtora 
	 */
	public function __construct($options = array()) {
		// não faz nada!
	}
	
	/**
	 * Formata número informado como valor e já inclui as tags para exibição em colorido
	 * 
	 * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
	 * @param	numeric	$numero
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function format($numero) {
		$valorCor = new Trf1_Orcamento_Valor ();
		$numero = $valorCor->retornaNumeroFormatadoValor ( $numero, false );
		
		return $numero;
	}

}