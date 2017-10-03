<?php
class Sosti_Form_AtendimentoClienteDevolver extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $userNs = new Zend_Session_Namespace('userNs'); 

        $docm_id_documento = new Zend_Form_Element_Hidden('DOCM_ID_DOCUMENTO');
        $docm_id_documento->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $docm_nr_documento = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $movi_sg_secao_unid_origem= new Zend_Form_Element_Hidden('MOVI_SG_SECAO_UNID_ORIGEM');
        $movi_sg_secao_unid_origem->setRequired(false)
                                  ->removeDecorator('Label')
                                  ->removeDecorator('HtmlTag');

        $movi_cd_secao_unid_origem = new Zend_Form_Element_Hidden('MOVI_CD_SECAO_UNID_ORIGEM');
        $movi_cd_secao_unid_origem->setRequired(false)
                                  ->removeDecorator('Label')
                                  ->removeDecorator('HtmlTag');

        $mode_sg_secao_unid_destino = new Zend_Form_Element_Hidden('MODE_SG_SECAO_UNID_DESTINO');
        $mode_sg_secao_unid_destino->setRequired(false)
                                   ->removeDecorator('Label')
                                   ->removeDecorator('HtmlTag');

        $movi_id_caixa_entrada = new Zend_Form_Element_Hidden('MOVI_ID_CAIXA_ENTRADA');
        $movi_id_caixa_entrada->setRequired(false)
                              ->removeDecorator('Label')
                              ->removeDecorator('HtmlTag');

        $nivel_origem = new Zend_Form_Element_Hidden('NIVEL_ORIGEM');
        $nivel_origem->setRequired(false)
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');
        
        $servico_origem = new Zend_Form_Element_Hidden('SERVICO_ORIGEM');
        $servico_origem->setRequired(false)
                       ->removeDecorator('Label')
                       ->removeDecorator('HtmlTag');
        
        $mofa_id_movimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $mofa_id_movimentacao->setRequired(false)
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');
        
        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('Seção Judiciária:')
                      ->setRequired(false)
                      ->setAttribs(array('style' => 'width: 500px;', 'class' => 'trf1_secao'))
                      ->addMultiOptions(array(''=>''));
                
        
//        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
//        $sser_id_servico->setRequired(true)
//                        ->setLabel('Serviço:')
//                        ->setAttrib('style', 'width: 650px;');
//        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
//        $SosTbSserServico_array = $SosTbSserServico->getServicoPorGrupo(1, 'SSER_DS_SERVICO ASC');
//        $sser_id_servico->addMultiOptions(array('' => ''));
//        foreach ($SosTbSserServico_array as $SserServico_aux):
//            $sser_id_servico->addMultiOptions(array($SserServico_aux['SSER_ID_SERVICO'].'|'.$SserServico_aux['SSER_IC_TOMBO'].'|'.$SserServico_aux['SSER_IC_VIDEOCONFERENCIA'] => $SserServico_aux["SSER_DS_SERVICO"]));
//        endforeach;

        $de_mat = new Zend_Form_Element_Textarea('DE_MAT');
        $de_mat->setLabel('*Descrição do Tombo')
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setValue('Primeiro informe Nº do Tombo')
               ->setAttrib('disabled', 'disabled')
               ->setAttrib('style', 'width: 800px; height: 30px;')
               ->setAttrib('class', 'erroInputSelect');
        

        $snas_id_nivel = new Zend_Form_Element_Select('SNAS_ID_NIVEL');
        $snas_id_nivel->setRequired(false);
        $snas_id_nivel->setLabel('Nivel de Atendimento:');
                

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                            ->setLabel('Descrição da devolução:')
                            ->setOptions(array('style' => 'width:500px'))
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter($Zend_Filter_HtmlEntities);
        
         $acompanhar = new Zend_Form_Element_Checkbox('ACOMPANHAR');
         $acompanhar->setLabel('Quero Acompanhar a baixa desta solicitação:')
                       ->setDecorators(array( 'ViewHelper', 'Errors', 'Label'))
                       ->removeDecorator('HtmlTag', array('tag'=>'dt'))
                       ->addDecorator('HtmlTag', array('tag'=>'div','style'=> 'clear:both'))	
                       ->setAttribs(array('style' => 'float: left;'))
                       ->setRequired(false)
                       ->setCheckedValue('S')
                       ->setUncheckedValue('N');
         
         $submit = new Zend_Form_Element_Submit('devolucao');
         $submit->setLabel('Devolver');
         $this->addElements(array(
                                     $docm_id_documento,
                                     $docm_nr_documento,
                                     $movi_sg_secao_unid_origem,
                                     $movi_cd_secao_unid_origem,
                                     $movi_id_caixa_entrada,
                                     $nivel_origem,
                                     $servico_origem,
                                     $mofa_id_movimentacao,
                                     $snas_id_nivel,
                                     $trf1_secao,
                                     //$sser_id_servico,
                                     $de_mat,
                                     $mofa_ds_complemento,
                                     $acompanhar,
                                     $submit));
    }

}