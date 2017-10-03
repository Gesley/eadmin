<?php
class Sisad_Form_AddPermissaoDivulgacao extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('enctype', 'multipart/form-data')
             ->setName('cadastroPartes');

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        
        $docm_id_tipo_doc = new Zend_Form_Element_Text('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc->setRequired(false)
                         ->setAttrib('style', 'width: 540px;')
                         ->setLabel('*Tipo Documento');
        
       

        $submit = new Zend_Form_Element_Submit('Salvar');
      
        $this->addElements(array($docm_id_tipo_doc,
                                 $submit
                                 ));

     }

}