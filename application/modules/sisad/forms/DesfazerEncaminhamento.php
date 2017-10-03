<?php
class Sisad_Form_DesfazerEncaminhamento extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('Desfazer Encaminhamento');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('desfazer');
        $aNamespace = new Zend_Session_Namespace('userNs');
        $OcsTbPepePerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $pessoas = $OcsTbPepePerfilPessoa->getPessoa($aNamespace->codlotacao,$aNamespace->siglasecao);       
        
        $mode_cd_secao_unid_destino = new Zend_Form_Element_Hidden('MODE_CD_SECAO_UNID_DESTINO');
        $mode_cd_secao_unid_destino->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim');       
        
//        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
//        $mofa_ds_complemento->setRequired(true)
//                 ->setLabel('Justificativa para desfazer encaminhamento:')
//                 ->addFilter('StripTags')
//                 ->addFilter('StringTrim')
//                 ->addValidator('NotEmpty')
//                 ->addValidator('StringLength', false, array(5, 500))
//                ->setAttrib('style', 'width: 628px;');
        
//        $mode_cd_matr_recebedor = new Zend_Form_Element_Select('MODE_CD_MATR_RECEBEDOR');
//        $mode_cd_matr_recebedor->setRequired(true)
//                       ->setLabel('Pessoa da Unidade:')
//                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
//                       ->addFilter('StripTags')
//                       ->addFilter('StringTrim')
//                       ->addValidator('NotEmpty');
//        foreach ($pessoas as $pessoas_p):
//            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
//        endforeach;;

        $submit = new Zend_Form_Element_Submit('Desfazer');
        
        $this->addElements(array($submit));
    }
}