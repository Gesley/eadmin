<?php
class Sisad_Form_EncaInterno extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('EncaInterno');

        $userNamespace = new Zend_Session_Namespace('userNs');
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        $getLotacao = $rh_central->getLotacao();
        
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('EncaminhamentoInterno');
        
        $tipomovimentacao = new Zend_Form_Element_Radio('TIPO_MOVIMENTACAO');
        $tipomovimentacao->setLabel('Tipo de Movimentação:')
                       ->setRequired(true)
                       ->setValue('daunidade')
                       ->addMultiOption("internaunidade", "Interna Unidade")
                       ->addMultiOption("internalista", "Interna Lista");
        
        $mode_sg_secao_unid_destino = new Zend_Form_Element_Select('MODE_SG_SECAO_UNID_DESTINO');
        $mode_sg_secao_unid_destino->setLabel('Destino: TRF1/Seção')
                     ->setRequired(true)
                     ->setAttrib('style', 'width: 500px; ')
                     ->addMultiOptions(array(''=>''));
                     foreach($secao as $v){
                        $mode_sg_secao_unid_destino->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"].'|'.$v["LOTA_COD_LOTACAO"].'|'.$v["LOTA_TIPO_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
                     }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Destino: Seção/Subseção')
                         ->setRequired(true)
                         ->setAttrib('style', 'width: 500px; ')
                         ->addMultiOptions(array(''=>'Primeiro escolha o seção'));
//                         foreach ($getLotacao as $lotacao) {
//                            $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"].'|'.$lotacao["LOTA_COD_LOTACAO"].'|'.$lotacao["LOTA_TIPO_LOTACAO"]=>$lotacao["LOTA_SIGLA_LOTACAO"].' - '.$lotacao["LOTA_DSC_LOTACAO"].' - '.$lotacao["LOTA_COD_LOTACAO"].' - '.$lotacao["LOTA_SIGLA_SECAO"]));
//                         }  
                         
                         
        $mode_cd_secao_unid_destino = new Zend_Form_Element_Select('MODE_CD_SECAO_UNID_DESTINO');
        $mode_cd_secao_unid_destino->setLabel('Unidade de destino')
                         ->setRequired(true)
                         ->setAttrib('style', 'width: 600px; ')
                         ->addMultiOptions(array(''=>'Primeiro escolha o seção'));
//                         foreach ($getLotacao as $lotacao) {
//                            $mode_cd_secao_unid_destino->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"].'|'.$lotacao["LOTA_COD_LOTACAO"]=>$lotacao["LOTA_SIGLA_LOTACAO"].' - '.$lotacao["LOTA_DSC_LOTACAO"].' - '.$lotacao["LOTA_COD_LOTACAO"].' - '.$lotacao["LOTA_SIGLA_SECAO"]));
//                         }        
        
       $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                             ->setLabel('Descrição do Encaminhamento:')
                             ->setAttrib('style', 'width: 628px;')
                             ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter($Zend_Filter_HtmlEntities);

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($acao,$tipomovimentacao,$mode_sg_secao_unid_destino,$secao_subsecao,
               $mode_cd_secao_unid_destino,$mofa_ds_complemento,$submit));
    }
}