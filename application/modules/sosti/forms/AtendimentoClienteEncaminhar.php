<?php
class Sosti_Form_AtendimentoClienteEncaminhar extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $userNs = new Zend_Session_Namespace('userNs'); 
        
        $encaminhamento = new Zend_Form_Element_Radio('ENCAMINHAMENTO');
        $encaminhamento->setLabel('Encaminhar para:')
                       ->setRequired(true)
                       ->setMultiOptions(array('nivel'   => 'Outro nível de atendimento', 
                                               'pessoal' => 'Caixa pessoal',
                                               'trf'   => 'Outro Grupo de Atendimento', 
                                               'secoes' => 'Seções',
                                                ));

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

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        $getLotacao = $rh_central->getLotacao();
        
        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('Seção Judiciária:')
                      ->setRequired(false)
                      ->setAttribs(array('style' => 'width: 500px;', 'class' => 'trf1_secao'))
                      ->addMultiOptions(array(''=>''));
                      foreach($secao as $v){
                            $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"].'|'.$v["LOTA_COD_LOTACAO"].'|'.$v["LOTA_TIPO_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
                      }
       $trf1_secao->removeMultiOption("TR|2|9");
                
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        /**
         * TR 2 CÓDIGO DO TRIBUNAL PARA TRAZER OS GRUPOS DE SERVIÇO DO TRF1
         */
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao( 'TR' , 2 );
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(false)
                      ->setLabel('*Grupo de Serviço:')
                      ->setAttrib('style', 'width: 450px;');;
        $sgrs_id_grupo->addMultiOptions(array('' => ''));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;
        
        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(false)
                        ->setLabel('*Serviço:')
                        ->setAttrib('style', 'width: 650px;');
        
        
        $ssol_nr_tombo = new Zend_Form_Element_Text('SSOL_NR_TOMBO');
        $ssol_nr_tombo->setLabel('Nº do Tombo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

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
                
        $lota_cod_lotacao= new Zend_Form_Element_Text('LOTA_COD_LOTACAO');
        $lota_cod_lotacao->setLabel('Unidade:')
                         ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                         ->addValidator('NotEmpty');
        //$pessoa = new Application_Model_DbTable_OcsTbPmatMatricula();
        $apsp_id_pessoa = new Zend_Form_Element_Select('APSP_ID_PESSOA');
        $apsp_id_pessoa->setLabel('Pessoa:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty')
                       ->setAttrib('disabled', 'disabled')
                    //   ->addMultiOptions(array(''=>'Informe primeiro a Unidade Administrativa'))
                ;
         
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                            ->setLabel('Descrição do Encaminhamento:')
                            ->setOptions(array('style' => 'width:500px'))
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        
         $acompanhar = new Zend_Form_Element_Checkbox('ACOMPANHAR');
         $acompanhar->setLabel('Quero Acompanhar a baixa desta solicitação:')
                       ->setDecorators(array( 'ViewHelper', 'Errors', 'Label'))
                       ->removeDecorator('HtmlTag', array('tag'=>'dt'))
                       ->addDecorator('HtmlTag', array('tag'=>'div','style'=> 'clear:both'))	
                       ->setAttribs(array('style' => 'float: left;'))
                       ->setRequired(false)
                       ->setCheckedValue('S')
                       ->setUncheckedValue('N');
         
         $this->addElements(array($docm_id_documento,
                                    $docm_nr_documento,
                                    $movi_sg_secao_unid_origem,
                                    $movi_cd_secao_unid_origem,
                                    $movi_id_caixa_entrada,
                                    $nivel_origem,
                                    $servico_origem,
                                    $mofa_id_movimentacao,
                                    $encaminhamento,
                                    $snas_id_nivel,
                                    $lota_cod_lotacao,
                                    $apsp_id_pessoa,
                                    $trf1_secao,
                                    $sgrs_id_grupo,
                                    $sser_id_servico,
                                    $ssol_nr_tombo,
                                    $de_mat,
                                    $mofa_ds_complemento,
                                    $acompanhar));
    }

}