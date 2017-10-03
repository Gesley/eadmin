<?php

class Arquivo_Form_Classe extends Zend_Form {
    
    
    public $mapperPctt;
    
    public function init()
    {    
        
        $this->mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        
        $this->setAction('')
             ->setMethod('get');
        
        $assuntosPrincipais = $this->mapperPctt->getAssuntoPrincipal();
       
        $aqap_cd_assunto_principal = 
                new Zend_Form_Element_Select('AQAP_CD_ASSUNTO_PRINCIPAL');
        $aqap_cd_assunto_principal->setRequired(true)
                                  ->setAttrib('onChange','assuntoSecundario()')
                                  ->setLabel('Cód - Assunto Principal:')
                                  ->addFilter('StripTags')
                                  ->addFilter('StringTrim')
                                  ->addValidator('NotEmpty')
                                  ->addMultiOptions(array(''=>'SELECIONE UM ASSUNTO'));
        foreach ($assuntosPrincipais as $assunto):
            $aqap_cd_assunto_principal
                ->addMultiOptions(array($assunto["AQAP_CD_ASSUNTO_PRINCIPAL"] 
                                        => strtoupper(
                                               $assunto['AQAP_CD_ASSUNTO_PRINCIPAL']
                                               . ' - ' . 
                                                $assunto["AQAP_DS_ASSUNTO_PRINCIPAL"])));
        endforeach;

        $this->addElements(array($aqap_cd_assunto_principal));

    }
}
