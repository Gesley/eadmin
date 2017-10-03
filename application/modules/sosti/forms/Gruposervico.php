<?php
class Sosti_Form_Gruposervico extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        $getLotacao = $rh_central->getLotacao();
        
        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('Filtrar Região: TRF1 ou Seção')
                     ->setRequired(true)
                      ->setAttrib('style', 'width: 500px; ')
                     ->addMultiOptions(array(''=>''));
                     foreach($secao as $v){
                        $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"].'|'.$v["LOTA_COD_LOTACAO"].'|'.$v["LOTA_TIPO_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
                     }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Filtrar Unidade Pai: Trf1, Seção, Subseção')
                         ->setRequired(true)
                         ->setAttrib('style', 'width: 500px; ')
                         ->addMultiOptions(array(''=>''));
                         
        $unidade = new Zend_Form_Element_Select('UNPE_SG_SECAO');
        $unidade->setLabel('Unidade de responsável')
                         ->setRequired(true)
                         ->setAttrib('onChange','this.form.submit();')
                         ->setAttrib('style', 'width: 400px; ')
                         ->addMultiOptions(array(''=>'Primeiro escolha o seção'));
                         foreach ($getLotacao as $lotacao) {
                            $unidade->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"].'|'.$lotacao["LOTA_COD_LOTACAO"]=>$lotacao["LOTA_SIGLA_LOTACAO"].' - '.$lotacao["LOTA_DSC_LOTACAO"].' - '.$lotacao["LOTA_COD_LOTACAO"].' - '.$lotacao["LOTA_SIGLA_SECAO"]));
                         }        

        $sgrs_cd_grupo = new Zend_Form_Element_Hidden('SGRS_ID_GRUPO');
        $sgrs_cd_grupo->addFilter('Int')
                      ->removeDecorator('Label')
                      ->removeDecorator('HtmlTag');

        $sgrs_ds_grupo = new Zend_Form_Element_Textarea('SGRS_DS_GRUPO');
        $sgrs_ds_grupo->setLabel('Descrição:')
                      ->setRequired(true)
                      ->addFilter('StripTags')
                      ->setAttrib('style', 'width: 735px; height: 45px;')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $sgrs_ic_visivel = new Zend_Form_Element_Radio('SGRS_IC_VISIVEL');
        $sgrs_ic_visivel->setLabel('Indicador de visibilidade de usuário fora da TI:')
                        ->setRequired(true)
                        ->setMultiOptions(array('S'=>'Visível', 'N'=>'Não visível'));

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($trf1_secao, $secao_subsecao, $unidade, /*$lotacao,*/
                                 $sgrs_ds_grupo, $sgrs_ic_visivel, $sgrs_cd_grupo, $submit));
    }
    
}