<?php
class Sosti_Form_Grupoatendimento extends Zend_Form
{
    public function init()
    {
        $this->setAction('save')
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs'); 
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        $getLotacao = $rh_central->getLotacao();
        
        $SadTbTpcxTipoCaixa = new Application_Model_DbTable_SadTbTpcxTipoCaixa();
        $TpcxTipoCaixa = $SadTbTpcxTipoCaixa->fetchAll();
        
        $SadTbCxenCaixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $CxenCaixaEntrada = $SadTbCxenCaixaEntrada->fetchAll();
        
        $cxen_id_tipo_caixa = new Zend_Form_Element_Select('CXEN_ID_TIPO_CAIXA');
        $cxen_id_tipo_caixa->setLabel('Tipo de Caixa')
                         ->setRequired(true);
                         //->addMultiOptions(array(''=>'Primeiro escolha o seção'));
                         foreach ($TpcxTipoCaixa as $TipoCaixa) {
                            $cxen_id_tipo_caixa->addMultiOptions(array($TipoCaixa["TPCX_ID_TIPO_CAIXA"]=>$TipoCaixa["TPCX_DS_CAIXA_ENTRADA"]));
                         }  
                         
        $cxen_id_caixa_entrada = new Zend_Form_Element_Text('CXEN_ID_CAIXA_ENTRADA');
        $cxen_id_caixa_entrada->setLabel('Nome da caixa')
                         ->setRequired(true);
                         //->addMultiOptions(array(''=>'Primeiro escolha o seção'));
//                         foreach ($CxenCaixaEntrada as $CxenCaixaEntrada) {
//                            $cxen_id_caixa_entrada->addMultiOptions(array($CxenCaixaEntrada["CXEN_ID_CAIXA_ENTRADA"]=>$CxenCaixaEntrada["CXEN_DS_CAIXA_ENTRADA"]));
//                         }  
        
        $mode_sg_secao_unid_destino = new Zend_Form_Element_Select('MODE_SG_SECAO_UNID_DESTINO');
        $mode_sg_secao_unid_destino->setLabel('TRF1/Seção')
                     ->setRequired(true)
                     ->addMultiOptions(array(''=>''));
                     foreach($secao as $v){
                        $mode_sg_secao_unid_destino->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"].'|'.$v["LOTA_COD_LOTACAO"].'|'.$v["LOTA_TIPO_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
                     }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Seção/Subseção')
                         ->setRequired(true)
                         ->addMultiOptions(array(''=>'Primeiro escolha o seção'));
                         foreach ($getLotacao as $lotacao) {
                            $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"].'|'.$lotacao["LOTA_COD_LOTACAO"]=>$lotacao["LOTA_SIGLA_LOTACAO"].' - '.$lotacao["LOTA_DSC_LOTACAO"].' - '.$lotacao["LOTA_COD_LOTACAO"].' - '.$lotacao["LOTA_SIGLA_SECAO"]));
                         }  
                         
                         
        $mode_cd_secao_unid_destino = new Zend_Form_Element_Select('MODE_CD_SECAO_UNID_DESTINO');
        $mode_cd_secao_unid_destino->setLabel('Unidade')
                         ->setRequired(true)
                         ->addMultiOptions(array(''=>'Primeiro escolha o seção'));
                         foreach ($getLotacao as $lotacao) {
                            $mode_cd_secao_unid_destino->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"].'|'.$lotacao["LOTA_COD_LOTACAO"]=>$lotacao["LOTA_SIGLA_LOTACAO"].' - '.$lotacao["LOTA_DSC_LOTACAO"].' - '.$lotacao["LOTA_COD_LOTACAO"].' - '.$lotacao["LOTA_SIGLA_SECAO"]));
                         }        
        

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatorio')
                    ->setAttrib('style', 'display: none;');
//        $obrigatorio = ;

        $this->addElements(array($mode_sg_secao_unid_destino,
                                 $secao_subsecao,
                                 $mode_cd_secao_unid_destino,
                                        $cxen_id_tipo_caixa,
                                     $cxen_id_caixa_entrada,
                                 $submit,
                                 $obrigatorio));
    }

}