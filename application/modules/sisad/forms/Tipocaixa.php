<?php
class Sisad_Form_Tipocaixa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $tpcx_id_tipo_caixa = new Zend_Form_Element_Hidden('TPCX_ID_TIPO_CAIXA');
        $tpcx_id_tipo_caixa->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');

        $tpcx_ds_caixa_entrada = new Zend_Form_Element_Text('TPCX_DS_CAIXA_ENTRADA');
        $tpcx_ds_caixa_entrada->setRequired(true)
                        ->setLabel('Descrição do Tipo:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 735px; ')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty');
               
        $Sistemas_Trf = new Application_Model_DbTable_SistemasTrf();
        $Sistemas_Trf = $Sistemas_Trf->getSistemasdoEadmin();

        $tpcx_ds_proprietario_caixa = new Zend_Form_Element_Select('TPCX_DS_PROPRIETARIO_CAIXA');
        $tpcx_ds_proprietario_caixa->setRequired(true)
                      ->setLabel('Sistema Proprietário:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
        foreach ($Sistemas_Trf as $Sistemas) {
            $tpcx_ds_proprietario_caixa->addMultiOptions(array($Sistemas["NOME_SISTEMA"] => $Sistemas["NOME_SISTEMA"] . ' - ' . $Sistemas["DS_NOME_SISTEMA"]));
        }

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tpcx_id_tipo_caixa, 
                                $tpcx_ds_caixa_entrada, 
                                $tpcx_ds_proprietario_caixa,
                                $submit));
    }
}