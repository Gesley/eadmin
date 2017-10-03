<?php
class Sisad_Form_EncaMalote extends Zend_Form
{
    public function init()
    {
        $this->setAction('protocolar')
             ->setMethod('post')
             ->setName('EncaMalote');

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSelectSecao();
        $getLotacao = $rh_central->getLotacao();
        $getUf = $rh_central->getCapitalUF();
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('EncaminhamentoMalote');
        
        $orgaoDestino = new Zend_Form_Element_Text('ORGAO_DESTINO');
        $orgaoDestino->setRequired(true)
                     ->setLabel('Orgão Destino:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200));
        
        $destinatario = new Zend_Form_Element_Text('POPD_CD_ORGA_DESTINO');
        $destinatario->setRequired(true)
                     ->setLabel('Destinatário:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200));
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($acao,
                                $orgaoDestino,
                                $destinatario,
                                $submit));
    }
}