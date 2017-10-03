<?php

class Sisad_Form_MembroDistProc extends Zend_Form {

    public function init() {
        $this->setAction('')
                ->setMethod('post');

        /* CAMPOS HIDENS */
        $ccpa_cd_orgao_julgador = new Zend_Form_Element_Hidden('CCPA_CD_ORGAO_JULGADOR');
        $ccpa_cd_servidor = new Zend_Form_Element_Hidden('CCPA_CD_SERVIDOR');
        $ccpa_ic_ativo = new Zend_Form_Element_Hidden('CCPA_IC_DISTRIBUICAO');
        
        $ccpa_cd_servidor->removeDecorator('Label');
        $ccpa_ic_ativo->removeDecorator('Label');
        /* FIM CAMPOS HIDENS */
        $pnat_no_pessoa = new Zend_Form_Element_Text('PNAT_NO_PESSOA');
        $pnat_no_pessoa->setRequired(true)
                ->setLabel('Nome do membro:')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->addValidator('StringLength', false, array(5, 200))
                ->setDescription('As letras devem estar maiúsculas.');

        $dtpd_no_tipo = new Zend_Form_Element_Checkbox('CCPA_IC_ATIVO');
        $dtpd_no_tipo->setRequired(true)
                ->setLabel('Incluir na distribuição:')
                ->setRequired(true);



        $submit = new Zend_Form_Element_Submit('Adicionar');

        $this->addElements(array($ccpa_cd_orgao_julgador,
            $ccpa_cd_servidor,
            $ccpa_ic_ativo,
            $pnat_no_pessoa,
            $dtpd_no_tipo,
            $submit));
    }

}