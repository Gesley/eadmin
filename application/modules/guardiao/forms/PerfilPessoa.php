<?php
class Guardiao_Form_PerfilPessoa extends Zend_Form
{
    public function init()
    {
        $this->setAction('form')
             ->setMethod('post');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $modelPerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        //$pessoas = $modelPerfilPessoa->getPessoa($userNamespace->codlotacao,$userNamespace->siglasecao);
        
        
        $OcsTbPerfPerfil= new Application_Model_DbTable_OcsTbPerfPerfil();
        $modelRhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $sistemas = $modelPerfilPessoa->getSistemas();
 
        
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($userNamespace->matricula);
        
        $lota_cod_lotacao= new Zend_Form_Element_Select('LOTA_COD_LOTACAO');
        $lota_cod_lotacao->setRequired(true)
                       ->setLabel('Unidade:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty')
                       ->setAttrib('onChange','this.form.submit();')
                       ->addMultiOptions(array(''=>'Escolha uma unidade'));
         foreach ($CaixasUnidadeAcesso as $CaixaUnidade):
                $lota_cod_lotacao->addMultiOptions(array(Zend_Json::encode($CaixaUnidade) => $CaixaUnidade["LOTA_SIGLA_LOTACAO"].' - '.$CaixaUnidade["LOTA_DSC_LOTACAO"].' - '.$CaixaUnidade["LOTA_COD_LOTACAO"]));
         endforeach;
        
        $grupopessoas = new Zend_Form_Element_Radio('GRUPOPESSOAS');
        $grupopessoas->setLabel('Pessoa')
                ->setRequired(true)
                ->setMultiOptions(array(
                    'pessoasunidade'=>'Pessoas da unidade',
                    'pessoastribunal'=>'Todas as pessoas do TRF1/Seção/Subseção:'))
                ->setValue(pessoasunidade);

        
        $perfis = $OcsTbPerfPerfil->fetchAll()->toArray();
        $pspa_id_perfil = new Zend_Form_Element_Select('PSPA_ID_PERFIL');
        $pspa_id_perfil->setRequired(true)
                     ->setLabel('Perfil:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     //->setAttrib('onChange','this.form.submit();')
                     ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                     ->addMultiOptions(array(''=>'SELECIONE O PERFIL'));
        foreach ($perfis as $perfis_p):
            $pspa_id_perfil->addMultiOptions(array($perfis_p["PERF_ID_PERFIL"] => $perfis_p["PERF_DS_PERFIL"]));
        endforeach;;
        
        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('PMAT_CD_MATRICULA');
        $docm_cd_matricula_cadastro->setRequired(true)
                                   ->setLabel('Informe o nome ou matricula: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        
        $pupe_cd_matricula = new Zend_Form_Element_Select('PUPE_CD_MATRICULA');
        $pupe_cd_matricula->setRequired(true)
                       ->setLabel('Pessoas da unidade:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty')
                       //->setAttrib('disabled', 'disabled')
                       ->setAttrib('onChange','this.form.submit();')
                       ->addMultiOptions(array(''=>'Escolha uma pessoa da unidade'));
        

        $this->addElements(array($lota_cod_lotacao,$grupopessoas,$pupe_cd_matricula, $docm_cd_matricula_cadastro));

    }
}