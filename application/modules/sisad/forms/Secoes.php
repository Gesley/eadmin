<?php
class Sisad_Form_Secoes extends Zend_Form
{
    public function init()
    {
        $this->setAction('sisad/caixaunidade/acoescaixa')
             ->setMethod('post');
        
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSelectSecao();

        $lota_sigla_secao = new Zend_Form_Element_Select('LOTA_SIGLA_SECAO');
        $lota_sigla_secao->setLabel('TRF1 / Seções e Subseções Judiciárias')
                     ->setRequired(true);
                     foreach($secao as $v){
                        $lota_sigla_secao->addMultiOptions(array($v["LOTA_COD_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
                     }
        
        $this->addElements(array($lota_sigla_secao));
    }
}