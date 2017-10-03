<?php
class Sisad_Form_Finalizar extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('finalizarForm');
        
        $osctbTipoDocumento = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $tipodoc = $osctbTipoDocumento ->getTipoDocumento();
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('submitFinalizar');
        
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Descrição da finalização:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 4000))
                ->setAttrib('style', 'width: 950px;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($acao,$mofa_ds_complemento,$docm_id_tipo_doc,$docm_ds_hash_red,$submit));
    }
}