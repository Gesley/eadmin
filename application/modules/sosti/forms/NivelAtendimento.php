<?php
class Sosti_Form_NivelAtendimento extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $snat_id_nivel = new Zend_Form_Element_Hidden('SNAT_ID_NIVEL');
        $snat_id_nivel->removeDecorator('Label')
                      ->removeDecorator('HtmlTag');
             
        $sgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
        $grupo = $sgrsGrupoServico->getGrupoServico();

        $snat_id_grupo = new Zend_Form_Element_Select('SNAT_ID_GRUPO');
        $snat_id_grupo->setLabel('Grupo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
        foreach ($grupo as $grupo_p) {
            $snat_id_grupo->addMultiOptions(array($grupo_p["SGRS_ID_GRUPO"] => $grupo_p["SGRS_DS_GRUPO"].' - '. $grupo_p["LOTACAO"]));
        }
       
        $snat_cd_nivel = new Zend_Form_Element_Text('SNAT_CD_NIVEL');
        $snat_cd_nivel->setLabel('Código do Nível:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setOptions(array('style' => 'width: 15px'));

        $snat_ds_nivel = new Zend_Form_Element_Text('SNAT_DS_NIVEL');
        $snat_ds_nivel->setLabel('Descrição:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setOptions(array('style' => 'width:600px'));

        $snat_sg_nivel = new Zend_Form_Element_Text('SNAT_SG_NIVEL');
        $snat_sg_nivel->setLabel('Sigla:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $snat_pz_atendimento = new Zend_Form_Element_Text('SNAT_PZ_ATENDIMENTO');
        $snat_pz_atendimento->setLabel('Prazo de Atendimento:')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($snat_id_nivel, $snat_id_grupo,
                           $snat_ds_nivel, $snat_sg_nivel, $snat_pz_atendimento,$snat_cd_nivel, $submit));
    }
    
}