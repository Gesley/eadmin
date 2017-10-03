<?php
class Sosti_Form_RetirarSla extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $dsin_id_movimentacao = new Zend_Form_Element_Hidden('DSIN_ID_MOVIMENTACAO');
        $dsin_id_movimentacao->addFilter('Int')
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');
        
        $dsin_id_indicador = new Zend_Form_Element_Hidden('DSIN_ID_INDICADOR');
        $dsin_id_indicador->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $dsin_cd_matricula_operacao = new Zend_Form_Element_Hidden('DSIN_CD_MATRICULA_OPERACAO');
        $dsin_cd_matricula_operacao->setRequired(false)
                                   ->removeDecorator('Label')
                                   ->removeDecorator('HtmlTag');
               
        $dsin_id_doc_justificativa = new Zend_Form_Element_Text('DSIN_ID_DOC_JUSTIFICATIVA');
        $dsin_id_doc_justificativa->setLabel('Número do Documento da Justificativa:')
                                  ->setOptions(array('style' => 'width:500px'))
                                  ->addFilter('StripTags')
                                  ->addFilter('StringTrim')
                                  ->addValidator('Float')
                                  ->addValidator('Alnum')
                                  ->addValidator('Between',false, array(0,9999999999999999999999999999))
                                  ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
       
        $dsin_ds_justificativa = new Zend_Form_Element_Textarea('DSIN_DS_JUSTIFICATIVA');
        $dsin_ds_justificativa->setRequired(true)
                              ->setLabel('Descrição para Desconsiderar do SLA:')
                              ->setOptions(array('style' => 'width:500px'))
                              ->addValidator('StringLength', false, array(5, 4000))
                              ->addValidator('NotEmpty')
                              ->addFilter('StripTags')
                              ->addFilter('StringTrim')
                              ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');

        $this->addElements(array($dsin_id_movimentacao, $dsin_id_indicador, $dsin_cd_matricula_operacao, $dsin_id_doc_justificativa,
                                 $dsin_ds_justificativa, $submit));
    }

}