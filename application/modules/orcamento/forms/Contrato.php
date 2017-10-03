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
 * Disponibiliza o formulário para entrada de dados sobre contrato da despesa.
 *
 * @category Orcamento
 * @package Orcamento_Form_ContratoDespesa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Contrato extends Orcamento_Form_Base {

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init() {

        $this->setName('frmContrato')->setMethod('post')->setAttrib(
            'id', 'frmContrato')->setElementFilters(
            array('StripTags', 'StringTrim'));

        $txtNrContrato = new Zend_Form_Element_Text('CTRD_NR_CONTRATO');
        $txtNrContrato->setLabel('Número do contrato:')->setAttribs(
            array('size' => 25, 'maxlength' => '9', 'class' => 'nrcontrato'))->addValidator(
            'StringLength', false, array(9, 9))->setRequired();

        $txtEmpresaDespesa = new Zend_Form_Element_Text(
            'CTRD_NM_EMPRESA_CONTRATADA');
        $txtEmpresaDespesa->setLabel('Nome da empresa contratada:')->setRequired(
            false)->setAttribs(array('size' => 60, 'maxlength', '60'));

        $txtCpfCnpjDespesa = new Zend_Form_Element_Text('CTRD_CPFCNPJ_DESPESA');
        $txtCpfCnpjDespesa->setLabel('CPF/CNPJ da contratada:')->setRequired(
            false)->setAttribs(array('size' => 30, 'maxlength', '17'))->addValidator(
            'StringLength', false, array(14, 18))->setDescription(
            'Informe apenas os números do CPF/CNPJ');

        $txtDTInicioDespesa = new Zend_Form_Element_Text(
            'CTRD_DT_INICIO_VIGENCIA');
        $txtDTInicioDespesa->setLabel('Início da vigência:')->setRequired(
            false)->setAttrib('size', '14')->setAttrib('class', 'datepicker');

        $txtDTFimDespesa = new Zend_Form_Element_Text(
            'CTRD_DT_TERMINO_VIGENCIA');
        $txtDTFimDespesa->setLabel('Término da vigência:')->setRequired(
            false)->setAttrib('size', '14')->setAttrib('class', 'datepicker');

        $txtVlDespesa = new Zend_Form_Element_Text('CTRD_VL_DESPESA');
        $txtVlDespesa->setLabel('Valor do Contrato:')->setRequired(false)->setAttrib(
            'size', '18')->setAttrib('class', 'valordespesa');

        $txtNrDespesa = new Zend_Form_Element_Text('CTRD_NR_DESPESA');
        $txtNrDespesa->setRequired(true)->addFilter('Int')->addValidator(
            'Int');
        $txtNrDespesa->setLabel('Despesa:')->setRequired(TRUE)
            ->setAttrib('readOnly', 'TRUE');

        $txtIdDespesa = new Zend_Form_Element_Hidden(
            'CTRD_ID_CONTRATO_DESPESA');
        $txtIdDespesa->setRequired(true)->addFilter('Int')->addValidator(
            'Int');

        // Botão submit
        $cmdSubmit = new Zend_Form_Element_Button('Salvar');
        $cmdSubmit->setLabel('Salvar')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_salvar');

        $this->addElements(
            array($txtNrContrato, $txtEmpresaDespesa, $txtCpfCnpjDespesa,
                $txtDTInicioDespesa, $txtDTFimDespesa, $txtVlDespesa, $txtNrDespesa, $txtIdDespesa, $cmdSubmit));
    }

}
