<?php
class Sosti_Form_LicencaSoftw extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $lisw_id_software = new Zend_Form_Element_Hidden('LISW_ID_SOFTWARE');
        $lisw_id_software->addFilter('Int')
                      ->removeDecorator('Label')
                      ->removeDecorator('HtmlTag');

        $licenca = new Zend_Form_Element_Text('LISW_QT_LICENCA');
        $licenca->setLabel('Licenças')
        			  ->setDescription('Digite a nova quantidade de licenças')
        			  ->setAttrib('size', 4);
                      
                      
                      

        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array( $licenca, $lisw_id_software, $submit));
    }

}