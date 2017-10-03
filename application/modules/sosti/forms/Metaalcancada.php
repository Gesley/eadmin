<?php
class Sosti_Form_Metaalcancada extends Zend_Form
{
    public function init()
    {

        $tb_sume_unidade = new Application_Model_DbTable_SosTbSumeUnidadeMedida();
        $this->setAction('sosti/metaalcanda/save')
             ->setMethod('post');

        $sman_id_indicador = new Zend_Form_Element_Hidden('SMAN_ID_INDICADOR');
        $sman_id_indicador->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $sman_id_meta = new Zend_Form_Element_Text('SMAN_ID_META');
        $sman_id_meta->setLabel('Id da meta:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty');
               
        $sman_nr_inicial_meta = new Zend_Form_Element_Text('SMAN_NR_INICIAL_META');
        $sman_nr_inicial_meta->setLabel('Nº Inicial da Meta:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty');

        $sman_nr_final_meta = new Zend_Form_Element_Text('SMAN_NR_FINAL_META');
        $sman_nr_final_meta->setLabel('Nº Final da Meta:')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->addValidator('NotEmpty');

        $sman_nr_glosa = new Zend_Form_Element_Text('SMAN_NR_GLOSA');
        $sman_nr_glosa->setLabel('Glosa:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $sman_id_unid_medida = new Zend_Form_Element_Select('SINS_ID_UNID_MEDIDA');
        $tb_unid_medida  = new Application_Model_DbTable_SosTbSumeUnidadeMedida();
        $sman_id_unid_medida->setLabel('Unidade de Medida:')
                ->setRequired(true);
                foreach ($tb_unid_medida->getUnidadeMedida() as $d) {
                    $sman_id_unid_medida->addMultiOption($d['SUME_ID_UNID_MEDIDA'], $d['SUME_DS_UNID_MEDIDA']);
                }
//
//        $sser_ic_ativo = new Zend_Form_Element_Checkbox('SSER_IC_ATIVO');
//        $sser_ic_ativo->setLabel('Flag Ativo:')
//                      ->addFilter('StripTags')
//                      ->addFilter('StringTrim')
//                      ->addValidator('NotEmpty')
//                      ->setCheckedValue('S')
//                      ->setUncheckedValue('N');
//
//        $submit = new Zend_Form_Element_Submit('Salvar');
//
   $this->addElements(array($sman_id_indicador, $sman_id_meta,$sman_nr_inicial_meta,
                            $sman_nr_final_meta,$sman_nr_glosa,$sman_id_unid_medida,
                            $submit));
    }
}