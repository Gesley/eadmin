<?php
class Sisad_Form_DadosJulgamento extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $hdpa_id_distribuicao = new Zend_Form_Element_Hidden('HDPA_ID_DISTRIBUICAO');
        $hdpa_id_distribuicao->removeDecorator('Label');
        $hdpa_dt_julgamento = new Zend_Form_Element_Text('HDPA_DT_JULGAMENTO');
        $hdpa_dt_julgamento->setRequired(true)
                ->setLabel('*Data Julgamento:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setOptions(array('style' => 'width: 80px'))
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyy')));
        
        $hdpa_ds_resumo_decisao = new Zend_Form_Element_Textarea('HDPA_DS_RESUMO_DECISAO');
        $hdpa_ds_resumo_decisao->setRequired(true)
                 ->setLabel('*Resumo da Decisão:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setAttrib('maxlength',4000)
                 ->setOptions(array('style' => 'width: 700px; height: 20px'));
        
        $hdpa_dt_public_julgamento_dj = new Zend_Form_Element_Text('HDPA_DT_PUBLIC_JULGAMENTO_DJ');
        $hdpa_dt_public_julgamento_dj->setRequired(true)
                 ->setLabel('*Data da Publicação no Diário da Justiça:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('style' => 'width: 80px'))
                 ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyy')));
        
        $hdpa_dt_public_julgamento_bs = new Zend_Form_Element_Text('HDPA_DT_PUBLIC_JULGAMENTO_BS');
        $hdpa_dt_public_julgamento_bs->setLabel('Data da Publicação no Boletim de Serviço:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('style' => 'width: 80px'))
                 ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyy')));
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($hdpa_id_distribuicao,
                                $hdpa_dt_julgamento,
                                $hdpa_ds_resumo_decisao,
                                $hdpa_dt_public_julgamento_dj,
                                $hdpa_dt_public_julgamento_bs,
                                $submit));
    }
}