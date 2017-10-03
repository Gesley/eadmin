<?php

class Sosti_Form_SolicitacoesSetor extends Zend_Form {

    public function init()
    {
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

        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('TRF1/Seção')
                ->setRequired(true)
                ->setAttrib('style', 'width: 540px;')
                ->addMultiOptions(array('' => ''));
        foreach ($rh_central->getSecoestrf1() as $v) {
            $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
        }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Seção/Subseção')
                ->setAttrib('style', 'width: 540px; ')
                ->setRequired(true)
                ->addMultiOptions(array('' => 'Primeiro Escolha o TRF1/Seção'));

        $unidade = new Zend_Form_Element_Select('UNPE_SG_SECAO');
        $unidade->setLabel('Unidade:')
                ->setRequired(false)
                ->setAttrib('style', 'width: 540px;')
                ->addMultiOptions(array('' => 'Primeiro Escolha a Seção/Subseção'));

        $pessoas = new Zend_Form_Element_Text('SOLICITANTE');
        $pessoas->setRequired(false)
                ->setLabel('Solicitante: ')
                ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao($userNamespace->siglasecao, $userNamespace->codlotacao);

        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(false)
                ->setLabel('Grupo de Serviço:')
                ->setAttrib('style', 'width: 540px;')
                ->addMultiOptions(array('' => 'Primeiro Escolha o TRF1/Seção'));

        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(false)
                ->setLabel('Serviço:')
                ->setAttrib('style', 'width: 540px;')
                ->addMultiOptions(array('' => 'Primeiro Escolha o Grupo de Serviço'));

        $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicial->setLabel('Data inicial:');

        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final->setLabel('Data final:');

        $pesquisar = new Zend_Form_Element_Submit('Pesquisar');
        $pesquisar->setOptions(array('class' => 'ui-button ui-widget ui-state-default ui-corner-all'));

        $this->addElements(array($trf1_secao, $secao_subsecao, $unidade, $pessoas,
            $sgrs_id_grupo, $sser_id_servico, $data_inicial, $data_final, $pesquisar));
    }
}