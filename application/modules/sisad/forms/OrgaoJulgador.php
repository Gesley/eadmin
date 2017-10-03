<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrgaoJulgador
 *
 * @author TR17358PS
 */
class Sisad_Form_OrgaoJulgador extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $tppr_id_tipo_processo = new Zend_Form_Element_Hidden('ORGJ_CD_ORGAO_JULGADOR');
        $tppr_id_tipo_processo->setRequired(false)
                        ->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');

        $orgj_cd_orgao_julgador = new Zend_Form_Element_Text('ORGJ_NM_ORGAO_JULGADOR');
        $orgj_cd_orgao_julgador->setRequired(true)
                        ->setLabel('Nome:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 300px; ')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->addValidator('StringLength', false, array(3, 50));

        $orgj_nm_orgao_julgador = new Zend_Form_Element_Text('ORGJ_DS_ORGAO_JULGADOR');
        $orgj_nm_orgao_julgador->setRequired(true)
                        ->setLabel('Nome resumido:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 300px; ')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->addValidator('StringLength', false, array(3, 20));
        
        $orgj_sg_orgao_julgador = new Zend_Form_Element_Text('ORGJ_SG_ORGAO_JULGADOR');
        $orgj_sg_orgao_julgador->setRequired(true)
                        ->setLabel('Descrição do Tipo:')
                        ->addFilter('StripTags')
                        ->setAttrib('style', 'width: 300px; ')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->addValidator('StringLength', false, array(0, 3));
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tppr_id_tipo_processo, 
                                 $orgj_cd_orgao_julgador,
                                 $orgj_nm_orgao_julgador,
                                 $submit));
    }
}

?>
