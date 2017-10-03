<?php
class Sosti_Form_Caixagruposervico extends Zend_Form
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
                        ->setAttrib('style', 'width: 800px; ')
                        ->setAttrib('readonly', 'readonly')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->setValue('CAIXA DE ');
               
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
        
        $cxen_id_tipo_caixa_hidden = new Zend_Form_Element_Hidden('CXEN_ID_TIPO_CAIXA_HIDDEN');
        $cxen_id_tipo_caixa_hidden->addFilter('Int')
                                    ->removeDecorator('Label')
                                    ->removeDecorator('HtmlTag');
        
        $tpcx_ds_proprietario_caixa = new Zend_Form_Element_Select('CXEN_ID_TIPO_CAIXA');
        $tpcx_ds_proprietario_caixa->setRequired(true)
                      ->setAttrib('style', 'width: 400px; ')
                      ->setLabel('Tipo de Caixa')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
        $tpcx_ds_proprietario_caixa->addMultiOptions(array('' => ''));
        foreach ($TpcxTipoCaixa as $TipoCaixa) {
            $tpcx_ds_proprietario_caixa->addMultiOptions(array($TipoCaixa["TPCX_ID_TIPO_CAIXA"] => $TipoCaixa["TPCX_DS_CAIXA_ENTRADA"]));
        }
        
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        //$getLotacao = $rh_central->getLotacao();
        
        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('Filtrar Região: TRF1 ou Seção')
                     ->setRequired(false)
                      ->setAttrib('style', 'width: 400px; ')
                     ->addMultiOptions(array(''=>''));
                     foreach($secao as $v){
                        $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"].'|'.$v["LOTA_COD_LOTACAO"].'|'.$v["LOTA_TIPO_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
                     }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Filtrar Unidade Pai: Trf1, Seção, Subseção')
                         ->setRequired(false)
                         ->setAttrib('style', 'width: 400px; ')
                         ->addMultiOptions(array(''=>''));
        
//                         foreach ($getLotacao as $lotacao) {
//                            $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"].'|'.$lotacao["LOTA_COD_LOTACAO"]=>$lotacao["LOTA_SIGLA_LOTACAO"].' - '.$lotacao["LOTA_DSC_LOTACAO"].' - '.$lotacao["LOTA_COD_LOTACAO"].' - '.$lotacao["LOTA_SIGLA_SECAO"]));
//                         }  
        
        
        $cxgs_id_grupo = new Zend_Form_Element_Select('CXGS_ID_GRUPO');
        $cxgs_id_grupo->setRequired(true)
                      ->setLabel('Grupo de Serviço')
                      ->setAttrib('style', 'width: 600px; ')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');


        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tpcx_id_tipo_caixa, 
                                $tpcx_ds_caixa_entrada,
                                $cxen_id_tipo_caixa_hidden,
                                $tpcx_ds_proprietario_caixa,
                                $trf1_secao,
                                $secao_subsecao,
                                $cxgs_id_grupo,
                                $submit));
    }
}