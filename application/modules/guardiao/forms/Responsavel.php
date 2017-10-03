<?php
class Guardiao_Form_Responsavel extends Zend_Form
{
    public function init()
    {
        $this->setAction('form')
             ->setMethod('post');
        $modelPerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $OcsTbPerfPerfil= new Application_Model_DbTable_OcsTbPerfPerfil();
        $modelRhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        
        $lota_cod_lotacao= new Zend_Form_Element_Text('LOTA_COD_LOTACAO');
        $lota_cod_lotacao->setRequired(true)
                         ->setLabel('Unidade:')
                         ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                         ->addValidator('NotEmpty');
        
        $apsp_id_pessoa = new Zend_Form_Element_Select('APSP_ID_PESSOA');
        $apsp_id_pessoa->setRequired(true)
                       ->setLabel('Pessoas da unidade selecionada:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty')
                       //->setAttrib('disabled', 'disabled')
                       //->setAttrib('onChange','this.form.submit();')
                       ->addMultiOptions(array(''=>'Informe primeiro a Unidade Administrativa'));
//        foreach ($pessoas as $pessoas_p):
//            $apsp_id_pessoa->addMultiOptions(array($pessoas_p["PMAT_ID_PESSOA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
//        endforeach;;
        

        $this->addElements(array($lota_cod_lotacao,$apsp_id_pessoa));

    }
}