<?php
class Sosti_Form_LabCadastroTipoUsuario extends Zend_Form{

    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
             ->setName('CadastroTipoUsuario');
        
        $ltpu_id_tp_usuario = new Zend_Form_Element_Hidden('LTPU_ID_TP_USUARIO');
        $ltpu_id_tp_usuario->removeDecorator('label');
        
        $ltpu_ds_tp_usuario = new Zend_Form_Element_Text('LTPU_DS_TP_USUARIO');
        $ltpu_ds_tp_usuario->setRequired(true)
                 ->setLabel('*Descrição do Tipo de Usuário:')
                 ->addFilter('StripTags')
                ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
                 ->addFilter('StringTrim')
//                 ->addValidator('NotEmpty')
                 ->addValidator('Alnum', false, true)
                 ->setAttrib('maxLength', 60)
                 ->setAttrib('size', 60)
                ->addValidator('StringLength', false, array(5, 60));

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');
        
        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');
        
        $this->addElements(array($ltpu_id_tp_usuario,$ltpu_ds_tp_usuario,$submit, $obrigatorio));
    }
}