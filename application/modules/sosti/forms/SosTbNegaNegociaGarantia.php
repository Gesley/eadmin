<?php
class Sosti_Form_SosTbNegaNegociaGarantia extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
             ->setName('NegociaGarantia');
         
        /**
         * Configuração do Filter HtmlEntities
         */
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);

        
        $nega_id_movimentacao = new Zend_Form_Element_Hidden('NEGA_ID_MOVIMENTACAO');
        $nega_id_movimentacao->removeDecorator('Label')->removeDecorator('HtmlTag');
        $nega_dh_solic_garantia = new Zend_Form_Element_Hidden('NEGA_DH_SOLIC_GARANTIA');
        $nega_dh_solic_garantia->removeDecorator('Label')->removeDecorator('HtmlTag');
        $nega_cd_matr_solic = new Zend_Form_Element_Hidden('NEGA_CD_MATR_SOLIC');
        $nega_cd_matr_solic->removeDecorator('Label')->removeDecorator('HtmlTag');
        
        $nega_ic_solicita = new Zend_Form_Element_Checkbox('NEGA_IC_SOLICITA');
        $nega_ic_solicita->setLabel('Solicitar Garantia:')
                        ->setCheckedValue('S')
                        ->setUncheckedValue('N')
                        ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                        ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                        ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both', 'id' => 'GARANTIA_CHECKBOX'))
                        ->setAttribs(array('style' => 'float: left;'));
        
        
        $nega_ds_justificativa_pedido = new Zend_Form_Element_Textarea('NEGA_DS_JUSTIFICATIVA_PEDIDO');
        $nega_ds_justificativa_pedido->setRequired(false)
                                    ->setLabel('Justificativa da Solicitação de Garantia')
                                    ->setOptions(array('style' => 'width:500px'))
                                    ->addValidator('StringLength', false, array(5, 4000))
                                    ->addValidator('NotEmpty')
                                    ->addFilter('StripTags')
                                    ->addFilter('StringTrim')
                                    ->addFilter($Zend_Filter_HtmlEntities);
        
        $nega_ic_aceite = new Zend_Form_Element_Radio('NEGA_IC_ACEITE');
        $nega_ic_aceite->setLabel('Confirma Garantia:')
                       ->setMultiOptions(array('A'   => 'Aceita', 
                                               'R' => 'Recusa'
                                                ));
        
        $nega_dh_aceite_recusa = new Zend_Form_Element_Hidden('NEGA_DH_ACEITE_RECUSA');
        $nega_dh_aceite_recusa->removeDecorator('Label')->removeDecorator('HtmlTag');
        $nega_cd_matr_aceite_recusa = new Zend_Form_Element_Hidden('NEGA_CD_MATR_ACEITE_RECUSA');
        $nega_cd_matr_aceite_recusa->removeDecorator('Label')->removeDecorator('HtmlTag');
        
        $nega_ds_just_aceite_recusa = new Zend_Form_Element_Textarea('NEGA_DS_JUST_ACEITE_RECUSA');
        $nega_ds_just_aceite_recusa->setRequired(false)
                                    ->setLabel('Justificativa Recusa Garantia')
                                    ->setOptions(array('style' => 'width:500px'))
                                    ->addValidator('StringLength', false, array(5, 4000))
                                    ->addValidator('NotEmpty')
                                    ->addFilter('StripTags')
                                    ->addFilter('StringTrim')
                                    ->addFilter($Zend_Filter_HtmlEntities);
        
        
        $nega_ic_concordancia = new Zend_Form_Element_Radio('NEGA_IC_CONCORDANCIA');
        $nega_ic_concordancia->setLabel('Confirma Recusa Garantia:')
                        ->setMultiOptions(array('C'   => 'Concorda', 
                                                'D' => 'Discorda'));
        
        $nega_dh_concordancia = new Zend_Form_Element_Hidden('NEGA_DH_CONCORDANCIA');
        $nega_dh_concordancia->removeDecorator('Label')->removeDecorator('HtmlTag');
        $nega_cd_matr_concordancia = new Zend_Form_Element_Hidden('NEGA_CD_MATR_CONCORDANCIA');
        $nega_cd_matr_concordancia->removeDecorator('Label')->removeDecorator('HtmlTag');
        
        
        $nega_ds_justificativa_concor = new Zend_Form_Element_Textarea('NEGA_DS_JUSTIFICATIVA_CONCOR');
        $nega_ds_justificativa_concor->setRequired(false)
                            ->setLabel('Justificativa Solicitação Garantia')
                            ->setOptions(array('style' => 'width:500px'))
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter($Zend_Filter_HtmlEntities);
        
        
        $this->addElements(array(
            $nega_id_movimentacao,
            $nega_dh_solic_garantia,
            $nega_cd_matr_solic,
            $nega_ic_solicita,
            $nega_ds_justificativa_pedido,
            $nega_ic_aceite,
            $nega_dh_aceite_recusa,
            $nega_cd_matr_aceite_recusa,
            $nega_ds_just_aceite_recusa,
            $nega_ic_concordancia,
            $nega_dh_concordancia,
            $nega_cd_matr_concordancia,
            $nega_ds_justificativa_concor
        ));
    }
    
    public function addSubmitInput()
    {
        $submit = new Zend_Form_Element_Submit('Salvar');
        $this->addElement($submit);
        return $this;
    }
    
    
    public function confFormFiltro()
    {
       $nega_ic_concordancia = $this->getElement('NEGA_IC_CONCORDANCIA'); 
       $this->removeElement('NEGA_IC_CONCORDANCIA');
       $submit2 = new Zend_Form_Element_Submit('Filtrar2');
       $submit2 ->setLabel('Filtrar')
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');
       $this->addElement($submit2);
       $this->addElement($nega_ic_concordancia);
        

        $CaixaSolicitacoes = new Sosti_Form_CaixaSolicitacao();
        $addElemetosFiltroCaixa = array(
            "DOCM_NR_DOCUMENTO" =>"",
            "SERVICO" =>"",
            "SSER_ID_SERVICO" =>"",
            "SSER_DS_SERVICO" =>"",
            "DATA_INICIAL" =>"",
            "DATA_FINAL" =>"",
            "SOMENTE_PRINCIPAL" =>""
        );
        foreach ($addElemetosFiltroCaixa as $key => $value) {
            $this->addElement($CaixaSolicitacoes->getElement($key));
        }
        
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo(2, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $this->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;
        
        $somente_principal = $this->getElement('SOMENTE_PRINCIPAL');
        $somente_principal
                        ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                        ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                        ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both; margin-top:6px;'))
                        ->setAttribs(array('style' => 'float: left;'));
        
        $removeElementsGarantia = array(
            "NEGA_ID_MOVIMENTACAO" =>"",
            "NEGA_DH_SOLIC_GARANTIA" =>"",
            "NEGA_CD_MATR_SOLIC" =>"",
            "NEGA_IC_SOLICITA" =>"",
            "NEGA_DS_JUSTIFICATIVA_PEDIDO" =>"",
            "NEGA_IC_ACEITE" =>"",
            "NEGA_DH_ACEITE_RECUSA" =>"",
            "NEGA_CD_MATR_ACEITE_RECUSA" =>"",
            "NEGA_DS_JUST_ACEITE_RECUSA" =>"",
            "NEGA_DH_CONCORDANCIA" =>"",
            "NEGA_CD_MATR_CONCORDANCIA" =>"",
            "NEGA_DS_JUSTIFICATIVA_CONCOR" =>""  
        );
        foreach ($removeElementsGarantia as $key => $value) {
            $this->removeElement($key);
        }
        
        $nega_ic_concordancia = $this->getElement('NEGA_IC_CONCORDANCIA');
        $nega_ic_concordancia->setLabel("Divergencia:");
        $nega_ic_concordancia->addMultiOptions(array('AV'   => 'Verificados', 
                                                    'NAV' => 'Não Verificados'
                                               ));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;
        
        $nega_ic_concordancia->setValue("NAV");
        
        $data_inicial = $this->getElement('DATA_INICIAL');
        $data_inicial->setLabel("Data inicial - Aceite/Recusa");
        $data_final = $this->getElement('DATA_FINAL');
        $data_final->setLabel("Data final - Aceite/Recusa");
        
        $submit = new Zend_Form_Element_Submit('Filtrar');
        $this->addElement($submit);
    }
}