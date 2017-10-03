<?php
/**
 * FlashMessages view helper
 * application/modules/admin/views/helpers/FlashMessages.php
 *
 * Formatar saida de variáves encodadas.
 *
 * @author Leonan Alves dos Anjos
 * @license Free to use - no strings.
 */
class App_View_Helper_Decodehtmlentities extends Zend_View_Helper_Abstract
{
	public function decodehtmlentities($input)
	{
		return nl2br( ( html_entity_decode(   htmlspecialchars_decode($input, ENT_QUOTES  ),ENT_QUOTES, 'UTF-8' )  ) );   
	}
}