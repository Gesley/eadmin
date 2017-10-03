<?php
class Sosti_Form_SolicitacaoExtenderPrazo extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $sopr_id_documento = new Zend_Form_Element_Hidden('SSPA_ID_DOCUMENTO');
        $sopr_id_documento->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $sopr_dh_solicitacao_prazo = new Zend_Form_Element_Hidden('SSPA_DH_FASE');
        $sopr_dh_solicitacao_prazo->setRequired(false)
                                  ->removeDecorator('Label')
                                  ->removeDecorator('HtmlTag');
               
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $sopr_ds_descricao_prazo = new Zend_Form_Element_Textarea('SOPR_DS_DESCRICAO_PRAZO');
        $sopr_ds_descricao_prazo->setRequired(true)
                                ->setLabel('Descrição da Extensão do Prazo:')
                                ->setOptions(array('style' => 'width:500px'))
                                ->addValidator('StringLength', false, array(10, 4000))
                                ->addValidator('NotEmpty')
                                ->addFilter('StripTags')
                                ->addFilter('StringTrim')
                                ->addFilter($Zend_Filter_HtmlEntities);
        
        $sspa_ic_confirmacao = new Zend_Form_Element_Radio('SSPA_IC_CONFIRMACAO');
        $sspa_ic_confirmacao->setLabel('Confirma a Extensão de Prazo?')
                            ->addMultiOption('S', 'Sim')
                            ->addMultiOption('N', 'Não')
                            ->addValidator('NotEmpty')
                            ->setRequired(true)
                            ->setSeparator('<br />');
        
        $sopr_pz_solicitado = new Zend_Form_Element_Text('SSPA_DT_PRAZO');
        $sopr_pz_solicitado->setLabel('Solicitar Prazo:')
                           ->addValidator('NotEmpty')
                           ->setRequired(true)
                           ->addValidator( new Zend_Validate_Date(array('format'=>'dd/MM/yyyyHH:mm')))
                           ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $this->addElements(array($sopr_id_documento, $sopr_dh_solicitacao_prazo, $sopr_cd_matricula_solicitante, $sspa_ic_confirmacao,
                                 $sopr_pz_solicitado, $data_final, $sopr_ds_descricao_prazo, $submit, $redir_action, $redir_controller));
    }

}