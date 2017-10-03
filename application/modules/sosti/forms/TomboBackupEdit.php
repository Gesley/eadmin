<?php

class Sosti_Form_TomboBackupEdit extends Zend_Form
{

    public function init()
    {
        $this->setAction('')
                ->setMethod('post');

        $LBKP_NR_TOMBO = new Zend_Form_Element_Text('NU_TOMBO');
        $LBKP_NR_TOMBO->setLabel('Número do Tombo:');
        $LBKP_NR_TOMBO->addFilter('StripTags');
        $LBKP_NR_TOMBO->addFilter('StringTrim');
        $LBKP_NR_TOMBO->addValidator('NotEmpty');
        $LBKP_NR_TOMBO->addValidator('Int');
        $LBKP_NR_TOMBO->setRequired(true);
        $LBKP_NR_TOMBO->setAttrib('readonly', 'readonly');
        $LBKP_NR_TOMBO->setAttrib('disabled', true);
        $LBKP_NR_TOMBO->setAttrib('class', 'campo-leitura');
        
        $LBKP_NR_TOMBO_AUX = new Zend_Form_Element_Hidden('LBKP_NR_TOMBO_AUX');
        $LBKP_NR_TOMBO_AUX->removeDecorator('Label');
        $LBKP_NR_TOMBO_AUX->removeDecorator('rros');
        $LBKP_NR_TOMBO_AUX->addFilter('StripTags');
        $LBKP_NR_TOMBO_AUX->addFilter('StringTrim');
        $LBKP_NR_TOMBO_AUX->addValidator('NotEmpty');
        $LBKP_NR_TOMBO_AUX->addValidator('Int');
        $LBKP_NR_TOMBO_AUX->setRequired(true);

        $LBKP_SG_TOMBO = new Zend_Form_Element_Text('TI_TOMBO');
        $LBKP_SG_TOMBO->setLabel('Tipo do Tombo:');
        $LBKP_SG_TOMBO->setAttrib('readonly', 'readonly');
        $LBKP_SG_TOMBO->addFilter('StripTags');
        $LBKP_SG_TOMBO->addFilter('StringTrim');
        $LBKP_SG_TOMBO->addValidator('NotEmpty');
        $LBKP_SG_TOMBO->setRequired(true);
        $LBKP_SG_TOMBO->setAttrib('class', 'campo-leitura');
        $LBKP_SG_TOMBO->setAttrib('disabled', true);

        $LBKP_CD_MATRICULA_CAD = new Zend_Form_Element_Text('LBKP_CD_MATRICULA_CAD');
        $LBKP_CD_MATRICULA_CAD->setLabel('Matrícula cadastrante:');
        $LBKP_CD_MATRICULA_CAD->setAttrib('readonly', 'readonly');
        $LBKP_CD_MATRICULA_CAD->addFilter('StripTags');
        $LBKP_CD_MATRICULA_CAD->addFilter('StringTrim');
        $LBKP_CD_MATRICULA_CAD->addValidator('NotEmpty');
        $LBKP_CD_MATRICULA_CAD->setRequired(true);
        $LBKP_CD_MATRICULA_CAD->setAttrib('class', 'campo-leitura');
        $LBKP_CD_MATRICULA_CAD->setAttrib('disabled', true);

        $LBKP_DH_CADASTRO = new Zend_Form_Element_Text('LBKP_DH_CADASTRO');
        $LBKP_DH_CADASTRO->setLabel('data do cadastro:');
        $LBKP_DH_CADASTRO->setAttrib('readonly', 'readonly');
        $LBKP_DH_CADASTRO->addFilter('StripTags');
        $LBKP_DH_CADASTRO->addFilter('StringTrim');
        $LBKP_DH_CADASTRO->setRequired(true);
        $LBKP_DH_CADASTRO->setAttrib('class', 'campo-leitura');
        $LBKP_DH_CADASTRO->setAttrib('disabled', true);

        $LBKP_IC_ATIVO = new Zend_Form_Element_Checkbox('LBKP_IC_ATIVO');
        $LBKP_IC_ATIVO->setLabel('Tombo Ativo?')
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'));

        $LBKP_SG_SECAO = new Zend_Form_Element_Text('LOTA_SIGLA_SECAO');
        $LBKP_SG_SECAO->setLabel('Seção:');
        $LBKP_SG_SECAO->setAttrib('readonly', 'readonly');
        $LBKP_SG_SECAO->addFilter('StripTags');
        $LBKP_SG_SECAO->addFilter('StringTrim');
        $LBKP_SG_SECAO->setRequired(true);
        $LBKP_SG_SECAO->setAttrib('class', 'campo-leitura');
        $LBKP_SG_SECAO->setAttrib('disabled', true);

        $LBKP_CD_LOTACAO = new Zend_Form_Element_Text('LOTA_COD_LOTACAO');
        $LBKP_CD_LOTACAO->setLabel('Lotação:');
        $LBKP_CD_LOTACAO->setAttrib('readonly', 'readonly');
        $LBKP_CD_LOTACAO->addFilter('StripTags');
        $LBKP_CD_LOTACAO->addFilter('StringTrim');
        $LBKP_CD_LOTACAO->setRequired(true);
        $LBKP_CD_LOTACAO->setAttrib('class', 'campo-leitura');
        $LBKP_CD_LOTACAO->setAttrib('disabled', true);


        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $this->addElements(array($LBKP_NR_TOMBO_AUX, $LBKP_NR_TOMBO, $LBKP_SG_TOMBO, $LBKP_CD_MATRICULA_CAD, $LBKP_DH_CADASTRO, $LBKP_SG_SECAO, $LBKP_CD_LOTACAO, $LBKP_IC_ATIVO, $submit));
    }

}

