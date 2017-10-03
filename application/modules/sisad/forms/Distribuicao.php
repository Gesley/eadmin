<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Distribuicao
 *
 * @author TR17358PS
 */
class Sisad_Form_Distribuicao extends Zend_Form {

    public function init() {
        $this->setAction('')
                ->setMethod('post')
                ->setName('processodistribuicao');

        $orgj_cd_orgao_julgador = new Zend_Form_Element_Hidden('ORGJ_CD_ORGAO_JULGADOR');
        $orgj_cd_orgao_julgador->setRequired(true)
                ->removeDecorator('label');
        
        $nome_orgao = new Zend_Form_Element_Text('nome_orgao');
        $nome_orgao->setRequired(true)
                ->setLabel('Orgão Julgador/ Comissão')
                ->addValidator('NotEmpty')
                ->setAttrib('style', 'width: 300px;');

        $grupoDistribuicao = new Zend_Form_Element_Radio('GRUPO_DISTRIBUICAO');
        $grupoDistribuicao->setLabel('Distribuição')
                ->setRequired(true)
                ->setMultiOptions(array(
                    'distautomatica'=>'Automática',
                    'distmanual'=>'Manual'))
                ->setValue('distautomatica');
        
        $pessoasOrgao = new Zend_Form_Element_Select('matricula_membro');
        $pessoasOrgao->setLabel('Selecione o membro do orgão')
                     ->setRequired(true)
                     ->setAttrib('style', 'width: 400px; ')
                     ->addMultiOptions(array(''=>'Selecione primeiro um orgão'));
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array(
            $orgj_cd_orgao_julgador,
            $nome_orgao,
            $grupoDistribuicao,
            $pessoasOrgao,
            $submit));
    }

}

?>
