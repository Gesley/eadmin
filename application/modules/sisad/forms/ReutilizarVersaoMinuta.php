<?php
class Sisad_Form_ReutilizarVersaoMinuta extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('reutilizar');
        
        $osctbTipoDocumento = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $tipodoc = $osctbTipoDocumento ->getTipoDocumento();
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('submitReutilizar');

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($acao,$docm_id_tipo_doc,$docm_ds_hash_red,$submit));
    }
}