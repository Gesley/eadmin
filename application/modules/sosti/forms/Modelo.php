<?php
class Sosti_Form_Modelo extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $mode_id_modelo = new Zend_Form_Element_Hidden('MODE_ID_MODELO');
        $mode_id_modelo->addFilter('Int')
                      ->removeDecorator('Label')
                      ->removeDecorator('HtmlTag');
        
        $mode_cd_mat_inclusao = new Zend_Form_Element_Hidden('MODE_CD_MAT_INCLUSAO');
        $mode_cd_mat_inclusao->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');
        
        $mode_dt_inclusao = new Zend_Form_Element_Hidden('MODE_DT_INCLUSAO');
        $mode_dt_inclusao->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');

        $mode_ds_modelo = new Zend_Form_Element_Text('MODE_DS_MODELO');
        $mode_ds_modelo->setLabel('Descrição do Modelo:')
                      ->addFilter('StripTags')
                      ->setAttrib('style', 'width: 735px;')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($mode_id_modelo, $mode_cd_mat_inclusao, $mode_dt_inclusao,
                                 $mode_ds_modelo, $submit));
    }

}