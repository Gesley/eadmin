<?php

/**
 * Contém formuçarios da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Form
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre justificativa.
 *
 * @category Orcamento
 * @package Orcamento_Form_Justificativa
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Justificativa extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'justificativa' );

        // Id regra
        $codigo = new Zend_Form_Element_Text ( 'JUST_ID_JUSTIFICATIVA' );
        $codigo->setLabel ( 'Codigo:' );
        $codigo->setAttrib('size', '10');
        $codigo->setAttrib('style', ' background: #CDCDCD; font-weight: bold; text-align: center;"');

        // Cria o campo titulo da justificativa
        $txtTitulo = new Zend_Form_Element_Text ( 'JUST_DS_TITULO' );

        // Definições do titulo
        $txtTitulo->setLabel ( 'Titulo da justificativa:' );
        $txtTitulo->addFilter ( 'StripTags' );
        $txtTitulo->setRequired ( true );

        // Cria o campo de descrição da justificativa
        $txtDescricao = new Zend_Form_Element_Textarea ( 'JUST_DS_DESCRICAO' );

        // Define opções da descrição
        $txtDescricao->setLabel ( 'Descrição da justificativa:' );
        $txtDescricao->setAttrib ( 'size', 40 );
        $txtDescricao->setAttrib ( 'maxlength', 255 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );

        // Cria o campo situação da justificativa
        $cbJustificativa = new Zend_Form_Element_Hidden ( 'JUST_IC_SITUACAO' );
        // Define opções da situação
        $cbJustificativa->setValue('1');
        $cbJustificativa->setRequired ( true );

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );

        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', Orcamento_Business_Dados::CLASSE_SALVAR );

        // Adiciona os controles no formulário

        $this->addElement ( $codigo );
        $this->addElement ( $txtTitulo );
        $this->addElement ( $txtDescricao );
        $this->addElement ( $cbJustificativa );
        $this->addElement ( $cmdEnviar );
    }

}
