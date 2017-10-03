<?php
class Guardiao_Form_Papel extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $modelPapel = new Application_Model_DbTable_OcsTbPaplPapel();
        $sistemas = $modelPapel->getSistemas();
        $acao = $modelPapel->getAcao();
        $modulos = $modelPapel->getModulos();
        $controle = $modelPapel->getControle();
        
        $papl_nm_papel = new Zend_Form_Element_Text('PAPL_NM_PAPEL');
        $papl_nm_papel->setRequired(true)
                     ->setLabel('*Nome:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->setAttrib('style', 'width: 500px;')
                     ->addValidator('NotEmpty');

        $papl_ds_finalidade = new Zend_Form_Element_Text('PAPL_DS_FINALIDADE');
        $papl_ds_finalidade->setRequired(true)
                           ->setLabel('*Descrição da Finalidade: ')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->setAttrib('style', 'width: 500px;')
                           ->addValidator('NotEmpty');
        
        $papl_sg_sistema = new Zend_Form_Element_Select('PAPL_SG_SISTEMA');
        $papl_sg_sistema->setRequired(true)
                     ->setLabel('Sistema:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                     ->addMultiOptions(array(''=>'SELECIONE O SISTEMA'));
        foreach ($sistemas as $sistemas_p):
            $papl_sg_sistema->addMultiOptions(array($sistemas_p["NOME_SISTEMA"] => $sistemas_p["NOME_SISTEMA"]  . ' - ' . $sistemas_p["DS_NOME_SISTEMA"]));
        endforeach;;
        
        $papl_id_acao_sistema = new Zend_Form_Element_Select('PAPL_ID_ACAO_SISTEMA');
        $papl_id_acao_sistema->setRequired(true)
                     ->setLabel('Ação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->setAttrib('style', 'width: 500px;')
                     ->addMultiOptions(array(''=>'SELECIONE A AÇÃO'));
        foreach ($acao as $acao_p):
            $papl_id_acao_sistema->addMultiOptions(array($acao_p["ACAO_ID_ACAO_SISTEMA"] => $acao_p["ACAO_NM_ACAO_SISTEMA"]));
        endforeach;;
        
        $ctrl_id_modulo = new Zend_Form_Element_Select('CTRL_ID_MODULO');
        $ctrl_id_modulo->setRequired(true)
                     ->setLabel('Modulo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->setAttrib('style', 'width: 500px;')
                     ->addMultiOptions(array(''=>'SELECIONE O MÓDULO'));
        foreach ($modulos as $modulos_p):
            $ctrl_id_modulo->addMultiOptions(array($modulos_p["MODL_ID_MODULO"] => $modulos_p["MODL_NM_MODULO"]));
        endforeach;;
        
        $acao_id_controle_sistema = new Zend_Form_Element_Select('ACAO_ID_CONTROLE_SISTEMA');
        $acao_id_controle_sistema->setRequired(true)
                     ->setLabel('Controle:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->setAttrib('style', 'width: 500px;')
                     //->setAttrib('onChange','this.form.submit();')
                     ->addMultiOptions(array(''=>'SELECIONE O CONTROLE'));
        foreach ($controle as $controle_p):
            $acao_id_controle_sistema->addMultiOptions(array($controle_p["CTRL_ID_CONTROLE_SISTEMA"] => $controle_p["CTRL_NM_CONTROLE_SISTEMA"]));
        endforeach;;
        
        $Criar = new Zend_Form_Element_Submit('Criar');
        $Criar->setOptions(array('class' => 'novo'));
        
        $Alterar = new Zend_Form_Element_Submit('Alterar');
        $Alterar->setOptions(array('class' => 'novo'));
        
        $this->addElements(array($papl_sg_sistema,
                                 $ctrl_id_modulo,
                                 $acao_id_controle_sistema,
                                 $papl_id_acao_sistema,
                                 $papl_nm_papel,
                                 $papl_ds_finalidade,
                                 $Criar,
                                 $Alterar));
    }
}