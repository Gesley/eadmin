<?php

class Guardiao_Form_PermissaoPorPessoa extends Zend_Form
{

    public function init()
    {
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

        $this->setAction('form')
            ->setMethod('post');
        $radioTipoPesquisa = new Zend_Form_Element_Radio('tipo_pesquisa');
        $radioTipoPesquisa->setLabel('Tipo de Pesquisa:')
            ->setRequired(true)
            ->setMultiOptions(array(
                'formPorPessoa' => 'Por Pessoa - Pessoas',
                'formPorUnidade' => 'Por Unidade - Unidades'))
            ->setValue('formPorPessoa');

        $grupopessoas = new Zend_Form_Element_Select('GRUPOPESSOAS');
        $grupopessoas->setLabel('Tipo de pesquisa: ')
            ->setRequired(true)
            ->setMultiOptions(array(
                '' => 'Escolha um tipo de busca',
                'pessoasunidade' => 'Pessoas da unidade',
                'pessoaacesso' => 'Responsáveis pela Caixa',
                'pessoassecao' => 'Todas as pessoas da minha Seção Judiciária',
            	'pessoastribunal' => 'Todas as pessoas do TRF1'
            ))
            ->setValue('');

        $unidade = new Zend_Form_Element_Select('LOTA_COD_LOTACAO');
        $unidade->setLabel('Unidade:')
            ->setRequired(true)
            ->setAttrib('style', 'width: 500px; ')
            ->setAttrib('onChange', 'this.form.submit();')
            ->addMultiOptions(array('' => 'Primeiro escolha o TRF1/Seção'));
        foreach ($getLotacao as $lotacao) {
            $unidade->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
        }

        $pmat_cd_matricula = new Zend_Form_Element_Text('PMAT_CD_MATRICULA');
        $pmat_cd_matricula->setRequired(true)
            ->setLabel('Informe o nome ou matricula: ')
            ->setDescription('Nome: NOME DO USUÁRIO ou Matrícula: TR1111')
            ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
            ->setAttrib('id', 'PMAT_CD_MATRICULA');

//        $lota_cod_lotacao = new Zend_Form_Element_Select('LOTA_COD_LOTACAO');
//        $lota_cod_lotacao->setRequired(true)
//            ->setLabel('Unidades com permissão:')
//            ->setDescription('Unidades em que o usuário possua alguma permissão.')
//            ->setAttrib('style', 'text-transform: uppercase; width: 510px;')
//            ->addFilter('StripTags')
//            ->addFilter('StringTrim')
//            ->addValidator('NotEmpty')
//            ->addMultiOptions(array('' => ''));

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

        $Associar = new Zend_Form_Element_Submit('Salvar');
        $Associar->setOptions(array('class' => 'novo'));

        $this->addElements(array(
            $radioTipoPesquisa, 
            $grupopessoas, 
            $unidade, 
            $pupe_cd_matricula,
        	$docm_cd_matricula_cadastro,
            $respcaixa_cd_matricula,
            $secao_cd_matricula,
            $Associar
            ));
    }

}
