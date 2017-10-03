<?php
class Guardiao_Form_PerfilPapel extends Zend_Form
{
    public function init()
    {
        $this->setAction('form')
             ->setMethod('post');
        $modelPerfilPapel = new Application_Model_DbTable_OcsTbPspaPerfilPapel();
        $modelPerfis = new Application_Model_DbTable_OcsTbPerfPerfil();
        $modulos = $modelPerfilPapel->getModulos();
        $perfis = $modelPerfis->getPerfisCriados();
        
        $pspa_id_perfil = new Zend_Form_Element_Select('PSPA_ID_PERFIL');
        $pspa_id_perfil->setRequired(true)
                     ->setLabel('Perfil:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     //->setAttrib('onChange','this.form.submit();')
                     ->addMultiOptions(array(''=>'SELECIONE O PERFIL'));
        foreach ($perfis as $perfis_p):
            $pspa_id_perfil->addMultiOptions(array($perfis_p["PERF_ID_PERFIL"] => $perfis_p["PERF_DS_PERFIL"]));
        endforeach;;
        
        $modl_nm_modulo = new Zend_Form_Element_Select('MODL_NM_MODULO');
        $modl_nm_modulo->setRequired(true)
                     ->setLabel('Módulo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->setAttrib('onChange','this.form.submit();')
                     ->addMultiOptions(array(''=>'SELECIONE O MÓDULO'));
        foreach ($modulos as $modulos_p):
            $modl_nm_modulo->addMultiOptions(array($modulos_p["MODL_ID_MODULO"] => $modulos_p["MODL_NM_MODULO"]));
        endforeach;;
        
        $this->addElements(array($pspa_id_perfil,$modl_nm_modulo));
    }
}