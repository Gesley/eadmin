<?php

class Guardiao_Form_PermissaoPorUnidade extends Zend_Form {

    public function init() {
        $this->setAction('form')
                ->setMethod('post');
        $userNamespace = new Zend_Session_Namespace('userNs');

        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($userNamespace->matricula);

        /**
         * Session
         */
        $userNs = new Zend_Session_Namespace('userNs');

        /**
         * Table para buscar os valores
         */
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();

        /**
         * Buscando Seção, Subseção e Unidades que o usuário pode dar permissão
         */
        $secao = $rh_central->getSecoestrf1();
        $subsecao = $rh_central->getSecSubsecPai($userNs->siglasecao, $userNs->codlotacao);
        $getLotacao = $rh_central->getLotacaobySecao($userNs->siglasecao, $userNs->codsecsubseclotacao, $subsecao['LOTA_TIPO_LOTACAO']);


        $lota_cod_lotacao = new Zend_Form_Element_Select('LOTA_COD_LOTACAO');
        $lota_cod_lotacao->setRequired(true)
                ->setLabel('Unidades da minha Seção Judiciária:')
                ->setAttrib('style', 'text-transform: uppercase; width: 510px;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addMultiOptions(array('' => ''));
        foreach ($getLotacao as $lotacao) {
            $lota_cod_lotacao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
        }

        $pupe_cd_matricula = new Zend_Form_Element_Select('PUPE_CD_MATRICULA');
        $pupe_cd_matricula->setRequired(true)
                ->setLabel('Pessoas da Unidade:')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDescription('Nome: NOME DO USUÁRIO ou Matrícula: TR1111')
                ->addMultiOptions(array('' => 'Escolha uma pessoa da unidade'));

        $this->addElements(array($lota_cod_lotacao, $pupe_cd_matricula));
    }

}