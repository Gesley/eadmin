<?php

class Sosti_Form_Marca extends Zend_Form
{

    public function init()
    {
        $this->setAction('')
                ->setMethod('post');

        $marc_id_marca = new Zend_Form_Element_Hidden('MARC_ID_MARCA');
        $marc_id_marca->addFilter('Int')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $marc_cd_mat_inclusao = new Zend_Form_Element_Hidden('MARC_CD_MAT_INCLUSAO');
        $marc_cd_mat_inclusao->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $marc_dt_inclusao = new Zend_Form_Element_Hidden('MARC_DT_INCLUSAO');
        $marc_dt_inclusao->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $marc_ds_marca = new Zend_Form_Element_Text('MARC_DS_MARCA');
        $marc_ds_marca->setLabel('*Descrição da Marca:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('maxLength', 60)
                ->addValidator('StringLength', false, array(2, 60))
                ->setRequired(true);

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $this->addElements(array($marc_id_marca, $marc_ds_marca, $marc_cd_mat_inclusao,
            $marc_dt_inclusao, $submit, $obrigatorio));
    }

}