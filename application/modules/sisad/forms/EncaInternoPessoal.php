<?php
class Sisad_Form_EncaInternoPessoal extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('EncaInternoPessoal');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('EncaminhamentoInternoPessoal');
        
        $mode_sg_secao_unid_destino = new Zend_Form_Element_Hidden('MODE_SG_SECAO_UNID_DESTINO');
        $mode_sg_secao_unid_destino->addFilter('alnum')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim');

        $mode_cd_secao_unid_destino = new Zend_Form_Element_Hidden('MODE_CD_SECAO_UNID_DESTINO');
        $mode_cd_secao_unid_destino->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim');       
        
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Descrição do Encaminhamento:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 500))
                ->setAttrib('style', 'width: 628px;');
        
        $mode_cd_matr_recebedor = new Zend_Form_Element_Select('MODE_CD_MATR_RECEBEDOR');
        $mode_cd_matr_recebedor->setRequired(true)
                       ->setLabel('Pessoa:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($acao,$mode_sg_secao_unid_destino,$mode_cd_secao_unid_destino,$mode_cd_matr_recebedor,$mofa_ds_complemento,$submit));
    }
}