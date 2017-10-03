<?php
class Sosti_Form_ServicoCaixa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $mofa_id_movimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $mofa_id_movimentacao->addFilter('Int')
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');
        
        $docm_id_documento = new Zend_Form_Element_Hidden('DOCM_ID_DOCUMENTO');
        $docm_id_documento->setRequired(false)
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        
        $docm_nr_documento = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setRequired(false)
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        
        
        $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico(); 
        $SgrsGrupoServico = $SosTbSgrsGrupoServico->fetchAll()->toArray();
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(true)
                      ->setLabel('Grupo de Serviço:');
        
        $sgrs_id_grupo->addMultiOptions(array('' => ''));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array($SgrsGrupoServico_p["SGRS_ID_GRUPO"] => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;
        
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->fetchAll()->toArray();
        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(true)
                          ->setLabel('Serviço:')
                         ->setAttrib('disabled', 'disabled');
        $sser_id_servico->addMultiOptions(array('' => 'Primeiro Escolha Grupo de Serviço'));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;
               
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                            ->setLabel('Descrição da mudança de Serviço:')
                            ->addValidator('NotEmpty')
                            ->setAttrib('style', 'width: 800px; height: 80px;')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addValidator('StringLength', false, array(5, 4000));

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($mofa_id_movimentacao, $docm_id_documento, $docm_nr_documento,$sgrs_id_grupo,$sser_id_servico, $mofa_ds_complemento, $submit));
    }

}