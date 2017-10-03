<?php
class Sisad_Form_Faseadm extends Zend_Form
{
    public function init()
    {
        $this->setAction('sisad/faseadm/save')
             ->setMethod('post');

        $fadm_id_fase = new Zend_Form_Element_Hidden('FADM_ID_FASE');
        $fadm_id_fase->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $fadm_ds_fase = new Zend_Form_Element_Text('FADM_DS_FASE');
        $fadm_ds_fase//->setRequired(true)
                     ->setLabel('Descrição:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));
        
        $fadm_ic_dcto_ativa = new Zend_Form_Element_Radio('FADM_IC_DCTO_FASE');
        $fadm_ic_dcto_ativa->setLabel('Fase documento?')
                           ->setRequired(true)
                           ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não'));

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($fadm_id_fase, $fadm_ds_fase, $fadm_ic_dcto_ativa, $submit));

        //$this->setElementDecorators(array('Label','ViewHelper', 'Errors')); # sempre no final do form
    }
}