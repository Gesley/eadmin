<?php
/**
 * Contém classe para validação de elementos de formulário
 * 
 * e-Admin
 * e-Orçamento
 * Business - Validação
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as validações referentes à seleção de um PA no campo
 *
 * @category Orcamento
 * @package Orcamento_Business_Validacao_Pa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
require_once 'Zend/Validate/Abstract.php';

class Orcamento_Business_Validacao_Pa extends Zend_Validate_Abstract
{
    const PA_NAO_SELECIONADO = 'paNaoSelecionado';

    protected static $_filter = null;
    protected $_tipoProcesso;

    protected $_messageTemplates = array ( 
            self::PA_NAO_SELECIONADO => "O processo informado não existe." );


    public function defineTipo( $tipo ) {
        $this->_tipoProcesso = $tipo;
        return $this;
    }

    public function retornaTipo() {
        return $this->_tipoProcesso;
    }

    /**
     * Valida seleção do processo administrativo
     *
     * @see Zend_Validate_Interface::isValid()
     * @param string $campoPA
     *        Valor do campo de processo
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function isValid ( $campoPA )
    {
        // remove caracteres
        $campoPA = preg_replace("/[^0-9,.]/", "", $campoPA);

        // verifica o tipo do processo
        $tipoProcesso = $this->_tipoProcesso->getValue ();
        
        // model
        $negocio = new Trf1_Orcamento_Negocio_Rdo ();

        // verifica se o processo existe no banco de dados
        if ($tipoProcesso == 0) {
            // consulta processo digital
            $consulta = $negocio->validaProcessoDigital ( $campoPA );
        } else {
            // consulta processo fisico 
            $consulta = $negocio->validaProcessoFisico ( $campoPA );
        }

        if ( $consulta == false )
        {
            $this->_error ( self::PA_NAO_SELECIONADO );
            return false;
        }
        
        return true;
    }

}
