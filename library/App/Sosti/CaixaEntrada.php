<?php
/**
 * Esta classe serve para exibir todos os dados da caixa de entrada
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta[at]trf1.jus.br
 * @license Free to use - no strings.
 */
class App_Sosti_CaixaEntrada extends Zend_View_Helper_Abstract
{
    
    public static function getCaixaEntrada($idCaixa)
    {
        $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        return $caixaEntrada->getCaixaEntrada($idCaixa);
    }
}