<?php

class Sosti_Form_Ctss extends Zend_Form {

    public function init() {
        $this->setAction('save')
                ->setMethod('post');

        $ctss_id_categoria_servico = new Zend_Form_Element_Text('CTSS_ID_CATEGORIA_SERVICO');
        $ctss_id_categoria_servico->setLabel('id_categoria_servico')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $ctss_id_servico_sistema = new Zend_Form_Element_Text('CTSS_ID_SERVICO_SISTEMA');
        $ctss_id_servico_sistema->setLabel('id_servico_sistema')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $ctss_nm_categoria_servico = new Zend_Form_Element_Select('CTSS_NM_CATEGORIA_SERVICO');
        $ctss_nm_categoria_servico->setLabel('*Categoria de Serviço')
                ->setRequired(false)
                ->addMultiOptions(array('' => ''));
        
        $SosTbCtssCategServSistema = new Application_Model_DbTable_SosTbCtssCategServSistema();
        //$CategoriaServico = $SosTbCtssCategServSistema->fetchAll('CTSS_ID_CATEGORIA_SERVICO IN (1,2,3,4,5,6)','CTSS_NM_CATEGORIA_SERVICO ASC');
        $CategoriaServico = $SosTbCtssCategServSistema->fetchAll(null,'CTSS_NM_CATEGORIA_SERVICO ASC');
        foreach ($CategoriaServico as $Categoria):
            $ctss_nm_categoria_servico->addMultiOptions(array($Categoria['CTSS_ID_CATEGORIA_SERVICO'] => $Categoria["CTSS_NM_CATEGORIA_SERVICO"]));
        endforeach;
        
        $ctss_ds_categoria_servico = new Zend_Form_Element_Text('CTSS_DS_CATEGORIA_SERVICO');
        $ctss_ds_categoria_servico->setLabel('ds_categoria_servico')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');


        $this->addElements(array($ctss_nm_categoria_servico,));
    }

}
