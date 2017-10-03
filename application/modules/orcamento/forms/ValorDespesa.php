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
 * Disponibiliza o formulário para entrada de dados de diversos valores de uma
 * dada despesa.
 *
 * @category Orcamento
 * @package Orcamento_Form_ValorDespesa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_ValorDespesa extends Orcamento_Form_Base {

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init () {
        // Definições iniciais do formulário
        $this->retornaFormulario('frmValorDespesa');

        // Cria o campo VL_DESPESA_BASE_EXERC_ANTERIOR
        $nomeCampo = 'VL_DESPESA_BASE_EXERC_ANTERIOR';
        $txtVLDespesa5 = new Zend_Form_Element_Text($nomeCampo);

        /*
         * $this->setName ( 'frmValorDespesa' )->setMethod ( 'post' )->setAttrib
         * ( 'id', 'frmValorDespesa' )->setElementFilters ( array ( 'StripTags',
         * 'StringTrim' ) );
         */

        // Cria o campo VL_DESPESA_AJUSTE_DIPLA
        $txtVLDespesa7 = new Zend_Form_Element_Text('VL_DESPESA_AJUSTE_DIPLA');

        // Define opções o controle $txtVLDespesa7
        $txtVLDespesa7->setLabel('Reajuste ano atual:');
        // $txtVLDespesa7->setRequired ( true );
        $txtVLDespesa7->setAttrib('size', 25);
        $txtVLDespesa7->setAttrib('class', 'valordespesa');

        // Cria o campo VL_DESPESA_RESPONSAVEL
        $txtVLDespesa1 = new Zend_Form_Element_Text('VL_DESPESA_RESPONSAVEL');

        // Define opções o controle $txtVLDespesa1
        $txtVLDespesa1->setLabel('Proposta inicial (R$)');
        // $txtVLDespesa1->setRequired ( true );
        $txtVLDespesa1->setAttrib('size', 25);
        $txtVLDespesa1->setAttrib('class', 'valordespesa');
        $txtVLDespesa1->setAttrib('readonly', true);

        // Cria o campo VL_DESPESA_SOLIC_RESPONSAVEL
        $txtVLDespesa8 = new Zend_Form_Element_Text('VL_DESPESA_SOLIC_RESPONSAVEL');

        // Define opções o controle $txtVLDespesa8
        $txtVLDespesa8->setLabel('Solicitado pelo responsável:');
        // $txtVLDespesa8->setRequired ( true );
        $txtVLDespesa8->setAttrib('size', 25);
        $txtVLDespesa8->setAttrib('class', 'valordespesa');

        // Cria o campo VL_DESPESA_DIPLA
        $txtVLDespesa2 = new Zend_Form_Element_Text('VL_DESPESA_DIPLA');

        // Define opções o controle $txtVLDespesa2
        $txtVLDespesa2->setLabel('Ajuste setorial da Pré-proposta (R$)');
        // $txtVLDespesa2->setRequired ( true );
        $txtVLDespesa2->setAttrib('size', 25);
        $txtVLDespesa2->setAttrib('class', 'valordespesa');

        // Cria o campo VL_DESPESA_BASE_EXERC_ATUAL
        $txtVLDespesa11 = new Zend_Form_Element_Text('VL_DESPESA_BASE_EXERC_ATUAL');

        // Define opções o controle $txtVLDespesa2
        $txtVLDespesa11->setLabel('Base da pré-proposta (R$)');
        // $txtVLDespesa2->setRequired ( true );
        $txtVLDespesa11->setAttrib('size', 25);
        $txtVLDespesa11->setAttrib('class', array('valordespesa'));
        $txtVLDespesa11->setAttrib('id', 'vlde6');

        // Cria o campo VL_DESPESA_BASE_EXERC_ATUAL
        $txtVLDespesa12 = new Zend_Form_Element_Text('VL_DESPESA_BASE_DIFERENCA');

        // Define opções o controle $txtVLDespesa2
        $txtVLDespesa12->setLabel('Composição da Base (R$)');
        // $txtVLDespesa2->setRequired ( true );
        $txtVLDespesa12->setAttrib('size', 25);
        $txtVLDespesa12->setAttrib('class', 'valordespesa');
        $txtVLDespesa12->setAttrib('id', 'vlde12');

        // Cria o campo VL_REAJUSTE_PROPOSTA
        $txtVLDespesa13 = new Zend_Form_Element_Text('VL_REAJUSTE_PROPOSTA');

        // Define opções o controle $txtVLDespesa2
        $txtVLDespesa13->setLabel('Reajuste da pré-proposta (R$)');
        // $txtVLDespesa2->setRequired ( true );
        $txtVLDespesa13->setAttrib('size', 25);
        $txtVLDespesa13->setAttrib('class', 'valordespesa');
        $txtVLDespesa13->setAttrib('id', 'vlde13');

        // Cria o campo VL_DESPESA_CONGRESSO
        $txtVLDespesa3 = new Zend_Form_Element_Text('VL_DESPESA_CONGRESSO');

        // Define opções o controle $txtVLDespesa3
        $txtVLDespesa3->setLabel('Proposta ajustada ao limite:');
        // $txtVLDespesa3->setRequired ( true );
        $txtVLDespesa3->setAttrib('size', 25);
        $txtVLDespesa3->setAttrib('class', 'valordespesa');

        // Cria o campo VL_DESPESA_SECOR
        $txtVLDespesa4 = new Zend_Form_Element_Text('VL_DESPESA_SECOR');

        // Define opções o controle $txtVLDespesa4
        $txtVLDespesa4->setLabel('Orçamento aprovado');
        // $txtVLDespesa4->setRequired ( true );
        $txtVLDespesa4->setAttrib('size', 25);
        $txtVLDespesa4->setAttrib('class', 'valordespesa');

        // Cria o campo VLDE_NR_DESPESA
        $txtNrDespesaValor = new Zend_Form_Element_Hidden('VLDE_NR_DESPESA');

        // Define opções o controle $txtNrDespesaValor
        $txtNrDespesaValor->setRequired(true);
        $txtNrDespesaValor->addFilter('Int');
        $txtNrDespesaValor->addValidator('Int');

        // Adiciona os controles no formulário
        $this->addElement($txtVLDespesa7);
        $this->addElement($txtVLDespesa1);
        $this->addElement($txtVLDespesa8);
        $this->addElement($txtVLDespesa2);
        $this->addElement($txtVLDespesa3);
        $this->addElement($txtVLDespesa4);
        $this->addElement($txtVLDespesa11);
        $this->addElement($txtVLDespesa12);
        $this->addElement($txtVLDespesa13);
        $this->addElement($txtNrDespesaValor);
    }

}
