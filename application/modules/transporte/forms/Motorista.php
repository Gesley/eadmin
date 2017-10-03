<?php
class Transporte_Form_Motorista extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $moto_id_motorista = new Zend_Form_Element_Hidden('MOTO_ID_MOTORISTA');
        $moto_id_motorista->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $moto_cd_matr_motorista = new Zend_Form_Element_Text('MOTO_CD_MATR_MOTORISTA');
        $moto_cd_matr_motorista//->setRequired(true)
                     ->setLabel('Matricula:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $moto_sg_secao = new Zend_Form_Element_Text('MOTO_SG_SECAO');
        $moto_sg_secao//->setRequired(true)
                     ->setLabel('Seção:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $moto_cd_lotacao = new Zend_Form_Element_Text('MOTO_CD_LOTACAO');
        $moto_cd_lotacao//->setRequired(true)
                     ->setLabel('Lotação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $moto_ds_observacao = new Zend_Form_Element_Text('MOTO_DS_OBSERVACAO');
        $moto_ds_observacao//->setRequired(true)
                     ->setLabel('Observação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));

        $moto_ic_gabinete = new Zend_Form_Element_Checkbox('MOTO_IC_GABINETE');
        $moto_ic_gabinete//->setRequired(true)
                     ->setLabel('Disp. Gabinete:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;
        

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($moto_id_motorista, $moto_cd_matr_motorista, $moto_sg_secao, $moto_cd_lotacao, $moto_ds_observacao, $moto_ic_gabinete, $submit));

        //$this->setElementDecorators(array('Label','ViewHelper', 'Errors')); # sempre no final do form
    }
}