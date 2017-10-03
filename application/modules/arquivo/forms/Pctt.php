<?php

class Arquivo_Form_Pctt extends Zend_Form {
    
    public function init()
    {    
        $this->setAction('')
             ->setMethod('get');
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $assuntosPrincipais = $mapperPctt->getAssuntoPrincipal();
       
        $aqap_cd_assunto_principal = 
                new Zend_Form_Element_Select('AQAP_CD_ASSUNTO_PRINCIPAL');
        $aqap_cd_assunto_principal->setRequired(true)

                                  ->setAttrib('class','AQAP_CD_ASSUNTO_PRINCIPAL')
                                  ->setLabel('Cód - Assunto Principal')
                                  ->addFilter('StripTags')
                                  ->addFilter('StringTrim')
                                  ->addValidator('NotEmpty')
                                  ->addMultiOptions(array(''=>'SELECIONE UM ASSUNTO'));
        foreach ($assuntosPrincipais as $assunto):
            $aqap_cd_assunto_principal
                ->addMultiOptions(array($assunto["AQAP_CD_ASSUNTO_PRINCIPAL"] 
                                        => strtoupper(
                                               $assunto['AQAP_CD_ASSUNTO_PRINCIPAL'] .
                                                " - " . 
                                                $assunto["AQAP_DS_ASSUNTO_PRINCIPAL"])));
        endforeach;
        
        $this->addElements(array($aqap_cd_assunto_principal));
    }
}
