<?php
class Soseg_Form_EncaminhaSolicitacaoServico extends Zend_Form
{
    public function init()
    {
    $this->setAction('saveencaminhamento')
         ->setName('encaminhar')
         ->setMethod('post');
      
    $encaminhamento = new Zend_Form_Element_Radio('ENCAMINHAMENTO');
    $encaminhamento->setLabel('Encaminhar para:')
                ->setRequired(true)
                ->setMultiOptions(array('pessoa'=>'Atendente', 'grupo'=>'Outro Grupo de Atendimento'));
    
    $apsp_id_pessoa = new Zend_Form_Element_Select('APSP_ID_PESSOA');
    $apsp_id_pessoa->setLabel('Pessoa:')
                   ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty');
            
    $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
    $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicosGraficos();
        
    $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
    $sgrs_id_grupo->setRequired(false)
                      ->setLabel('Grupo de Serviço:');
        
    $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
    foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
    endforeach;   
    
    $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
    $sser_id_servico->setRequired(false)
                    ->setLabel('Serviço:')
                    ->setAttrib('style', 'width: 650PX;')
                    ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Primeiro Escolha um Grupo de Serviço.');


    $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
    $mofa_ds_complemento->setRequired(true)
                        ->setLabel('Descrição do Encaminhamento:')
                        ->addValidator('NotEmpty')
                        ->setAttrib('style', 'width: 800px; height: 80px;')
                        ->addValidator('StringLength', false, array(5, 4000));
    
    /*$docm_ds_hash_red = new Zend_Form_Element_File('DOCM_DS_HASH_RED');
    $docm_ds_hash_red->setLabel('Inserir Anexos:')
                                ->setRequired(false)
                                ->addValidator(new Zend_Validate_File_Extension(array('pdf','doc','docx')))
                                ->addValidator('Size', false, 52428800) // limit to 50m
                                ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                                ->setDestination(APPLICATION_PATH . '/../temp')
                                ->setDescription('Somente serão aceitos arquivos com os seguintes formatos: pdf|docx|doc .Tamanho máximo de 50 Megas.');
            
                                */
     $submit = new Zend_Form_Element_Submit('Encaminhar');
     
     $this->addElements(array(   $encaminhamento,
                                 $apsp_id_pessoa,
                                 $sgrs_id_grupo, 
                                 $sser_id_servico,
                                 $mofa_ds_complemento, 
                                 $submit));
     
    }

}