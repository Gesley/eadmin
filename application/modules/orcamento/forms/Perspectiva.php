<?php
/**
 * Contém formuçarios da aplicação
*
* e-Admin
* e-Orçamento
* Form
*
*/

/**
 * Disponibiliza o formulário para entrada de dados sobre perspectiva.
 *
 * @category Orcamento
 * @package Orcamento_Form_Perspectiva
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Perspectiva extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'perspectiva' );
        
        // Cria o campo
        $txtCodigo = new Zend_Form_Element_Text ( 'PERS_ID_PERSPECTIVA' );
        
        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel ( 'Codigo:' );
        $txtCodigo->setAttrib ( 'size', 5 );
        $txtCodigo->setAttrib ( 'maxlength', 1 );
        $txtCodigo->addFilter ( 'StringTrim' );
        $txtCodigo->addValidator ( 'Digits' );
        
        // Cria o campo 
        $txtDescricao = new Zend_Form_Element_Text ( 'PERS_TX_PERSPECTIVA' );
        
        // Define opções o controle $txtDescricao
        $txtDescricao->setLabel ( 'Descrição da perspectiva:' );
        $txtDescricao->setAttrib ( 'size', 40 );
        $txtDescricao->setAttrib ( 'maxlength', 45 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );
        
        // Ano
        $txtAno = new Zend_Form_Element_Select ( 'PERS_AA_EXERCICIO' );

        // Dados sobre exercícios
        $tbAno = new Orcamento_Business_Negocio_Exercicio ();
        $exercicios = $tbAno->retornaCombo ();

        // Define opções o controle $txtAno
        $txtAno->setLabel ( 'Ano exercicio:' );
        $txtAno->addFilter ( 'StringTrim' );
        $txtAno->addFilter ( 'StripTags' );
        $txtAno->addMultiOptions ( array ( '' => 'Selecione' ) );
        $txtAno->addMultiOptions ( $exercicios );
        $txtAno->setRequired ( true );
        
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
        $this->addElement ( $txtAno );
        $this->addElement ( $cmdEnviar );
    }

}