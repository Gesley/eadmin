<?php

/**
 * @category	TRF1
 * @package		Sisad_Form_Documento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de formulário para documentos
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
class Sisad_Form_Documento extends Zend_Form
{

    private $populate_executado = false;

    /**
     * LEIA-ME PORFAVOR
     * 
     * metodo de inicialização dos campos do formulário de documento
     * os campos são definidos como obrigatórios ou não pela function isValid()
     * que foi sobrescrita mais abaixo
     */
    public function init()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();

        $service_tipoDocumento = new Services_Sisad_TipoDocumento();
        $service_tipoSituacaoDocumento = new Services_Sisad_TipoSituacaoDocumento();
        $service_tipoConfidencialidade = new Services_Sisad_TipoConfidencialidade();
        $service_pessoa = new Services_Rh_Pessoa();
        $service_pctt = new Services_Sisad_Pctt();
        $service_lotacao = new Services_Rh_Lotacao();

        $formParteVista = new Sisad_Form_ParteVista();

        $this->setName('documento')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setMethod('post');

        $radio_tipo_cadastro = new Zend_Form_Element_Radio('radio_tipo_cadastro');
        $radio_tipo_cadastro->setRequired()
                ->setLabel('*Tipo de cadastro:')
                ->setMultiOptions(array('interno' => 'Documentos Internos', 'externo' => 'Documentos Externos', 'pessoal' => 'Documento Pessoal'));

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Select('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora
                ->setLabel('*Unidade emissora:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade da lista.')
                ->addMultiOption('', '')
                ->addMultiOptions($service_lotacao->retornaComboUnidadesDaMinhaSecao());

        $docm_id_pessoa_externo = new Zend_Form_Element_Select('DOCM_ID_PESSOA_EXTERNO');
        $docm_id_pessoa_externo
                ->setLabel('*Orgão/empresa emissora:')
                ->setAttrib('class', 'apenas_documento_externo')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione a empresa/orgão.')
                ->addMultiOption('', '')
                ->addMultiOptions($service_pessoa->retornaComboPessoasJuridicasTrf1());

        $docm_ds_nome_emissor_externo = new Zend_Form_Element_Text('DOCM_DS_NOME_EMISSOR_EXTERNO');
        $docm_ds_nome_emissor_externo
                ->setLabel('*Emissor/assinante:')
                ->setAttrib('class', 'apenas_documento_externo');

        $docm_cd_lotacao_redatora = new Zend_Form_Element_Select('DOCM_CD_LOTACAO_REDATORA');
        $docm_cd_lotacao_redatora
                ->setLabel('*Unidade redatora:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade da lista.')
                ->addMultiOption('', '')
                ->addMultiOptions($service_lotacao->retornaComboUnidadesDaMinhaSecao());

        $docm_nr_dcmto_usuario = new Zend_Form_Element_Text('DOCM_NR_DCMTO_USUARIO');
        $docm_nr_dcmto_usuario->setLabel('Número de controle do usuário:')
                ->setAttrib('maxLength', 60)
                ->addValidator('StringLength', false, array(5, 60));

        $docm_id_tipo_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc
                ->setLabel('*Tipo de documento:')
                ->addMultiOption('', '')
                ->addMultiOptions($service_tipoDocumento->retornaCombo());

        $docm_id_pctt = new Zend_Form_Element_Select('DOCM_ID_PCTT');
        $docm_id_pctt
                ->setLabel('*Assunto do documento:')
                ->addMultiOption('', '')
                ->addMultiOptions($service_pctt->retornaCombo())
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.');


        $docm_id_tipo_situacao_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_SITUACAO_DOC');
        $docm_id_tipo_situacao_doc
                ->setLabel('*Estado do documento:')
                ->addMultiOptions($service_tipoSituacaoDocumento->retornaCombo());

        $docm_ds_palavra_chave = new Zend_Form_Element_Text('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave
                ->setLabel('*Palavras chave:');

        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc
                ->setAttrib('maxLength', 3000)
                ->addValidator('StringLength', false, array(5, 3000))
                ->setLabel('*Ementa:');


        $docm_id_confidencialidade = new Zend_Form_Element_Select('DOCM_ID_CONFIDENCIALIDADE');
        $docm_id_confidencialidade
                ->setLabel('*Confidencialidade:')
                ->addMultiOptions($service_tipoConfidencialidade->retornaComboAdministrativa());

        $arquivo_principal = new Zend_Form_Element_File('arquivo_principal');
        $arquivo_principal->setLabel('*Arquivo principal:')
//                ->addValidator(new Zend_Validate_File_Extension(array(0 => 'pdf')))
                ->addValidator('Size', false, 52428800) // limit to 50m
                ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setDescription('O tamanho máximo é de 50 Megas.');

        $anexos = new Zend_Form_Element_File('ANEXOS');
        $anexos->setLabel('Anexos:')
                ->setIsArray(true)
                //->addValidator(new Zend_Validate_File_Extension($mapperDocumento->getExtensoesAceitas()))
//                ->addValidator(new Zend_Validate_File_Extension(array(0 => 'pdf')))
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setAttrib('class', 'campo-anexo')
                ->setDestination(APPLICATION_PATH . '/../temp')
                //->setAttribs(array('class' => 'Multi', 'accept' => $extensao->getExtensoesAceitas(), 'maxlength' => 20, 'multiple' => true))
                ->setDescription('Até 20 Anexos. A soma do tamanho dos arquivos não deve ultrapassar 50 Megas.');

        $check_box_autuacao = new Zend_Form_Element_Checkbox('check_box_autuacao');
        $check_box_autuacao->setLabel('Autuar documento');

        $prdi_ds_texto_autuacao = new Zend_Form_Element_Textarea('PRDI_DS_TEXTO_AUTUACAO');
        $prdi_ds_texto_autuacao
                ->setLabel('*Objeto do processo:')
                ->setAttrib('maxLength', 2000)
                ->addValidator('StringLength', false, array(5, 2000))
                ->setDescription('Obs: O assunto, as palavras chaves, o estado, a confidencialidade e as vistas do documento serão replicados para o processo administrativo criado. O documento será como autuado.');

        //CAMPOS PARA ENCAMINHAMENTO DO DOCUMENTO
        $radio_tipo_encaminhamento = new Zend_Form_Element_Radio('radio_tipo_encaminhamento');
        $radio_tipo_encaminhamento
                ->setLabel('*Encaminhar para:')
                ->setMultiOptions(array('caixa_unidade' => 'Caixa da unidade', 'caixa_pessoal' => 'Caixa pessoal', 'caixa_rascunho' => 'Minha caixa de rascunho'));

        $checkbox_minha_caixa_pessoal = new Zend_Form_Element_Checkbox('checkbox_minha_caixa_pessoal');
        $checkbox_minha_caixa_pessoal
                ->setLabel('Encaminhar para minha caixa pessoal');

        $checkbox_apenas_responsaveis = new Zend_Form_Element_Checkbox('checkbox_apenas_responsaveis');
        $checkbox_apenas_responsaveis
                ->setLabel('Apenas responsáveis pela unidade');

        $comboUnidadesAgrupadasPorResponsavel = $service_pessoa->retornaComboUnidadesAgrupadasPorResponsavel();
        $caixasMinhaResponsabilidade = new Zend_Form_Element_Select('caixa_minha_responsabilidade');
        $caixasMinhaResponsabilidade->setLabel('*Caixas de minha responsabilidade:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;')
                ->addMultiOption('', '')
                ->addMultiOptions($comboUnidadesAgrupadasPorResponsavel[$userNs->matricula]);

        $pessoas_da_unidade = new Zend_Form_Element_Select('pessoas_da_unidade');
        $pessoas_da_unidade->setLabel('*Pessoas da unidade:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;');

        $responsaveis_pela_unidade = new Zend_Form_Element_Select('responsaveis_pela_unidade');
        $responsaveis_pela_unidade->setLabel('*Responsáveis pela unidade:')
                ->addFilter('StripTags')
                ->setAttrib('style', 'width: 500px;');

        $salvar = new Zend_Form_Element_Submit('salvar');
        $salvar->setLabel('Salvar');

        $this->addElements(array(
            $radio_tipo_cadastro
            , $docm_cd_lotacao_geradora
            , $docm_id_pessoa_externo
            , $docm_ds_nome_emissor_externo
            , $docm_cd_lotacao_redatora
            , $docm_nr_dcmto_usuario
            , $docm_id_tipo_doc
            , $docm_id_pctt
            , $docm_id_tipo_situacao_doc
            , $docm_ds_palavra_chave
            , $docm_ds_assunto_doc
            , $docm_id_confidencialidade
        ));

        $this->addElements($formParteVista->getElements());

        $this->addElements(array(
            $arquivo_principal
            , $anexos
            , $check_box_autuacao
            , $prdi_ds_texto_autuacao
            , $radio_tipo_encaminhamento
            , $checkbox_minha_caixa_pessoal
            , $checkbox_apenas_responsaveis
            , $caixasMinhaResponsabilidade
            , $pessoas_da_unidade
            , $responsaveis_pela_unidade
            , $salvar
        ));
    }

    public function populate($data)
    {
        $service_pessoa = new Services_Rh_Pessoa();
        $serviceLotacao = new Services_Rh_Lotacao();

        $pessoasLotadas = $service_pessoa->retornaComboPessoasFisicasTrf1AgrupadasPorMinhasUnidades();
        $pessoasResponsaveis = $service_pessoa->retornaComboResponsaveisAgrupadosPorMinhasUnidade();

        if ($data['caixa_minha_responsabilidade'] != '') {
            $this->pessoas_da_unidade
                    ->addMultiOption('', '')
                    ->addMultiOptions($pessoasLotadas[$data['caixa_minha_responsabilidade']]);

            $this->responsaveis_pela_unidade
                    ->addMultiOption('', '')
                    ->addMultiOptions($pessoasResponsaveis[$data['caixa_minha_responsabilidade']]);
        }


        //POPULATE DAS PARTES
        if ($data['subsecao_parte_vista'] != '') {
            $subsecoes = $serviceLotacao->retornaComboSubsecoes($data['secao_parte_vista']);
            $this->subsecao_parte_vista->addMultiOption('', '')
                    ->addMultiOptions($subsecoes);
        }
        if ($data['unidade_administrativa'] != '') {
            $unidades = $serviceLotacao->retornaComboUnidadesPorSubsecao($data['subsecao_parte_vista']);
            $this->unidade_administrativa->addMultiOption('', '')
                    ->addMultiOptions($unidades);
        }
        unset($data['pessoa_fisica_interna']);
        unset($data['pessoa_fisica_externa']);
        unset($data['pessoa_juridica']);
        unset($data['unidade_administrativa']);
        //FIM POPULATE DAS PARTES


        $this->populate_executado = true;
        return parent::populate($data);
    }

    public function isValid($data)
    {
        $this->arquivo_principal->setRequired();

        if (!$this->populate_executado) {
            $this->populate($data);
        }
        $this->radio_tipo_cadastro->setRequired();
        if ($data['radio_tipo_cadastro'] == 'externo') {
            $this->DOCM_ID_PESSOA_EXTERNO->setRequired();
            $this->DOCM_DS_NOME_EMISSOR_EXTERNO->setRequired();
        } else if ($data['radio_tipo_cadastro'] == 'interno') {
            $this->DOCM_CD_LOTACAO_GERADORA->setRequired();
            $this->DOCM_CD_LOTACAO_REDATORA->setRequired();
        } else if ($data['radio_tipo_cadastro'] == 'pessoal') {
            $this->DOCM_ID_PESSOA_EXTERNO->setRequired(false);
            $this->DOCM_DS_NOME_EMISSOR_EXTERNO->setRequired(false);
            $this->DOCM_CD_LOTACAO_GERADORA->setRequired(false);
            $this->DOCM_CD_LOTACAO_REDATORA->setRequired(false);
        }
        $this->DOCM_ID_TIPO_DOC->setRequired();
        $this->DOCM_ID_PCTT->setRequired();
        $this->DOCM_ID_TIPO_SITUACAO_DOC->setRequired();
        $this->DOCM_DS_PALAVRA_CHAVE->setRequired();
        $this->DOCM_DS_ASSUNTO_DOC->setRequired();
        $this->DOCM_ID_CONFIDENCIALIDADE->setRequired();

        if ($data['check_box_autuacao'] == '1') {
            $this->PRDI_DS_TEXTO_AUTUACAO->setRequired();
            if ($data['radio_tipo_encaminhamento'] == 'caixa_rascunho') {
                //não coloquei uma mensagem correta nem um validator correto. Isso so vai ocorrer quando o usuário bloquear o javascript
                //deixei para corrigir depois mas caso queira podem colocar um validator adequado e uma mensagem adequada
                $this->radio_tipo_encaminhamento->addValidator('Alnum')
                        ->getValidator('Alnum')->setMessage("Mensagem de erro");
            }
        }

        /*
         * Se o cadastro for Documento Pessoal, não será necessário informar um
         * destino para encaminhamento, pois este vai para a caixa pessoal do usuário
         */
        if ($data['radio_tipo_cadastro'] == 'pessoal') {
            $this->radio_tipo_encaminhamento->setRequired(false);
            $this->caixa_minha_responsabilidade->setRequired(false);
        } else {
            $this->radio_tipo_encaminhamento->setRequired();
            if ($data['radio_tipo_encaminhamento'] == 'caixa_unidade') {
                $this->caixa_minha_responsabilidade->setRequired();
            } elseif ($data['radio_tipo_encaminhamento'] == 'caixa_pessoal') {
                if ($data['checkbox_minha_caixa_pessoal'] == '1') {
                    //apenas encaminhamento para caixa pessoal mesmo
                } elseif ($data['checkbox_apenas_responsaveis'] == '1') {
                    $this->caixa_minha_responsabilidade->setRequired();
                    $this->responsaveis_pela_unidade->setRequired();
                } else {
                    $this->caixa_minha_responsabilidade->setRequired();
                    $this->pessoas_da_unidade->setRequired();
                }
            } elseif ($data['radio_tipo_encaminhamento'] == 'caixa_rascunho') {
                //apenas encaminha para caixa de rascunho mesmo
            }
        }
        return parent::isValid($data);
    }

}