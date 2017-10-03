<?php
class Sisad_Form_Lancamentofase extends Zend_Form
{
    public function init()
    {
        $this->setAction('sisad/lancamentofase/save')
             ->setMethod('post');

        $mofa_id_movimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $mofa_id_movimentacao->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $mofa_dh_fase = new Zend_Form_Element_Hidden('MOFA_DH_FASE');
        $mofa_dh_fase->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $FadmFaseAdm=new Application_Model_DbTable_SadTbFadmFaseAdm();
        $faseadm_array=$FadmFaseAdm->fetchAll()->toArray();

        $mofa_id_fase = new Zend_Form_Element_Select('MOFA_ID_FASE');
        $mofa_id_fase->setRequired(true)
                     ->setLabel('Lançamento de Fase.');
        foreach ($faseadm_array as $faseadm_array_p):
            $mofa_id_fase->addMultiOptions(array($faseadm_array_p["FADM_ID_FASE"] => $faseadm_array_p["FADM_DS_FASE"]));
        endforeach;


        $mofa_cd_matricula = new Zend_Form_Element_Hidden('MOFA_CD_MATRICULA');
        $mofa_cd_matricula->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');


        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento ->setLabel('Descrição:')
                             ->addValidator('NotEmpty')
                             ->setAttrib('style', 'width: 800px; height: 80px;')
                             ->addValidator('StringLength', false, array(5, 2000));

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($mofa_id_movimentacao, $mofa_dh_fase, $mofa_id_fase, $mofa_ds_complemento, $submit));
    }
}