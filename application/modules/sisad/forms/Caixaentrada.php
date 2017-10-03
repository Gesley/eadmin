<?php
class Sisad_Form_Caixaentrada extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $tpcx_id_tipo_caixa = new Zend_Form_Element_Hidden('CXEN_ID_CAIXA_ENTRADA');
        $tpcx_id_tipo_caixa->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');

        $tpcx_ds_caixa_entrada = new Zend_Form_Element_Text('CXEN_DS_CAIXA_ENTRADA');
        $tpcx_ds_caixa_entrada->setRequired(true)
                        ->setLabel('Descrição da Caixa:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 400px; ')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty');
               
        $SadTbTpcxTipoCaixa = new Application_Model_DbTable_SadTbTpcxTipoCaixa();
        $TpcxTipoCaixa = $SadTbTpcxTipoCaixa->fetchAll(null, $order)->toArray();

        $cxen_dt_inclusao = new Zend_Form_Element_Hidden('CXEN_DT_INCLUSAO');
        $cxen_dt_inclusao->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');
        $cxen_cd_matricula_inclusao = new Zend_Form_Element_Hidden('CXEN_CD_MATRICULA_INCLUSAO');
        $cxen_cd_matricula_inclusao->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');
        
        $cxen_dt_exclusao = new Zend_Form_Element_Hidden('CXEN_DT_EXCLUSAO');
        $cxen_dt_exclusao->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');
        $cxen_cd_matricula_exclusao = new Zend_Form_Element_Hidden('CXEN_CD_MATRICULA_EXCLUSAO');
        $cxen_cd_matricula_exclusao->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');
        
        $tpcx_ds_proprietario_caixa = new Zend_Form_Element_Select('CXEN_ID_TIPO_CAIXA');
        $tpcx_ds_proprietario_caixa->setRequired(true)
                      ->setLabel('Tipo de Caixa')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
        foreach ($TpcxTipoCaixa as $TipoCaixa) {
            $tpcx_ds_proprietario_caixa->addMultiOptions(array($TipoCaixa["TPCX_ID_TIPO_CAIXA"] => $TipoCaixa["TPCX_DS_CAIXA_ENTRADA"]));
        }

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tpcx_id_tipo_caixa, 
                                $tpcx_ds_caixa_entrada, 
                                $tpcx_ds_proprietario_caixa,
                                $submit));
    }
}