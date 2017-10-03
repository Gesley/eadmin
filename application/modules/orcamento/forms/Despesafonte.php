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
 * Disponibiliza o formulário para alteração de Fonte em múltiplas despesas.
 *
 * @category Orcamento
 * @package Orcamento_Form_Despesafonte
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Despesafonte extends Orcamento_Form_Base
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
        $this->retornaFormulario ( 'despesafonte' );

        // Cria o campo DESP_ANO
        $txtAno = new Zend_Form_Element_Select ( 'DESP_ANO' );

        // Dados sobre exercícios
        $tbAno = new Orcamento_Business_Negocio_Exercicio ();
        $exercicios = $tbAno->retornaCombo ();

        // Define opções o controle $txtAno
        $txtAno->setLabel ( 'Ano (obrigatório):' );
        $txtAno->addFilter ( 'StringTrim' );
        $txtAno->addFilter ( 'StripTags' );
        $txtAno->addMultiOptions ( array ( '' => 'Selecione' ) );
        $txtAno->addMultiOptions ( $exercicios );
        $txtAno->setRequired ( true );

        // Define mensagem para campos FONTE
        $descricao = 'A lista será carregada após digitar 3 caracteres.';

        // Cria o campo DESP_FONTE_ATUAL
        $txtFonteAtual = new Zend_Form_Element_Text ( 'DESP_FONTE_ATUAL' );

        // Define opções o controle $txtPtres
        $txtFonteAtual->setLabel ( 'Fonte Atual (obrigatório):' );
        $txtFonteAtual->setAttrib ( 'size', 90 );
        $txtFonteAtual->setAttrib ( 'maxlength', 20 );
        $txtFonteAtual->addFilter ( 'StringTrim' );
        $txtFonteAtual->setRequired ( true );
        $txtFonteAtual->setDescription ( $descricao );

        // Cria o campo DESP_PTRES_ATUAL
        $txtPtresAtual = new Zend_Form_Element_Text ( 'DESP_PTRES_ATUAL' );

        // Define opções o controle $txtPtres
        $txtPtresAtual->setLabel ( 'PTRES:' );
        $txtPtresAtual->setAttrib ( 'size', 90 );
        $txtPtresAtual->setAttrib ( 'maxlength', 20 );
        $txtPtresAtual->addFilter ( 'StringTrim' );
        $txtPtresAtual->setDescription ( $descricao );        
        
        // Cria o campo LOTACAO
        $txtUgAtual = new Zend_Form_Element_Text ( 'LOTACAO' );

        // Define opções o controle $txtPtres
        $txtUgAtual->setLabel ( 'UG:' );
        $txtUgAtual->setAttrib ( 'size', 90 );
        $txtUgAtual->setAttrib ( 'maxlength', 20 );
        $txtUgAtual->addFilter ( 'StringTrim' );
        $txtUgAtual->setDescription ( $descricao );           
        
        // Cria o campo DESP_FONTE_NOVO
        $txtFonteNovo = new Zend_Form_Element_Text ( 'DESP_FONTE_NOVO' );

        // Define opções o controle $txtPtres
        $txtFonteNovo->setLabel ( 'Fonte Nova (obrigatório):' );
        $txtFonteNovo->setAttrib ( 'size', 90 );
        $txtFonteNovo->setAttrib ( 'maxlength', 20 );
        $txtFonteNovo->addFilter ( 'StringTrim' );
        $txtFonteNovo->setRequired ( true );
        $txtFonteNovo->setDescription ( $descricao );

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Button ( 'DESP_INFORMAR_DESPESAS' );

        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel ( 'Informar despesas' );
        // $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', 'ceo_salvar' );

        // Cria o botão de enviar
        $cmdLimpa = new Zend_Form_Element_Button ( 'DESP_LIMPA_TUDO' );

        // Define opções do controle $cmdLimpa
        $cmdLimpa->setLabel ( 'Habilitar campos' );
        $cmdLimpa->setAttrib ( 'class', 'ceo_voltar' );

        // Adiciona os controles no formulário
        $this->addElement ( $txtAno );
        $this->addElement ( $txtFonteAtual );
        $this->addElement ( $txtPtresAtual );
        $this->addElement ( $txtUgAtual );
        $this->addElement ( $txtFonteNovo );
        $this->addElement ( $cmdEnviar );
        $this->addElement ( $cmdLimpa );
    }

    /**
     * Método de validação deste formulário
     *
     * @see Zend_Form::isValid()
     */
    public function isValid ( $dados )
    {
        // @TODO: FAzer a validação de PTRES (que devem ser diferentes)
        //Zend_Debug::dump ( 'Validações...' );
        //Zend_Debug::dump ( $dados );

        return parent::isValid ( $dados );
    }
}
