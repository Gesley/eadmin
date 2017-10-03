<?php
class Guardiao_Form_Ajuda extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $acao_id_acao_sistema = new Zend_Form_Element_Hidden('ACAO_ID_ACAO_SISTEMA');
        
        
        $acao_ds_ajuda = new Zend_Form_Element_Textarea('ACAO_DS_AJUDA');
        $acao_ds_ajuda->setLabel('Ajuda:')
                      ->setAttrib('style','width: 800px;');
        
        $acao_ds_informacao = new Zend_Form_Element_Textarea('ACAO_DS_INFORMACAO');
        $acao_ds_informacao->setLabel('Informação:')
                           ->setAttrib('style','width: 800px;');
        
        $Alterar = new Zend_Form_Element_Submit('Alterar');
        $Alterar->setOptions(array('class' => 'novo'));
        
        $this->addElements(array($acao_id_acao_sistema,
                                 $acao_ds_ajuda,
                                 $acao_ds_informacao,
                                 $Alterar));
    }
}