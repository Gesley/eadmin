<?php
/**
 * Contém funcionalidade básicas dos controllers da aplicação
 * 
 * e-Admin
 * Core
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza a factory genérica para instanciação de facades
 *
 * @category Trf1
 * @package Trf1_Facade_Factory
 * @tutorial Para uso das classes facade, a classe chamadora, tipicamente uma
 *           controller, deve utilizar o seguinte código-fonte (como exemplo):
 *           $facade = Trf1_Facade_Factory::retornaInstancia('orcamento',
 *           'despesa');
 *           $facade->incluirDespesa();
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Trf1_Facade_Factory
{

    /**
     * Retorna instância da classe facade informada como parâmetro. Padrão de
     * nomenclatura facade deve ser: [Módulo]_Facade_[Controle]
     *
     * @param string $modulo        
     * @param string $controle        
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public static function retornaInstancia ( $modulo = null, $controle = null )
    {
        // Verifica se o parâmetro $modulo foi informado
        if ( !$modulo )
        {
            throw new Zend_Exception ( 'Necessário informar o módulo.' );
        }
        
        // Verifica se o parâmetro $controle foi informado
        if ( !$controle )
        {
            throw new Zend_Exception ( 'Necessário informar o controle.' );
        }
        
        // Monta a string com o nome completo da classe facade
        $facade = ucfirst ( $modulo ) . '_Facade_' . ucfirst ( $controle );
        
        // Verifica a existência da classe facade
        if ( !class_exists ( $facade ) )
        {
            throw new Zend_Exception ( "Facade $facade não encontrada" );
        }
        
        // Retorna a classe facade
        return new $facade ();
    }

}