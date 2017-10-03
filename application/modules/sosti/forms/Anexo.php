<?php

class Sosti_Form_Anexo extends Zend_Form {

    public function init() {
        $this->setAction('')
             ->setMethod('post')
             ->setAttrib('enctype', 'multipart/form-data');
    }

    public function anexoUnico() {
        $docm_ds_hash_red = new Zend_Form_Element_File('DOCM_DS_HASH_RED');
        $docm_ds_hash_red->setLabel('Inserir Anexo:')
                ->setRequired(false)
                ->addValidator('Size', false, 52428800) // limit to 50m
                ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');

        $anexos = new Zend_Form_Element_File('ANEXOS');
        $anexos->setLabel('Anexos')
                ->setRequired(false)
                ->setIsArray(true)
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setAttribs(array('class' => 'Multi', 'maxlength' => 20, 'multiple' => true))
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');
        
        
        
        $pfds_nr_dcmto_ria_original = new Zend_Form_Element_File('PFDS_NR_DCMTO_RIA_ORIGINAL');
        $pfds_nr_dcmto_ria_original->setLabel('RIA Original:');
        $pfds_nr_dcmto_ria_original->addValidator('MimeType', false, array('application/msword'));
        $pfds_nr_dcmto_ria_original->addValidator('Count', false, 1);
        $pfds_nr_dcmto_ria_original->addValidator('Size', false, '50MB');
        
        $this->addElements(array($docm_ds_hash_red,$anexos,$pfds_nr_dcmto_ria_original));
    }
    
    public function submit(){
        $submit = new Zend_Form_Element_Submit('Salvar');
        $this->addElement($submit);
    }

}