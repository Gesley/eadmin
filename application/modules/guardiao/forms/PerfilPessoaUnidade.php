<?php

class Guardiao_Form_PerfilPessoaUnidade extends Zend_Form
{

    public function init()
    {
        $this->setAction('form')
            ->setMethod('post');

        $OcsTbPerfPerfil = new Application_Model_DbTable_OcsTbPerfPerfil();

        $modelPerfis = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $perfis = $modelPerfis->getPerfisCriados();

        /**
         * Instancias
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $OcsTbPepePerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();

        /**
         * Verificar se o usuário é desenvolvedor e-Admin
         */
        $verifica = $OcsTbPepePerfilPessoa->verificaPessoaDesen($userNs->matricula);


        /**
         * Table para buscar os valores
         */
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();

            /**
             * Se o usuário FOR desenvolvedor
             */
            $secao = $rh_central->getSecoestrf1();
            $getLotacao = $rh_central->getLotacao();

            $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
            $trf1_secao->setLabel('TRF1/Seção')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOptions(array('' => ''));
            foreach ($secao as $v) {
                $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
            }

            $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
            $secao_subsecao->setLabel('Seção/Subseção')
                ->setAttrib('disabled', 'disabled')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOptions(array('' => 'Primeiro escolha a TRF1/Seção'));


            $unidade = new Zend_Form_Element_Select('LOTA_COD_LOTACAO');
            $unidade->setLabel('Unidade:')
                ->setRequired(true)
                ->setAttrib('onChange', 'this.form.submit();')
                ->addMultiOptions(array('' => 'Primeiro escolha o TRF1/Seção'));

            $grupopessoas = new Zend_Form_Element_Select('GRUPOPESSOAS');
            $grupopessoas->setLabel('Tipo de pesquisa: ')
                ->setRequired(true)
                ->setMultiOptions(array(
                    '' => 'Escolha um tipo de busca',
                    'pessoasunidade' => 'Pessoas da unidade',
                    'pessoaacesso' => 'Responsáveis pela Caixa',
                    'pessoassecao' => 'Todas as pessoas da minha Seção/Subseção',
                    'pessoastribunal' => 'Todas as pessoas do TRF1'
                ))
                ->setValue('');

        $perfis = $OcsTbPerfPerfil->fetchAll()->toArray();
        $pspa_id_perfil = new Zend_Form_Element_Select('PSPA_ID_PERFIL');
        $pspa_id_perfil->setRequired(true)
            ->setLabel('Perfil:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
            ->addMultiOptions(array('' => 'SELECIONE O PERFIL'));
        foreach ($perfis as $perfis_p):
            $pspa_id_perfil->addMultiOptions(array($perfis_p["PERF_ID_PERFIL"] => $perfis_p["PERF_DS_PERFIL"]));
        endforeach;

        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('PMAT_CD_MATRICULA');
        $docm_cd_matricula_cadastro->setRequired(true)
            ->setLabel('Pessoas: ')
            ->setDescription('Selecione um tipo de pesquisa acima.')
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $pupe_cd_matricula = new Zend_Form_Element_Text('PUPE_CD_MATRICULA');
        $pupe_cd_matricula->setRequired(true)
            ->setLabel('Pessoas: ')
            ->setDescription('Selecione um tipo de pesquisa acima.')
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $respcaixa_cd_matricula = new Zend_Form_Element_Text('RESPCAIXA_CD_MATRICULA');
        $respcaixa_cd_matricula->setRequired(true)
            ->setLabel('Pessoas: ')
            ->setDescription('Selecione um tipo de pesquisa acima.')
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $secao_cd_matricula = new Zend_Form_Element_Text('SECAO_CD_MATRICULA');
        $secao_cd_matricula->setRequired(true)
            ->setLabel('Pessoas: ')
            ->setDescription('Selecione um tipo de pesquisa acima.')
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $Associar = new Zend_Form_Element_Submit('Associar');
        $Associar->setOptions(array('class' => 'novo'));

        $Alterar = new Zend_Form_Element_Submit('Pesquisar');
        $Alterar->setOptions(array('class' => 'novo'));

        $this->addElements(array($trf1_secao,
            $secao_subsecao,
            $unidade,
            $grupopessoas,
            $pupe_cd_matricula,
            $docm_cd_matricula_cadastro,
            $respcaixa_cd_matricula,
            $secao_cd_matricula,
            $Associar,
            $Alterar));
    }

}
