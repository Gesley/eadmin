<?php
/**
 * Contém formuçarios da aplicação
*
* e-Admin
* e-Orçamento
* Form
*
* @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
*/

/**
 * Disponibiliza o formulário para entrada de dados sobre esfera.
 *
 * @category Orcamento
 * @package Orcamento_Form_Esfera
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Esfera extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'esfera' );
        
        // Cria o campo ESFE_CD_ESFERA
        $txtCodigo = new Zend_Form_Element_Text ( 'ESFE_CD_ESFERA' );
        
        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel ( 'Esfera:' );
        $txtCodigo->setAttrib ( 'size', 5 );
        $txtCodigo->setAttrib ( 'maxlength', 1 );
        $txtCodigo->addFilter ( 'StringTrim' );
        // $txtCodigo->addFilter ( 'Digits' );
        $txtCodigo->addValidator ( 'Digits' );
        $txtCodigo->setRequired ( true );
        
        // Cria o campo ESFE_DS_ESFERA
        $txtDescricao = new Zend_Form_Element_Text ( 'ESFE_DS_ESFERA' );
        
        // Define opções o controle $txtDescricao
        $txtDescricao->setLabel ( 'Descrição da esfera:' );
        $txtDescricao->setAttrib ( 'size', 40 );
        $txtDescricao->setAttrib ( 'maxlength', 45 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );
        
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );
        
        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', 
        Orcamento_Business_Dados::CLASSE_SALVAR );
        
        // Adiciona os controles no formulário
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtDescricao );
        $this->addElement ( $cmdEnviar );
    }

}