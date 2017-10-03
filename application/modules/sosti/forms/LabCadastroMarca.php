<?php
class Sosti_Form_LabCadastroMarca extends Zend_Form
{
    public function init()
    {
       
       $this->setAction('')
             ->setMethod('post')
             ->setName('CadastroMarca');

        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('CadastroMarca');
        
        $marc_id_marca = new Zend_Form_Element_Text('MARC_ID_MARCA');
        $marc_id_marca->setRequired(true)
                ->setLabel('Código:')
                ->setOptions(array('style' => 'width: 50px','disabled' => 'disabled'));
        
        $marc_ds_marca = new Zend_Form_Element_Text('MARC_DS_MARCA');
        $marc_ds_marca->setRequired(true)
                     ->setLabel('Descrição:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 200));

        $marc_cd_mat_inclusao = new Zend_Form_Element_Hidden('acao');
        $marc_cd_mat_inclusao->setValue('CadastroMarca');        

        $marc_dt_inclusao = new Zend_Form_Element_Hidden('acao');
        $marc_dt_inclusao->setValue('CadastroMarca');    
        
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($acao,$marc_id_marca,$marc_ds_marca,$marc_cd_mat_inclusao,
                                 $marc_dt_inclusao,$submit));
    }
}