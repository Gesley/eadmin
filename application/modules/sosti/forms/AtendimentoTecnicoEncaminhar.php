<?php
class Sosti_Form_AtendimentoTecnicoEncaminhar extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs'); 
        
        $encaminhamento = new Zend_Form_Element_Radio('ENCAMINHAMENTO');
        $encaminhamento->setLabel('Encaminhar para:')
                       ->setRequired(true)
                       ->setMultiOptions(array('nivel'   => 'Outro nível de atendimento', 
                                               'grupo'   => 'Grupo de atendimento', 
                                              // 'unidade' => 'Unidade responsável',
                                               'pessoal' => 'Caixa pessoal'));

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

//        $mode_cd_secao_unid_destino = new Zend_Form_Element_Select('MODE_CD_SECAO_UNID_DESTINO');
//        $mode_cd_secao_unid_destino->setRequired(false)
//                                   ->setLabel('Unidade Responsável:');

        $mode_id_caixa_entrada = new Zend_Form_Element_Select('MODE_ID_CAIXA_ENTRADA');
        $mode_id_caixa_entrada->setRequired(false)
                              ->setLabel('Grupo de Atendimento')
                              ->addMultiOptions(array(':: Selecione ::'));
        
        
        
        $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico(); 
        //$SgrsGrupoServico = $SosTbSgrsGrupoServico->fetchAll('SGRS_ID_GRUPO = 1')->toArray();
        $SgrsGrupoServico = $SosTbSgrsGrupoServico->getGrupoServicoBySecsubsec($userNamespace->codsecsubseclotacao, true);
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(false)
                      ->setLabel('*Grupo de Serviço:');
        
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;
        
        
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->fetchAll()->toArray();
        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(false)
                          ->setLabel('*Serviço:')
                         ->setAttrib('disabled', 'disabled');
        $sser_id_servico->addMultiOptions(array('' => 'Primeiro Escolha Grupo de Serviço'));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;

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
//        foreach ($pessoas as $pessoas_p):
//            $apsp_id_pessoa->addMultiOptions(array($pessoas_p["PMAT_ID_PESSOA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
//        endforeach;
         
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
                                     $lota_cod_lotacao,
                                     $apsp_id_pessoa,
                                     //$mode_cd_secao_unid_destino,
                                     $mode_id_caixa_entrada,
//                                     $sgrs_id_grupo,
//                                     $sser_id_servico,
                                     $mofa_ds_complemento,
                                     $submit, 
                                     $proximo));
    }

}