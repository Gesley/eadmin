<?php

class Sosti_Form_LabCadastroTipoSoftware extends Zend_Form
{

    public function init()
    {

        $this->setAction('')
                ->setMethod('post');

        $ltps_id_tp_software = new Zend_Form_Element_Hidden('LTPS_ID_TP_SOFTWARE');
        $ltps_id_tp_software->addFilter('Int')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $ltps_ds_tp_software = new Zend_Form_Element_Text('LTPS_DS_TP_SOFTWARE');
        $ltps_ds_tp_software->setLabel('*Descrição do tipo de software:')
                ->addFilter('StripTags')
                ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
                ->setAttrib('style', 'width: 300px;')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', false, array(5, 60))
                ->setRequired(true)
                ->addValidator('Alnum', false, true)
                ->setAttrib('maxLength', 60);

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $this->addElements(array($ltps_id_tp_software, $ltps_ds_tp_software, $submit, $obrigatorio));
    }

}