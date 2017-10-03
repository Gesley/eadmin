<?php
class Sosti_Form_Aviso extends Zend_Form
{
    public function init()
    {
        $this->setAction('save')
             ->setMethod('post');

        $savi_id_aviso = new Zend_Form_Element_Hidden('SAVI_ID_AVISO');
        $savi_id_aviso->addFilter('Int')
                      ->removeDecorator('Label')
                      ->removeDecorator('HtmlTag');

        $savi_ds_aviso = new Zend_Form_Element_Textarea('SAVI_DS_AVISO');
        $savi_ds_aviso->setLabel('Descrição do Aviso:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $savi_ic_visibilidade = new Zend_Form_Element_Checkbox('SAVI_IC_VISIBILIDADE');
        $savi_ic_visibilidade->setLabel('Aviso para apenas executantes das seccionais:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty')
                             ->setCheckedValue('U')
                             ->setUncheckedValue('T');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($savi_id_aviso, $savi_ds_aviso, $savi_ic_visibilidade, $submit));
    }

}