<?php

class Sosti_Form_BackupCadastro extends Zend_Form
{

    public function init()
    {
        $this->setAction('')
                ->setMethod('post');

        $LBKP_NR_TOMBO = new Zend_Form_Element_Text('LBKP_NR_TOMBO');
        $LBKP_NR_TOMBO->setLabel('*Número do Tombo:');
        $LBKP_NR_TOMBO->addFilter('StripTags');
        $LBKP_NR_TOMBO->addFilter('StringTrim');
//        $LBKP_NR_TOMBO->addValidator('NotEmpty');
        $LBKP_NR_TOMBO->addValidator('Alnum');
        $LBKP_NR_TOMBO->setAttrib('maxlength', 10);
        $LBKP_NR_TOMBO->setRequired(true);

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $this->addElements(array($LBKP_NR_TOMBO, $submit, $obrigatorio));
    }

}