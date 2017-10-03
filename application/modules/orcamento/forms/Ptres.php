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
 * Disponibiliza o formulário para entrada de dados sobre programa de trabalho
 * resumido.
 *
 * @category Orcamento
 * @package Orcamento_Form_Ptres
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Ptres extends Orcamento_Form_Base
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
        $this->retornaFormulario ( 'ptres' );
        
        // Cria o campo PTRS_CD_PT_RESUMIDO
        $txtCodigo = new Zend_Form_Element_Text ( 'PTRS_CD_PT_RESUMIDO' );
        
        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel ( 'Código resumido:' );
        $txtCodigo->setAttrib ( 'size', '10' );
        $txtCodigo->setAttrib ( 'maxlength', 6 );
        $txtCodigo->addFilter ( 'StringTrim' );
        // $txtCodigo->addFilter ( 'Digits' );
        $txtCodigo->addValidator ( 'Digits' );
        $txtCodigo->setRequired ( true );
        
        // Cria o campo PTRS_DS_PT_RESUMIDO
        $txtDescricao = new Zend_Form_Element_Textarea ( 'PTRS_DS_PT_RESUMIDO' );
        
        // Define opções o controle $txtDescricao
        $txtDescricao->setLabel ( 'Descrição do PTRES:' );
        $txtDescricao->setAttrib ( 'size', 40 );
        $txtDescricao->setAttrib ( 'maxlength', 140 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );
        
        // Cria o campo PTRS_SG_PT_RESUMIDO
        $txtSigla = new Zend_Form_Element_Text ( 'PTRS_SG_PT_RESUMIDO' );
        
        // Define opções o controle $txtDescricao
        $txtSigla->setLabel ( 'Sigla:' );
        $txtSigla->setAttrib ( 'size', 20 );
        $txtSigla->setAttrib ( 'maxlength', 12 );
        $txtSigla->addFilter ( 'StringTrim' );
        $txtSigla->setRequired ( true );
        
        // Cria o campo PTRS_CD_PT_COMPLETO
        $txtPtCompleto = new Zend_Form_Element_Text ( 'PTRS_CD_PT_COMPLETO' );
        
        // Define opções o controle $txtDescricao
        $txtPtCompleto->setLabel ( 'Programa de trabalho completo:' );
        $txtPtCompleto->setAttrib ( 'size', 20 );
        $txtPtCompleto->setAttrib ( 'maxlength', 50 );
        $txtPtCompleto->addFilter ( 'StringTrim' );
        // $txtSigla->setRequired ( true );
        
        // Cria o campo PTRS_CD_UNID_ORCAMENTARIA
        $cboUO = new Zend_Form_Element_Select ( 'PTRS_CD_UNID_ORCAMENTARIA' );
        
        // Dados sobre Unidades orçamentárias
        $tbUo = new Trf1_Orcamento_Negocio_Uo ();
        
        // Define opções o controle $txtDescricao
        $cboUO->setLabel ( 'Unidade orçamentária:' );
        $cboUO->addFilter ( 'StringTrim' );
        $cboUO->addFilter ( 'StripTags' );
        $cboUO->addMultiOptions ( array ( '' => 'Selecione' ) );
        $cboUO->addMultiOptions ( $tbUo->retornaCombo () );
        $cboUO->setRequired ( true );

        // Cria o campo DESP_AA_DESPESA
        $txtAno = new Zend_Form_Element_Select ( 'PTRS_AA_EXERCICIO' );

        // Dados sobre exercícios
        $tbAno = new Orcamento_Business_Negocio_Exercicio ();
        $exercicios = $tbAno->retornaCombo ();

        // Define opções o controle $txtAno
        $txtAno->setLabel ( 'Ano:' );
        $txtAno->addFilter ( 'StringTrim' );
        $txtAno->addFilter ( 'StripTags' );
        $txtAno->addMultiOptions ( array ( '' => 'Selecione' ) );
        $txtAno->addMultiOptions ( $exercicios );
        $txtAno->setRequired ( true );        
        
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );
        
        // Define opções do controle $cmdEnviar
        $classeSubmit = Orcamento_Business_Dados::CLASSE_SALVAR;
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', $classeSubmit );
        
        // Adiciona os controles no formulário
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtDescricao );
        $this->addElement ( $txtSigla );
        $this->addElement ( $txtPtCompleto );
        $this->addElement ( $cboUO );
        $this->addElement ( $txtAno );
        $this->addElement ( $cmdEnviar );
    }

}