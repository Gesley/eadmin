<?php
class Sosti_Form_EncaminhaSolicitacao extends Zend_Form
{
    public function init()
    {
    $this->setAction('')
         ->setMethod('post');
        
    $encaminhamento = new Zend_Form_Element_Radio('ENCAMINHAMENTO');
    $encaminhamento->setLabel('Encaminhar para:')
                ->setRequired(true)
                ->setMultiOptions(array('nivel'=>'Outro nível de atendimento', 'grupo'=>'Grupo de Atendimento', /*'unidade'=>'Unidade Responsável'*/));
    
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
    
    $mode_cd_secao_unid_destino = new Zend_Form_Element_Select('MODE_CD_SECAO_UNID_DESTINO');
    $mode_cd_secao_unid_destino->setRequired(false)
        ->setLabel('Unidade Responsável:');
    
    $mode_id_caixa_entrada = new Zend_Form_Element_Select('MODE_ID_CAIXA_ENTRADA');
    $mode_id_caixa_entrada->setRequired(false)
            ->setLabel('Grupo de Atendimentosdfg');
    
    $snas_id_nivel = new Zend_Form_Element_Select('SNAS_ID_NIVEL');
    $snas_id_nivel->setRequired(false);
            $snas_id_nivel->setLabel('Nivel de Atendimento:');



    $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
    $mofa_ds_complemento->setRequired(true)
                        ->setLabel('Descrição do Encaminhamento:')
                        ->addValidator('NotEmpty')
                        ->setAttrib('style', 'width: 800px; height: 80px;')
                        ->addValidator('StringLength', false, array(5, 4000));
            
  
     $submit = new Zend_Form_Element_Submit('Encaminhar');
     $this->addElements(array($docm_cd_matricula_cadastro,
                                 $docm_cd_lotacao_geradora,
                                 $docm_id_documento,
                                 $docm_nr_documento,
                                 $movi_sg_secao_unid_origem,
                                 $movi_cd_secao_unid_origem,
                                 $movi_id_caixa_entrada,
                                 $nivel_origem,
                                 $servico_origem,
                                 $mofa_id_movimentacao,
                                 $encaminhamento,
                                 $snas_id_nivel,
                                 $mode_cd_secao_unid_destino,
                                 $mode_id_caixa_entrada, 
                                 $mofa_ds_complemento, 
                                 $submit, 
                                 $proximo));
     
    }

}