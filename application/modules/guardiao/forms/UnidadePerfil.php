<?php

class Guardiao_Form_UnidadePerfil extends Zend_Form {

    public function init() {
        $this->setAction('form')
                ->setMethod('post');
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
         * Se o usuário NÃO FOR desenvolvedor
         */
        if ($verifica['VALOR'] == 0) {
            /**
             * Buscando Seção, Subseção e Unidades que o usuário pode dar permissão
             */
            $secao = $rh_central->getSecoestrf1();
            $subsecao = $rh_central->getSecSubsecPai($userNs->siglasecao, $userNs->codlotacao);
            $getLotacao = $rh_central->getLotacaobySecao($userNs->siglasecao, $userNs->codsecsubseclotacao, $subsecao['LOTA_TIPO_LOTACAO']);

            $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
            $trf1_secao->setLabel('TRF1/Seção')
                    ->setRequired(true)
                    ->setAttrib('style', 'width: 500px; ');
            foreach ($secao as $v) {
                if ($v["SESB_SIGLA_SECAO_SUBSECAO"] == $userNs->siglasecao && $v["LOTA_COD_LOTACAO"] == $userNs->codsecsubseclotacao) {
                    $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
                }
            }

            $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
            $secao_subsecao->setLabel('Seção/Subseção')
                    ->setAttrib('style', 'width: 500px; ')
                    ->setRequired(true);
            $secao_subsecao->addMultiOptions(array($subsecao["LOTA_SIGLA_SECAO"] . '|' . $subsecao["LOTA_COD_LOTACAO"] . '|' . $subsecao["LOTA_TIPO_LOTACAO"] => $subsecao["LOTA_SIGLA_LOTACAO"] . ' - ' . $subsecao["LOTA_DSC_LOTACAO"] . ' - ' . $subsecao["LOTA_COD_LOTACAO"] . ' - ' . $subsecao["LOTA_SIGLA_SECAO"]));

            $unidade = new Zend_Form_Element_Select('UNPE_SG_SECAO');
            $unidade->setLabel('Unidade:')
                    ->setRequired(true)
                    ->setAttrib('style', 'width: 500px; ')
                    ->setAttrib('onChange', 'this.form.submit();')
                    ->addMultiOptions(array('' => 'Primeiro escolha o TRF1/Seção'));
            foreach ($getLotacao as $lotacao) {
                $unidade->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
            }
        } else {
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
                    ->setAttrib('style', 'width: 500px; ')
                    ->setRequired(true)
                    ->addMultiOptions(array('' => 'Primeiro escolha a TRF1/Seção'));

            $unidade = new Zend_Form_Element_Select('UNPE_SG_SECAO');
            $unidade->setLabel('Unidade:')
                    ->setRequired(true)
                    ->setAttrib('onChange', 'this.form.submit();')
                    ->addMultiOptions(array('' => 'Primeiro escolha o TRF1/Seção'));
            }

        $unpe_id_perfil = new Zend_Form_Element_Select('UNPE_ID_PERFIL');
        $unpe_id_perfil->setLabel('Perfil:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('multiple', 'multiple')
                ->setAttrib('id', 'box1View')
                ->setAttrib('style', 'text-transform: uppercase; width: 400px; height:400px;')
                ->addMultiOptions(array('' => 'SELECIONE O PERFIL'));
        foreach ($perfis as $perfis_p):
            $unpe_id_perfil->addMultiOptions(array($perfis_p["PERF_ID_PERFIL"] => $perfis_p["PERF_DS_PERFIL"]));
        endforeach;

        $Associar = new Zend_Form_Element_Submit('Salvar');
        $Associar->setOptions(array('class' => 'novo'));

        $Listar = new Zend_Form_Element_Button('Listar');
        $Listar->setOptions(array('class' => 'novo'));

        $this->addElements(array($trf1_secao, $secao_subsecao, $unidade, $unpe_id_perfil, $Associar, $Listar));
    }
}
