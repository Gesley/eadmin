<?php

class Sosti_Form_LabCadastroTipoServico extends Zend_Form
{

    public function init()
    {
        /*
         * lhdw_id_hardware,
         * lhdw_ds_hardware,
         * lhdw_cd_material,
         * lhdw_cd_marca,
         * lhdw_cd_modelo,
         * lhdw_nr_processo
         */
        $this->setAction('')
                ->setMethod('post')
                ->setName('CadastroTipoServico');

        $tpse_id_tp_servico = new Zend_Form_Element_Hidden('TPSE_ID_TP_SERVICO');
        $tpse_id_tp_servico->removeDecorator('label');

        $tpse_ds_tp_servico = new Zend_Form_Element_Text('TPSE_DS_TP_SERVICO');
        $tpse_ds_tp_servico->setRequired(true)
                ->setLabel('*Descrição:')
                ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->setAttrib('size', 60)
                ->addValidator('Alnum', false, true)
                ->setAttrib('maxLength', 60)
                ->addValidator('StringLength', false, array(5, 60));

        $tpse_ic_ativo = new Zend_Form_Element_Select('TPSE_IC_ATIVO');
        $tpse_ic_ativo->setRequired(true)
                ->setLabel('*Ativo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->addMultiOption(' ', ':: Selecione ::')
                ->addMultiOptions(array('S' => 'Sim', 'N' => 'Não'));

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $this->addElements(array($tpse_id_tp_servico,
            $tpse_ds_tp_servico,
            $tpse_ic_ativo,
            $submit,
            $obrigatorio));
    }

}