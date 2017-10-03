<?php
class Sosti_Form_Indicadornivelservico extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $sins_id_indicador = new Zend_Form_Element_Hidden('SINS_ID_INDICADOR');
         $sins_id_indicador->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');

        $sins_cd_indicador = new Zend_Form_Element_Hidden('SINS_CD_INDICADOR');
        $sins_cd_indicador->removeDecorator('Label');
       
        $sins_ds_indicador = new Zend_Form_Element_Text('SINS_DS_INDICADOR');
        $sins_ds_indicador->setLabel('Descrição:')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim')
                          ->addValidator('NotEmpty')
                          ->addValidator('Alnum', false, true)
                          ->setAttrib('style', 'width: 640px;')
                          ->addValidator('StringLength', false, array(5, 100));

        $sins_sg_indicador = new Zend_Form_Element_Text('SINS_SG_INDICADOR');
        $sins_sg_indicador->setLabel('Sigla:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->addValidator('Alnum', false, true)
                      ->addValidator('StringLength', false, array(1, 20));
        
        $sins_cd_grupo = new Zend_Form_Element_Select('SINS_ID_GRUPO');
        $tb_grupo_servico  = new Application_Model_DbTable_SosTbSgrsGrupoServico();
        $sins_cd_grupo->setLabel('Grupo de Serviço:')
                ->setRequired(true);
                foreach ($tb_grupo_servico->getGrupoServico($order) as $d) {
                    $sins_cd_grupo->addMultiOption($d['SGRS_ID_GRUPO'], $d['SGRS_DS_GRUPO'] .'-'. $d['LOTACAO']);
                }

        $sins_ds_formula_calc = new Zend_Form_Element_Text('SINS_DS_FORMULA_CALC');
        $sins_ds_formula_calc->setLabel('Descrição Fórmula de cálculo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->setAttrib('style', 'width: 640px;')
                      ->addValidator('StringLength', false, array(1, 100));

        $sins_id_unid_medida = new Zend_Form_Element_Select('SINS_ID_UNID_MEDIDA');
        $tb_unid_medida  = new Application_Model_DbTable_OcsTbUnmeUnidadeMedida();
        $sins_id_unid_medida->setLabel('Unidade de Medida:')
                ->setRequired(true);
                foreach ($tb_unid_medida->getUnidadeMedida() as $d) {
                    $sins_id_unid_medida->addMultiOption($d['UNME_ID_UNID_MEDIDA'], $d['UNME_DS_UNID_MEDIDA']);
                }

        $sins_ds_sinal_meta = new Zend_Form_Element_Text('SINS_DS_SINAL_META');
        $sins_ds_sinal_meta->setLabel('Descrição sinal comparativo da meta exigida:')
                           ->addFilter('StringTrim')
                           ->addValidator('NotEmpty')
                           ->addValidator('StringLength', false, array(1, 10));

        $sins_nr_meta = new Zend_Form_Element_Text('SINS_NR_META');
        $sins_nr_meta->setLabel('Número da Meta:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(1, 7));

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($sins_id_indicador,
                                 $sins_cd_grupo,
                                 $sins_cd_indicador,
                                 $sins_ds_indicador,
                                 $sins_sg_indicador,
                                 $sins_ds_formula_calc,
                                 $sins_id_unid_medida,
                                 $sins_ds_sinal_meta,
                                 $sins_nr_meta,
                                 $submit));
    }
    
}