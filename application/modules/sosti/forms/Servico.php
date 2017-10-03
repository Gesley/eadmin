<?php
class Sosti_Form_Servico extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $sser_id_servico = new Zend_Form_Element_Hidden('SSER_ID_SERVICO');
        $sser_id_servico->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');

        $sser_ds_servico = new Zend_Form_Element_Textarea('SSER_DS_SERVICO');
        $sser_ds_servico->setLabel('Descrição do Serviço:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 735px; height: 45px;')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty');
               
        $sgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
        $grupo = $sgrsGrupoServico->getGrupoServico("SGRS_DS_GRUPO DESC");
       
        $sser_id_grupo = new Zend_Form_Element_Select('SSER_ID_GRUPO');
        $sser_id_grupo->setLabel('Grupo de Serviço:')
                    ->setAttrib('style', 'width: 800px;')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
        $sser_id_grupo->addMultiOptions(array('' => ''));
        foreach ($grupo as $grupo_p) {
            $sser_id_grupo->addMultiOptions(array($grupo_p["SGRS_ID_GRUPO"] => $grupo_p["SGRS_DS_GRUPO"].' - '. $grupo_p["LOTACAO"]));
        }
     
        $sser_ic_ativo = new Zend_Form_Element_Checkbox('SSER_IC_ATIVO');
        $sser_ic_ativo->setLabel('Flag Ativo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setCheckedValue('S')
                      ->setUncheckedValue('N');

        $sser_ic_visivel = new Zend_Form_Element_Checkbox('SSER_IC_VISIVEL');
        $sser_ic_visivel->setLabel('Flag Visível:')
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->setCheckedValue('S')
                        ->setUncheckedValue('N');

        $sser_ic_tombo = new Zend_Form_Element_Checkbox('SSER_IC_TOMBO');
        $sser_ic_tombo->setLabel('Flag Tombo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setCheckedValue('S')
                      ->setUncheckedValue('N');

        $sser_ic_anexo = new Zend_Form_Element_Checkbox('SSER_IC_ANEXO');
        $sser_ic_anexo->setLabel('Flag Anexo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setCheckedValue('S')
                      ->setUncheckedValue('N');
        
        $sser_ic_videoconferencia = new Zend_Form_Element_Checkbox('SSER_IC_VIDEOCONFERENCIA');
        $sser_ic_videoconferencia->setLabel('Flag videoconferência:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setCheckedValue('S')
                      ->setUncheckedValue('N')
                      ->setDescription('Atenção: Somente marque essa opção se o serviço for de REALIZAÇÃO de videoconferência. Essa opção obrigará a informação de data da videoconferência durante o cadastro da solicitação desse serviço.');
        
        $replicar_trf = new Zend_Form_Element_Checkbox('REPLICAR_TRF');
        $replicar_trf->setLabel('Replicar para o TRF:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setCheckedValue('S')
                      ->setUncheckedValue('N');

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($sser_id_servico, $sser_id_grupo, 
                                 $sser_ds_servico, $sser_ic_ativo, $sser_ic_visivel, 
                                 $sser_ic_tombo, $sser_ic_anexo,$sser_ic_videoconferencia, $replicar_trf, $submit));
    }

}