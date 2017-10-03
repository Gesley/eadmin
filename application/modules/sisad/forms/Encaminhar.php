<?php

/**
 * @category	TRF1
 * @package		Sisad_Form_Encaminhar
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Daniel Rodrigues
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de formulário para encaminhamento de documentos
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Sisad_Form_Encaminhar extends Zend_Form
{

    public function init() {
        $userNs = new Zend_Session_Namespace('userNs');

        $service_pessoa = new Services_Rh_Pessoa();
        $service_lotacao = new Services_Rh_Lotacao();
        $secao = $service_lotacao->retornaComboTrfSecao();

        $this->setName('encaminhar')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setMethod('post')
                ->setAttrib('id', 'encaminhar');

        $acao = new Zend_Form_Element_Hidden('ACAO_ENCAMINHAR');
        $acao->setValue('ACAO_ENCAMINHAR');

        $mode_sg_secao_unid_destino = new Zend_Form_Element_Select('MODE_SG_SECAO_UNID_DESTINO');
        $mode_sg_secao_unid_destino->setLabel('*TRF1/Seção:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setAttrib('class', 'combo_encadeada_caixa')
                ->addMultiOptions(array('' => ''))
                ->addMultiOptions($secao);

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('*Seção/Subseção:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setAttrib('class', 'combo_encadeada_caixa')
                ->addMultiOptions(array('' => 'Primeiro escolha o seção'));

        $mode_cd_secao_unid_destino = new Zend_Form_Element_Select('MODE_CD_SECAO_UNID_DESTINO');
        $mode_cd_secao_unid_destino->setLabel('*Unidade de destino:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setAttrib('class', 'combo_encadeada_caixa')
                ->addMultiOptions(array('' => 'Primeiro escolha o seção'));

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                ->setLabel('*Descrição do Encaminhamento:')
                ->setAttrib('style', 'width: 500px;')
                ->addValidator('StringLength', false, array(5, 4000))
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $anexos = new Zend_Form_Element_File('ANEXOS');
        $anexos->setLabel('Anexos:')
                ->setIsArray(true)
//                ->addValidator(new Zend_Validate_File_Extension(array(0 => 'pdf')))
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setAttrib('class', 'campo-anexo')
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setDescription('Até 20 Anexos. A soma do tamanho dos arquivos não deve ultrapassar 50 Megas.');

        $radio_tipo_encaminhamento = new Zend_Form_Element_Radio('radio_tipo_encaminhamento');
        $radio_tipo_encaminhamento
                ->setLabel('*Encaminhar para:')
                ->setMultiOptions(array(
                    'caixa_unidade' => 'Caixa da unidade',
                    'caixa_pessoal' => 'Caixa pessoal',
                    'pessoa_unidade' => 'Pessoas da Unidade',
                    'listas_internas' => 'Listas Internas'));

        $checkbox_minha_caixa_pessoal = new Zend_Form_Element_Checkbox('checkbox_minha_caixa_pessoal');
        $checkbox_minha_caixa_pessoal
                ->setLabel('Encaminhar para minha caixa pessoal');

        $checkbox_apenas_responsaveis = new Zend_Form_Element_Checkbox('checkbox_apenas_responsaveis');
        $checkbox_apenas_responsaveis
                ->setLabel('Apenas responsáveis pela unidade');

        $checkbox_apenas_minhas_caixas = new Zend_Form_Element_Checkbox('check_apenas_caixa_minha_responsabilidade');
        $checkbox_apenas_minhas_caixas
                ->setLabel('Apenas caixas de minha responsabilidade');

        $comboUnidadesAgrupadasPorResponsavel = $service_pessoa->retornaComboUnidadesAgrupadasPorResponsavel();
        $caixasMinhaResponsabilidade = new Zend_Form_Element_Select('caixa_minha_responsabilidade');
        $caixasMinhaResponsabilidade->setLabel('*Caixas de minha responsabilidade:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;')
                ->addMultiOption('', '')
                ->addMultiOptions($comboUnidadesAgrupadasPorResponsavel[$userNs->matricula]);

        $caixasResponsabilidadeUsuario = new Zend_Form_Element_Select('caixa_responsabilidade_usuario');
        $caixasResponsabilidadeUsuario->setLabel('*Caixas de responsabilidade do usuário:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;')
                ->addMultiOption('', '');

        $pessoas_trf = new Zend_Form_Element_Text('pessoa_trf1');
        $pessoas_trf->setLabel('*Pessoa do TRF1: ')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;');

        $pessoas_da_unidade = new Zend_Form_Element_Select('pessoas_da_unidade');
        $pessoas_da_unidade->setLabel('*Pessoas da unidade:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;');

        $responsaveis_pela_unidade = new Zend_Form_Element_Select('responsaveis_pela_unidade');
        $responsaveis_pela_unidade->setLabel('*Responsáveis pela unidade:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;');

        $acao_sistema = new Zend_Form_Element_Hidden('acao_sistema');
        $acao_sistema->removeDecorator('Html')
                ->removeDecorator('label');

        $controle_sistema = new Zend_Form_Element_Hidden('controle_sistema');
        $controle_sistema->removeDecorator('Html')
                ->removeDecorator('label');

        $modulo_sistema = new Zend_Form_Element_Hidden('modulo_sistema');
        $modulo_sistema->removeDecorator('Html')
                ->removeDecorator('label');

        $salvar = new Zend_Form_Element_Submit('salvar');
        $salvar->setLabel('Encaminhar');

        $this->addElements(array(
            $acao,
            $mofa_ds_complemento,
            $anexos,
            $radio_tipo_encaminhamento,
            $checkbox_minha_caixa_pessoal,
            $checkbox_apenas_responsaveis,
            $checkbox_apenas_minhas_caixas,
            $mode_sg_secao_unid_destino,
            $secao_subsecao,
            $mode_cd_secao_unid_destino,
            $caixasMinhaResponsabilidade,
            $pessoas_trf,
            $caixasResponsabilidadeUsuario,
            $pessoas_da_unidade,
            $responsaveis_pela_unidade,
            $acao_sistema,
            $controle_sistema,
            $modulo_sistema,
            $salvar
        ));
    }

    public function populateValidacao($data) {
        
        if ($data['radio_tipo_encaminhamento'] == 'caixa_pessoal') {
            $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
            $dadosMatricula = explode(' - ', $data['pessoa_trf1']);
            $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($dadosMatricula[0]);
            foreach ($CaixasUnidadeAcesso as $value) :
                $valueCampo = $value["LOTA_SIGLA_SECAO"] . '|' . $value['LOTA_COD_LOTACAO'];
                $label = $value["LOTA_SIGLA_LOTACAO"] . ' - ' . $value["LOTA_DSC_LOTACAO"] . ' - ' . $value["LOTA_COD_LOTACAO"] . ' - ' . $value["LOTA_SIGLA_SECAO"] . ' - ' . $value["FAMILIA_LOTACAO"];
                $this->caixa_responsabilidade_usuario->addMultiOption($valueCampo, $label);
            endforeach;
        }

        if ($data['radio_tipo_encaminhamento'] == 'pessoa_unidade' || $data['radio_tipo_encaminhamento'] == 'caixa_unidade') {
            $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
            $dadosSecao = explode('|', $data['MODE_SG_SECAO_UNID_DESTINO']);
            $dadosSubsecao = explode('|', $data['SECAO_SUBSECAO']);
            $dadosUnidade = explode('|', $data['MODE_CD_SECAO_UNID_DESTINO']);
            $dadosUnidade2 = explode('|', $data['caixa_minha_responsabilidade']);

            if ($dadosSecao[0] != "") {
                $arraySecaoSubsecao = $RhCentralLotacao->getSubSecoes($dadosSecao[0], $dadosSecao[1]);
                foreach ($arraySecaoSubsecao as $valueS) :
                    $valueCampoS = $valueS["LOTA_SIGLA_SECAO"] . '|' . $valueS['LOTA_COD_LOTACAO'] . '|' . $valueS["LOTA_TIPO_LOTACAO"];
                    $labelS = $valueS["LOTA_SIGLA_LOTACAO"] . ' - ' . $valueS["LOTA_DSC_LOTACAO"] . ' - ' . $valueS["LOTA_COD_LOTACAO"] . ' - ' . $valueS["LOTA_SIGLA_SECAO"] . ' - ' . $valueS["LOTA_LOTA_COD_LOTACAO_PAI"];
                    $this->SECAO_SUBSECAO->addMultiOption($valueCampoS, $labelS);
                endforeach;
            }
            if ($dadosSubsecao[0] != "") {
                $arrayUnidades = $Lotacao_array = $RhCentralLotacao->getLotacaobySecao($dadosSubsecao[0], $dadosSubsecao[1], $dadosSubsecao[2]);
                foreach ($arrayUnidades as $valueU) :
                    $valueCampoU = $valueU["LOTA_SIGLA_SECAO"] . '|' . $valueU['LOTA_COD_LOTACAO'];
                    $labelU = $valueU["LOTA_SIGLA_LOTACAO"] . ' - ' . $valueU["LOTA_DSC_LOTACAO"] . ' - ' . $valueU["LOTA_COD_LOTACAO"] . ' - ' . $valueU["LOTA_SIGLA_SECAO"] . ' - ' . $valueU["LOTA_LOTA_COD_LOTACAO_PAI"];
                    $this->MODE_CD_SECAO_UNID_DESTINO->addMultiOption($valueCampoU, $labelU);
                endforeach;
            }
            $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            $this->pessoas_da_unidade->addMultiOption('', '');

            if ($dadosUnidade[0] != "") {
                $arrayPessoas = $ocsTbPupePerfilUnidPessoa->getPessoasDaUnidade($dadosUnidade);
                $this->pessoas_da_unidade->addMultiOption(' ', ' ');
                foreach ($arrayPessoas as $valueP) :
                    $valueCampoP = $valueP['PMAT_CD_MATRICULA'];
                    $labelP = $valueP['PMAT_CD_MATRICULA'] . ' - ' . $valueP['PNAT_NO_PESSOA'];
                    $this->pessoas_da_unidade->addMultiOption($valueCampoP, $labelP);
                endforeach;
            } else {
                if ($dadosUnidade2[0] != '') {
                    $arrayPessoas = $ocsTbPupePerfilUnidPessoa->getPessoasDaUnidade($dadosUnidade2);
                    $this->pessoas_da_unidade->addMultiOption(' ', ' ');
                    foreach ($arrayPessoas as $valueP) :
                        $valueCampoP = $valueP['PMAT_CD_MATRICULA'];
                        $labelP = $valueP['PMAT_CD_MATRICULA'] . ' - ' . $valueP['PNAT_NO_PESSOA'];
                        $this->pessoas_da_unidade->addMultiOption($valueCampoP, $labelP);
                    endforeach;
                }
            }
        }

        parent::populate($data);
    }

    public function isValid($data) {

        $this->populateValidacao($data);
        if ($data['radio_tipo_encaminhamento'] == 'caixa_unidade') {
            if (isset($data['check_apenas_caixa_minha_responsabilidade']) && $data['check_apenas_caixa_minha_responsabilidade'] != '0') {
                $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(false);
                $this->SECAO_SUBSECAO->setRequired(false);
                $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(false);
                $this->caixa_minha_responsabilidade->setRequired(true);
                $this->caixa_responsabilidade_usuario->setRequired(false);
                $this->pessoas_da_unidade->setRequired(false);
                $this->responsaveis_pela_unidade->setRequired(false);
                $this->pessoa_trf1->setRequired(false);
            } else {
                $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(true);
                $this->SECAO_SUBSECAO->setRequired(true);
                $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(true);
                $this->caixa_minha_responsabilidade->setRequired(false);
                $this->caixa_responsabilidade_usuario->setRequired(false);
                $this->pessoas_da_unidade->setRequired(false);
                $this->responsaveis_pela_unidade->setRequired(false);
                $this->pessoa_trf1->setRequired(false);
            }
        } elseif ($data['radio_tipo_encaminhamento'] == 'caixa_pessoal') {
            if ($data['checkbox_minha_caixa_pessoal'] == '0') {
                $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(false);
                $this->SECAO_SUBSECAO->setRequired(false);
                $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(false);
                $this->caixa_minha_responsabilidade->setRequired(false);
                $this->caixa_responsabilidade_usuario->setRequired(true);
                $this->pessoas_da_unidade->setRequired(false);
                $this->responsaveis_pela_unidade->setRequired(false);
                $this->pessoa_trf1->setRequired(true);
            } else {
                $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(false);
                $this->SECAO_SUBSECAO->setRequired(false);
                $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(false);
                $this->caixa_minha_responsabilidade->setRequired(false);
                $this->caixa_responsabilidade_usuario->setRequired(false);
                $this->pessoas_da_unidade->setRequired(false);
                $this->responsaveis_pela_unidade->setRequired(false);
                $this->pessoa_trf1->setRequired(false);
            }
        } elseif ($data['radio_tipo_encaminhamento'] == 'pessoa_unidade') {
            if ($data['check_apenas_caixa_minha_responsabilidade'] == '0') {
                $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(true);
                $this->SECAO_SUBSECAO->setRequired(true);
                $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(true);
                $this->caixa_minha_responsabilidade->setRequired(false);
                $this->caixa_responsabilidade_usuario->setRequired(false);
                $this->pessoas_da_unidade->setRequired(true);
                $this->responsaveis_pela_unidade->setRequired(false);
                $this->pessoa_trf1->setRequired(false);
            } else {
                $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(false);
                $this->SECAO_SUBSECAO->setRequired(false);
                $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(false);
                $this->caixa_minha_responsabilidade->setRequired(true);
                $this->caixa_responsabilidade_usuario->setRequired(false);
                $this->pessoas_da_unidade->setRequired(true);
                $this->responsaveis_pela_unidade->setRequired(false);
                $this->pessoa_trf1->setRequired(false);
            }
        } elseif ($data['radio_tipo_encaminhamento'] == 'listas_internas') {
            $this->MODE_SG_SECAO_UNID_DESTINO->setRequired(false);
            $this->SECAO_SUBSECAO->setRequired(false);
            $this->MODE_CD_SECAO_UNID_DESTINO->setRequired(false);
            $this->caixa_minha_responsabilidade->setRequired(false);
            $this->caixa_responsabilidade_usuario->setRequired(false);
            $this->pessoas_da_unidade->setRequired(false);
            $this->responsaveis_pela_unidade->setRequired(false);
            $this->pessoa_trf1->setRequired(false);
        }
        return parent::isValid($data);
    }

}