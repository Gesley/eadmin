<?php
class Sisad_Form_Tipoprocesso extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $tppr_id_tipo_processo = new Zend_Form_Element_Hidden('TPPR_ID_TIPO_PROCESSO');
        $tppr_id_tipo_processo->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');

        $tppr_ds_descricao_processo = new Zend_Form_Element_Text('TPPR_DS_DESCRICAO_PROCESSO');
        $tppr_ds_descricao_processo->setRequired(true)
                        ->setLabel('Descrição do Tipo:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 500px; ')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->addValidator('StringLength', false, array(3, 200));
        
        $tprr_ic_ativo = new Zend_Form_Element_Select('TPRR_IC_ATIVO');
        $tprr_ic_ativo->setRequired(true)
                          ->setLabel('Ativo:')
                          ->addValidator('NotEmpty')
                          ->setRequired(true)
                          ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não')
                          );


        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tppr_id_tipo_processo, 
                                $tppr_ds_descricao_processo, 
                                $tprr_ic_ativo,
                                $submit));
    }
}