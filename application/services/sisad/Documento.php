<?php

/**
 * @category	Services
 * @package		Services_Sisad_Documento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre documentos do sisad
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
class Services_Sisad_Documento
{

    /**
     * Armazena dados de sessao do usuário logado
     *
     * @var Zend_Session_Namespace $_userNs
     */
    private $_userNs;

    /**
     * Armazena a quantidade de vinculos
     *
     * @var Trf1_Sisad_Negocio_Documento $_rnDocumento
     */
    private $_rnDocumento;

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Adapter_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct()
    {
        $this->_rnDocumento = new Trf1_Sisad_Negocio_Documento();
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    /**
     * Verifica se o usuario escolhido tem visibilidade do documento escolhido
     * pode ser usado o array de vistas também caso já tenha feito a pesquisa
     * para aumentar a performance da function
     * 
     * @param array $documento
     * @param string $matricula
     * @param array $array_vista
     * @return array array('sigiloso' => 'S' ou 'N', 'tem_vista' => bool)
     * 
     * @author	Leidison Siqueira Barbosa
     */
    public function isVisivel(array $documento, $matricula, array $array_vista = null)
    {
        return $this->_rnDocumento->isVisivel($documento, $matricula, $array_vista);
    }

    /**
     * retorna dados de um ou mais documentos
     * @param mixed $documento
     */
    public function getDocumento($documento)
    {
        if (is_array($documento)) {
            $array = array();
            foreach ($documento as $auxDocumento) {
                $array[] = $auxDocumento['DOCM_ID_DOCUMENTO'];
            }

            return $this->_rnDocumento->getDocumento($array, 'array');
        } else {
            return $this->_rnDocumento->getDocumento($documento);
        }
    }

    public function getHistorico($documento)
    {
        return $this->_rnDocumento->getHistoricoDocumento($documento['DOCM_ID_DOCUMENTO']);
    }

    public function getDocumentoDaMinuta($documento)
    {
        $rn_minuta = new Trf1_Sisad_Negocio_Minutas();
        return $rn_minuta->getDocumentoDaMinuta($documento['DOCM_ID_DOCUMENTO']);
    }

    /**
     * Altera os dados do documento. Altera também as vistas do documento.
     * @param array $data
     * @return array
     */
    public function edit($data)
    {
        $service_parteVistas = new Services_Sisad_ParteVista();
        $mensagens = array();
        try {
            $this->_db->beginTransaction();

            $documento = $this->_rnDocumento->getDocumento($data['DOCM_ID_DOCUMENTO']);

            $this->_rnDocumento->alterar($data['DOCM_ID_DOCUMENTO'], $data, $documento);
            $mensagens = $this->_rnDocumento->auditar('DOCM', Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR, $data, $data);

            if (!$mensagens['validado']) {
                return array('mensagem' => 'Ocorreu um problema com a auditoria do documento. ' . $mensagens['mensagem'], 'validado' => false);
            }

            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $processo = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
                $documento = array_merge($documento, $processo);
            }
            //altera as vistas ou partes do documento
            $mensagens = $service_parteVistas->add($documento, $data, false);
            if (!$mensagens['validado']) {
                return $mensagens;
            }
            if ($data['DOCM_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO) {
                //dessa forma ele faz publico mas mantem as vistas
                //descomentado ele remove as vistas caso vire publico
                /* $mensagensDesativa = $service_parteVistas->desativaVistas($documento, false);
                  if (!$mensagensDesativa['validado']) {
                  throw new Exception($mensagens['mensagem']);
                  } */
            }
            $this->_db->commit();
            return array('mensagem' => 'Documento alterado com sucesso', 'validado' => true);
        } catch (Exception $e) {
            $this->_db->rollBack();
            $mensagens = array('mensagem' => $e->getMessage(), 'validado' => false);
        }
        return $mensagens;
    }

    /**
     * Realiza o parecer em um documento ou conjunto de documentos
     * @param array $documento dados do documento (no minimo DOCM_ID_DOCUMENTO)
     * @param array $dadosParecer dados do parecer
     */
    public function parecer($documento, $dadosParecer)
    {
        $rn_documento = new Trf1_Sisad_Negocio_Documento();
        try {
            //busca o arquivo de parecer
            set_time_limit(1800);
            $anexos = new Zend_File_Transfer_Adapter_Http();
            $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

            //se for um array de documentos
            if (isset($documento[0])) {
                $mensagens = array();
                foreach ($documento as $documento_alvo) {
                    $documento_alvo = $rn_documento->getDocumento($documento_alvo['DOCM_ID_DOCUMENTO']);
                    $retorno = $rn_documento->parecer($documento_alvo, $dadosParecer, $anexos, false);
                    if ($retorno['validado'] == true) {
                        $mensagens[] = $retorno;
                    } else {
                        $mensagens = $retorno;
                        break;
                    }
                }
                return $mensagens;
            } else {
                $documento = $rn_documento->getDocumento($documento['DOCM_ID_DOCUMENTO']);
                return $rn_documento->parecer($documento, $dadosParecer, $anexos);
            }
        } catch (Exception $exc) {
            return array('validado' => false, 'mensagem' => 'Erro no serviço: ' . $exc->getMessage());
        }
    }

    /**
     * Realiza o despacho em um documento ou conjunto de documentos
     * @param array $documento dados do documento (no minimo DOCM_ID_DOCUMENTO)
     * @param array $dadosDespacho dados do parecer
     */
    public function despacho($documento, $dadosDespacho)
    {
        $rn_documento = new Trf1_Sisad_Negocio_Documento();
        try {
            //busca o arquivo de parecer
            set_time_limit(1800);
            $anexos = new Zend_File_Transfer_Adapter_Http();
            $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

            //se for um array de documentos
            if (isset($documento[0])) {
                $mensagens = array();
                foreach ($documento as $documento_alvo) {
                    $documento_alvo = $rn_documento->getDocumento($documento_alvo['DOCM_ID_DOCUMENTO']);
                    $retorno = $rn_documento->despacho($documento_alvo, $dadosDespacho, $anexos, false);
                    if ($retorno['validado'] == true) {
                        $mensagens[] = $retorno;
                    } else {
                        $mensagens = $retorno;
                        break;
                    }
                }
                return $mensagens;
            } else {
                $documento = $rn_documento->getDocumento($documento['DOCM_ID_DOCUMENTO']);
                return $rn_documento->despacho($documento, $dadosDespacho, $anexos);
            }
        } catch (Exception $exc) {
            return array('validado' => false, 'mensagem' => 'Erro no serviço: ' . $exc->getMessage());
        }
    }

    /**
     * Valida se o documento pode receber pareceres
     * retorna tambem o documento alvo do parecer (o array de dados do documento pode vir com keys diferentes)
     * 
     * @tutorial 
     * RESUMO DA FUNÇÃO
     *      - Documento é processo administrativo (152)?
     * 	- SIM
     * 		- POSSUI ALGUMA JUNTADA ATIVA?
     * 			- SIM
     *                          //não precisa validar se tem vinculos ou não
     * 				- É APENSO OU POSSUI APENSOS?
     * 					- SIM
     * 						- ARRAY DE TODOS OS PROCESSOS ENVOLVIDOS NA JUNTADA.
     * 						- PARECER OU DESPACHO NO ARRAY.
     * 						- MENSAGEM INFORMANDO O SUCESSO E OS PROCESSOS E QUE ERAM APENSOS.
     * 					- NÃO 
     * 						- A JUNTADA É DO TIPO ANEXO?
     * 							- SIM
     * 								- ENQUANTO DIFERENTE DE PROCESSO PRINCIPAL FAÇA
     * 									- PROCESSO PAI = PEGA O PROCESSO PAI DO PROCESSO.
     * 									- PROCESSO PAI É IGUAL A NULL
     * 										- SIM
     * 											- É O PROCESSO PRINCIPAL.
     * 											- SAIA DO ENQUANTO.
     * 										- NÃO
     * 											// SUBSTITUI O PROCESSO PELO PROCESSO PAI
     * 											- PROCESSO = PROCESSO PAI
     * 								- É APENSO OU POSSUI APENSOS?
     * 									- SIM
     * 										- ARRAY DE TODOS OS PROCESSOS APENSOS ENVOLVIDOS NA JUNTADA.
     * 										- PARECER OU DESPACHO NO ARRAY.
     * 										- MENSAGEM INFORMANDO O SUCESSO E OS PROCESSOS E QUE ERAM APENSOS.
     * 									- NÃO
     * 										- PARECER OU DESPACHO NO PROCESSO.
     * 										- MENSAGEM INFORMANDO O SUCESSO E O PROCESSO PELO FATO DE SER O PROCESSO PAI (CASO TENHA OCORRIDO A TROCA DO PROCESSO).
     * 							- NÃO
     * 								- AINDA NÃO IMPLEMENTADO.
     * 			- NÃO
     * 				- PARECER OU DESPACHO NO PROCESSO;
     * 				- MENSAGEM INFORMANDO O SUCESSO E CASO HAJA TROCA INFORMAR O MOTIVO E O PROCESSO PAI.
     * 	- NÃO
     * 		- POSSUI ALGUMA JUNTADA ATIVA?
     * 			- SIM
     * 				- A JUNTADA É DO TIPO ANEXO?
     * 					- SIM
     * 						- O DOCUMENTO FOI ESCOLHIDO APARTIR DE OUTRO?
     * 							- SIM
     * 								- BUSCA O DOCUMENTO PAI APARTIR DO DOCUMENTO (LIDO APARTIR DE, POIS PODE SER UMA CÓPIA ANEXADA) PASSADO JUNTO COMO PARAMETRO.
     * 								- O TIPO DO DOCUMENTO PAI É PROCESSO ADMINISTRATIVO?
     * 									- SIM
     * 										- RETORNA PARA A PRIMEIRA VALIDAÇÃO (RESETA A FUNÇÃO PASSANDO POR PARAMETRO O DOCUMENTO PAI).
     * 									- NÃO
     * 										- AINDA NÃO IMPLEMENTADO.
     * 							- NÃO
     * 								- IMPEDE A EXIBIÇÃO DOS FORMULÁRIOS.
     * 								- MENSAGEM: PARA DAR PARECERES OU DESPACHOS É NECESSÁRIO ESCOLHER O DOCUMENTO APARTIR DE OUTRO. POÍS NÃO É POSSÍVEL LOCALIZAR O DOCUMENTO PAI.
     * 					- NÃO
     * 						- AINDA NÃO IMPLEMENTADO
     * 			- NÃO
     * 				- REALIZA O PARECER OU DESPACHO NO DOCUMENTO.
     * 				- RETORNA MENSAGEM.
     * @param array $documento documento principal na função
     * @param array $escolhidoApartirDe deve ter no minimo a key "DOCM_ID_DOCUMENTO". É o documento pelo qual o documento representado pela variavel $documento foi selecionado
     * @param array $trocado informa que o documento foi trocado
     * @return array mensagem de erro, um documento ou um array de documentos junto será retornado uma key "motivo"
     */
    public function validaParecer($documento, $escolhidoApartirDe = null, $trocado = false)
    {
        $rn_juntadaProcessoProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
        $rn_processo = new Trf1_Sisad_Negocio_Processo();
        $rn_documento = new Trf1_Sisad_Negocio_Documento();
        $documentoApartirDe = null;
        if (!is_null($escolhidoApartirDe) && !is_null($escolhidoApartirDe['DOCM_ID_DOCUMENTO'])) {
            //sabe-se que é um processo pois um documento ou processo somente pode estar dentro de outro processo
            $dadosProcApartirDe = $rn_processo->getProcessoPorIdDocumento($escolhidoApartirDe['DOCM_ID_DOCUMENTO']);
            $dadosDocApartirDe = $rn_documento->getDocumento($escolhidoApartirDe['DOCM_ID_DOCUMENTO']);
            $documentoApartirDe = array_merge($dadosProcApartirDe, $dadosDocApartirDe);
        }
        //se for um processo administrativo
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            $juntada = $rn_juntadaProcessoProcesso->hasJuntada($documento);
            //se tiver algum tipo de juntada
            if (!is_null($juntada)) {
                //não precisa validar se tem vinculos ou não
                $processosApensos = $rn_juntadaProcessoProcesso->getProcessosApensadosAtivos($documento);
                if (count($processosApensos) > 0) {
                    //se for do tipo apensação
                    //inclui o documento no array de documentos
                    $processosApensos[] = $documento;
                    $processosApensos['motivo'] = ($trocado ? 'O documento foi trocado pelo seu processo administrativo.' : '') . ' O processo administrativo da leitura possui apensos. Logo a ação será realizada no processo administrativo escolhido e seus apensos';
                    return $processosApensos;
                } else {
                    $isAnexado = $rn_juntadaProcessoProcesso->isAnexado($documento);
                    //se for anexação
                    if (!is_null($isAnexado)) {
                        //se for do tipo anexar
                        $processoPai = false;
                        $qtdVoltas = 0;
                        //processo para pegar o processo pai principal da juntada de anexação
                        while ($processoPai == false) {
                            if ($qtdVoltas > 0) {
                                $isAnexado = $rn_juntadaProcessoProcesso->isAnexado($documento);
                            }
                            //não tem processo pai
                            if ($isAnexado == null) {
                                //logo ele é um processo pai
                                $processoPai = true;
                                //se o documento já não for o documento inicial
                                if ($qtdVoltas > 0) {
                                    //busca os dados do processo principal
                                    $processo = $rn_processo->getDocumentoPorIdProcesso($documento);
                                    $documento = array_merge($documento, $processo);

                                    $documentoDados = $rn_documento->getDocumento($processo['DOCM_ID_DOCUMENTO']);
                                    $documentoDados = array_merge($documentoDados, $processo);
                                    $documento = array_merge($documentoDados, $documento);

                                    /*
                                     * //busca os dados do processo principal
                                      $processo = $rn_processo->getDocumentoPorIdProcesso($documento);

                                      $documento = $rn_documento->getDocumento($processo['DOCM_ID_DOCUMENTO']);
                                      $documento = array_merge($documento, $processo);
                                      Zend_Debug::dump($processo);
                                      exit;
                                     */
                                }
                            } else {
                                $documento = array('PRDI_ID_PROCESSO_DIGITAL' => $isAnexado['VIPD_ID_PROCESSO_DIGITAL_PRINC']);
                            }
                            $qtdVoltas++;
                        }//fim while

                        $processosApensos = $rn_juntadaProcessoProcesso->getProcessosApensadosAtivos($documento);
                        //se tiver apensos
                        if (count($processosApensos) > 0) {
                            //inclui o documento no array de documentos
                            $processosApensos[] = $documento;
                            //se tiver realizado alguma troca de processos
                            if ($qtdVoltas > 1) {
                                $processosApensos['motivo'] = ($trocado ? 'O documento foi trocado pelo seu processo administrativo.' : '') . ' O processo administrativo da leitura está anexado a outro. Logo a ação será executada no processo administrativo principal e seus apensos';
                            } else {
                                $processosApensos['motivo'] = ($trocado ? 'O documento foi trocado pelo seu processo administrativo.' : '') . ' O processo administrativo da leitura possui apensos. Logo a ação será realizada no processo administrativo escolhido e seus apensos';
                            }
                            return $processosApensos;
                        } else {
                            $documento['motivo'] = ($trocado ? 'O documento foi trocado pelo seu processo administrativo.' : '') . ' O processo administrativo da leitura está anexado a outro. Logo a ação será executada no processo administrativo principal';
                            return $documento;
                        }
                    } else {
                        //não está anexado
                        $documento['motivo'] = null;
                        return $documento;
                    }
                }
            } else {
                //não existe nenhuma juntada associada ao processo
                $documento['motivo'] = null;
                return $documento;
            }
        } else {
            //não é processo administrativo
            $rn_juntadaDocumentoProcesso = new Trf1_Sisad_Negocio_JuntadaDocumentoProcesso();
            if ($rn_juntadaDocumentoProcesso->isAnexado($documento)) {
                if (!is_null($documentoApartirDe)) {
                    if (isset($documentoApartirDe["DTPD_ID_TIPO_DOC"]) && isset($documentoApartirDe["DOCM_ID_DOCUMENTO"]) && isset($documentoApartirDe["PRDI_ID_PROCESSO_DIGITAL"])) {
                        return $this->validaParecer($documentoApartirDe, null, true);
                    } else {
                        throw new Exception('É necessário que o documento principal na leitura tenha pelo menos as keys ["DTPD_ID_TIPO_DOC", "DOCM_ID_DOCUMENTO", "PRDI_ID_PROCESSO_DIGITAL"]');
                    }
                } else {
                    return array('validado' => false, 'motivo' => 'Para dar pareceres ou despachos é necessário escolher o documento anexado apartir de seu pai. Poís não é possível localizar o documento pai.');
                }
            } else {
                $documento['motivo'] = null;
                return $documento;
            }
        }
    }

    /**
     * Valida se o documento pode receber despacho
     * usa as mesmas regras da validação do parecer
     * retorna também o documento alvo do despacho
     * @param type $documento
     * @param type $flag
     * @return array
     */
    public function validaDespacho($documento, $flag = false)
    {
        //usa as mesmas regras da validação do parecer
        return $this->validaParecer($documento, $flag);
    }

    /**
     * Realiza o encaminhamento de um documento para a caixa pessoal de algum
     * usuário.
     * @param string $matricula
     * @param array $documento
     * @return array {'status':bolean, 'mensagem':string}
     */
    public function encaminharParaPessoa($documentos, $uploads, $dadosComplementares)
    {

        //Instancia da Session
        $userNs = new Zend_Session_Namespace('userNs');

        //Validar o destino
        if ($dadosComplementares['radio_tipo_encaminhamento'] == 'caixa_pessoal') {
            if ($dadosComplementares['checkbox_minha_caixa_pessoal'] == '1') {
                //Se for para minha caixa pessoal,pegar os dados da session
                $dadosDestino[0] = $userNs->siglasecao;
                $dadosDestino[1] = $userNs->codlotacao;
                $dadosDestino['matricula'] = $userNs->matricula;
            } else {
                $dadosDestino = explode('|', $dadosComplementares['caixa_responsabilidade_usuario']);
                $mat = explode(' - ', $dadosComplementares['pessoa_trf1']);
                $dadosDestino['matricula'] = trim($mat[0]);
            }
        } elseif ($dadosComplementares['radio_tipo_encaminhamento'] == 'pessoa_unidade') {
            if ($dadosComplementares['check_apenas_caixa_minha_responsabilidade'] == '1') {
                $dadosDestino = explode('|', $dadosComplementares['caixa_minha_responsabilidade']);
                $dadosDestino['matricula'] = $dadosComplementares['pessoas_da_unidade'];
            } else {
                $dadosDestino = explode('|', $dadosComplementares['MODE_CD_SECAO_UNID_DESTINO']);
                $dadosDestino['matricula'] = $dadosComplementares['pessoas_da_unidade'];
            }
        }

        //Dados necessários para o encaminhamento
        $destino['SIGLA'] = $dadosDestino[0];
        $destino['CODIGO'] = $dadosDestino[1];
        $destino['MATRICULA'] = $dadosDestino['matricula'];
        $qtdDocumentos = count($documentos['documento']);
        $dadosComplementares['TEXTO_ENCAMINHAMENTO'] = $dadosComplementares['MOFA_DS_COMPLEMENTO'];
        $dadosComplementares['AUTOCOMMIT'] = false;
        $dadosComplementares['TIPO'] = 'PESSOA';
        $dadosComplementares['QTD_DOCUMENTOS'] = $qtdDocumentos;

        try {
            foreach ($documentos['documento'] as $d) {
                $doc = Zend_Json::decode($d);
                $retorno = $this->_rnDocumento->encaminhar($doc, $destino, $uploads, $dadosComplementares);
            }

            /**
             * Limpando a session dos anexos
             */
            $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
            $sessionAnexos->anexos = NULL;
            return $retorno;
        } catch (Zend_Exception $e) {

            /**
             * Limpando a session dos anexos
             */
            $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
            $sessionAnexos->anexos = NULL;

            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao encaminhar documento: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => '');
        }
    }

    /**
     * Realiza o encaminhamento de um documento para a caixa de rascunho de
     * algum usuário.
     * @param string $matricula
     * @param array $documento
     * @return array {'status':bolean, 'mensagem':string}
     */
    public function encaminharParaRascunho($matricula, $documento)
    {

        return array();
    }

    /**
     * Realiza o encaminhamento de um documento para a caixa de entrada de
     * alguma unidade.
     * @param string $caixa
     * @param array $documento
     * @return array {'status':bolean, 'mensagem':string}
     */
    public function encaminharParaUnidade($documentos, $uploads, $dadosComplementares)
    {

        //Instancia da Session
        $userNs = new Zend_Session_Namespace('userNs');

        //Validar o destino
        if ($dadosComplementares['check_apenas_caixa_minha_responsabilidade'] == '1') {
            $dadosDestino = explode('|', $dadosComplementares['caixa_minha_responsabilidade']);
        } else {
            $dadosDestino = explode('|', $dadosComplementares['MODE_CD_SECAO_UNID_DESTINO']);
        }

        //Dados necessários para o encaminhamento
        $destino['SIGLA'] = $dadosDestino[0];
        $destino['CODIGO'] = $dadosDestino[1];
        $qtdDocumentos = count($documentos['documento']);
        $dadosComplementares['TEXTO_ENCAMINHAMENTO'] = $dadosComplementares['MOFA_DS_COMPLEMENTO'];
        $dadosComplementares['AUTOCOMMIT'] = false;
        $dadosComplementares['QTD_DOCUMENTOS'] = $qtdDocumentos;
        
        try {
            foreach ($documentos['documento'] as $d) {
                $doc = Zend_Json::decode($d);
                $retorno = $this->_rnDocumento->encaminhar($doc, $destino, $uploads, $dadosComplementares);   
            }            
            
            /**
             * Limpando a session dos anexos
             */
            $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
            $sessionAnexos->anexos = NULL;
            return $retorno;
        } catch (Zend_Exception $e) {

            /**
             * Limpando a session dos anexos
             */
            $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
            $sessionAnexos->anexos = NULL;

            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao encaminhar documento: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => '');
        }
    }

    /**
     * Função verifica se o usuário informado tem permissão para divulgação em
     * listas internas
     * 
     * @param type $matricula
     * @return boolean
     */
    public function verificaPermissaoLista($matricula)
    {
        /* Verificacao para encaminhar documento da caixa de entrada da unidade para listas internas para diferentes grupos */
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $divulgacao_avisos = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('DIVULGAÇÃO PARA LISTAS INTERNAS (AVISOS)', $matricula);
        $divulgacao_avisos_ESPECIAL = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('LISTAS INTERNAS (AVISOS) ESPECIAL', $matricula);
        if (($divulgacao_avisos) || ($divulgacao_avisos_ESPECIAL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Função trata os dados para fazer a divulgação para listas internas
     * 
     * @param array $documentos
     * @param array $dadosComplementares
     * @return array
     */
    public function divulgarDocumento($documentos, $dadosComplementares)
    {
        try {
            foreach ($documentos['documento'] as $d) {
                $doc = Zend_Json::decode($d);
                $dadosComplementares['AUTOCOMMIT'] = true;
                $retorno = $this->_rnDocumento->divulgarDocumento($dadosComplementares, $doc);
            }
            return $retorno;
        } catch (Zend_Exception $e) {

            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao Divulgar documento: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => '');
        }
    }

    /**
     * Cadastra um documento tanto interno quanto externo no sistema.
     * @param array $documento
     * @param array $upload
     * @param array $dadosComplementares (
     *  'CADASTRO'=>ARRAY('TIPO'=>STRING)
     *  ,'PARTES' => ARRAY('DADOS'=>ARRAY())
     *  ,'ENCAMINHAMENTO'=>ARRAY('TIPO'=>STRING,'PARA_MINHA_CAIXA_PESSOAL'=>BOOLEAN,'MATRICULA_DESTINO'=>STRING,'UNIDADE'=>STRING)
     *  ,'AUTUACAO'=>ARRAY('AUTUAR'=>BOOLEAN,'TEXTO'=>STRING)
     * @return array ARRAY('STATUS'=>BOOLEAN, 'MENSAGEM'=>STRING,'STATUS'=>STRING,'DATA'=>ARRAY())
     */
    public function cadastrar($data, $upload, $dadosComplementares)
    {
        if ($dadosComplementares['CADASTRO']['TIPO'] == 'externo') {
            //cadastra o documento externo. A variavel $data repete pois as partes estão no mesmo parametro
            return $this->_rnDocumento->cadastrarExterno($data, $data, $upload, $dadosComplementares);
        } elseif ($dadosComplementares['CADASTRO']['TIPO'] == 'interno') {
            //cadastra o documento interno. A variavel $data repete pois as partes estão no mesmo parametro
            return $this->_rnDocumento->cadastrarInterno($data, $data, $upload, $dadosComplementares);
        } elseif ($dadosComplementares['CADASTRO']['TIPO'] == 'pessoal') {
            //cadastra o documento pessoal. A variavel $data repete pois as partes estão no mesmo parametro
            return $this->_rnDocumento->cadastrarPessoal($data, $data, $upload, $dadosComplementares);
        } else {
            return array('validado' => false, 'mensagem' => 'Não foi detectado se é um documento interno, externo ou pessoal.');
        }
    }

    /**
     * 
     * Realiza o encaminhamento de um documento
     * 
     * @param type $documentos
     * @param type $upload
     * @param type $dadosComplementares
     * @return array
     */
    public function encaminhar($documentos, $upload, $dadosComplementares)
    {
        if ($this->verificaRestricaoPartesEncaminhamento($documentos, $dadosComplementares)) {
            if ($dadosComplementares['radio_tipo_encaminhamento'] == 'caixa_unidade') {
                return $this->encaminharParaUnidade($documentos, $upload, $dadosComplementares);
            } elseif ($dadosComplementares['radio_tipo_encaminhamento'] == 'caixa_pessoal') {
                return $this->encaminharParaPessoa($documentos, $upload, $dadosComplementares);
            } elseif ($dadosComplementares['radio_tipo_encaminhamento'] == 'pessoa_unidade') {
                return $this->encaminharParaPessoa($documentos, $upload, $dadosComplementares);
            } elseif ($dadosComplementares['radio_tipo_encaminhamento'] == 'listas_internas') {
                return $this->divulgarDocumento($documentos, $dadosComplementares);
            } else {
                return array('validado' => false, 'mensagem' => 'Não foi detectado o tipo de encaminhamento.', 'status' => 'error');
            }
        } else {
            return array('validado' => false, 'mensagem' => 'Não foi possível encaminhar. Verifique se algum documento selecionado é restrito as partes e se o destinatário escolhido é parte interessada do mesmo.', 'status' => 'error');
        }
    }

    /**
     * Função verifica se o documento ou processo é restrito as partes. Se for,
     * verificar se o destino faz parte do documento. Se não foi parte, não deixar
     * encaminhar.
     * 
     * @param array $documentos
     * @param array $destinatios
     * @return boolean
     */
    public function verificaRestricaoPartesEncaminhamento($documentos, $destinatios)
    {
        //Instancias
        $retorno = true;

        //Busca as partes
        $partesDocumento = $this->getPartesDocumento($documentos);

        foreach ($documentos['documento'] as $doc) {

            //Instancias
            $arrayPartesUnidade = array();
            $arrayPartesPessoa = array();
            $arrayPartesPessoaJuridica = array();
            $documento = Zend_Json::decode($doc);

            //Se o documento for restrito as partes, fazer a validação
            if ($documento['DOCM_ID_CONFIDENCIALIDADE'] == 1) {

                //Monta os arrays com as partes de cada documento
                foreach ($partesDocumento[$documento['DOCM_ID_DOCUMENTO']] as $parte) {
                    if ($parte['TIPO'] == "partes_unidade[]") {
                        $arrayPartesUnidade[] = $parte['VALUE'];
                    }
                    if ($parte['TIPO'] == "partes_pess_jur[]") {
                        $arrayPartesPessoaJuridica[] = $parte['VALUE'];
                    }
                    if ($parte['TIPO'] == "partes_pessoa_trf[]") {
                        $arrayPartesPessoa[] = $this->trataValorPessoa($parte['VALUE'], '-');
                    }
                }

                //Faz a validação das partes de cada documento
                if ($destinatios['radio_tipo_encaminhamento'] == "caixa_unidade") {
                    if ($destinatios['MODE_CD_SECAO_UNID_DESTINO'] != "") {
                        $unidade = $this->trataValorUnidade($destinatios['MODE_CD_SECAO_UNID_DESTINO'], '-');
                        if (!in_array($unidade, $arrayPartesUnidade)) {
                            $retorno = false;
                        }
                    } elseif ($destinatios['caixa_minha_responsabilidade'] != "") {
                        $unidade = $this->trataValorUnidade($destinatios['caixa_minha_responsabilidade'], '-');
                        if (!in_array($unidade, $arrayPartesUnidade)) {
                            $retorno = false;
                        }
                    } elseif ($destinatios['caixa_responsabilidade_usuario'] != "") {
                        $unidade = $this->trataValorUnidade($destinatios['caixa_responsabilidade_usuario'], '-');
                        if (!in_array($unidade, $arrayPartesUnidade)) {
                            $retorno = false;
                        }
                    }
                } elseif ($destinatios['radio_tipo_encaminhamento'] == "caixa_pessoal" || $destinatios['radio_tipo_encaminhamento'] == "pessoa_unidade") {
                    //Se for para uma pessoa, tratar aqui
                    if ($destinatios['pessoa_trf1'] != "") {
                        $matricula = $this->trataValorPessoa($destinatios['pessoa_trf1'], ' - ');
                        if (!in_array($matricula, $arrayPartesPessoa)) {
                            $retorno = false;
                        }
                    } elseif ($destinatios['pessoas_da_unidade'] != "") {
                        $matricula = trim($destinatios['pessoas_da_unidade']);
                        if (!in_array($matricula, $arrayPartesPessoa)) {
                            $retorno = false;
                        }
                    }
                }
            }
        }
        return $retorno;
    }

    /**
     * Função monta uma String do valor de uma Unidade passando um separador ou não
     * Ex: TR|1139 ou TR-1139 
     * 
     * @param String $valor
     * @param String $separador
     * @return String
     */
    public function trataValorUnidade($valor, $separador = null)
    {

        $dados = explode('|', $valor);
        $sg = $dados[0];
        $cd = $dados[1];
        if ($separador != "") {
            $unidade = $sg . $separador . $cd;
        } else {
            $unidade = $valor;
        }

        return $unidade;
    }

    /**
     * Trata a matricula junto com id do usuário e retorna somente a matricula do mesmo
     * Ex: TR18484PS-6117 = TR18484PS
     * 
     * @param String $valor
     * @param String $delimitador
     * @return String
     */
    public function trataValorPessoa($valor, $delimitador)
    {
        $dados = explode($delimitador, $valor);
        $matricula = $dados[0];
        return $matricula;
    }

    /**
     * Busca as partes interessadas de N Documentos 
     * 
     * @param array $documentos
     * @return array
     */
    public function getPartesDocumento($documentos)
    {
        //Instancias
        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $partesDocumento = array();

        foreach ($documentos['documento'] as $doc) {
            $documento = Zend_Json::decode($doc);
            if ($documento["DTPD_NO_TIPO"] == "Processo administrativo") {
                $DocumentosProcesso = $SadTbPrdiProcessoDigital->getdocsProcesso($documento["DOCM_ID_DOCUMENTO"]);
                $partesDocumento[$documento['DOCM_ID_DOCUMENTO']] = $SadTbPapdParteProcDoc->getPartesVistas(null, $DocumentosProcesso[0]["ID_PROCESSO"], 1); //1 = Parte/
            } else {
                $partesDocumento[$documento['DOCM_ID_DOCUMENTO']] = $SadTbPapdParteProcDoc->getPartesVistas($documento["DOCM_ID_DOCUMENTO"], null, 1); //1 = Parte
            }
        }
        return $partesDocumento;
    }

    /**
     * Retorna a arvore dos anexos e apensos de um processo administrativo.
     * O primeiro processo é o processo passado por parametro.
     * @param array $processo
     * @return array
     */
    public function getArvoreJuntadaProcesso($processo)
    {
        return $this->_rnDocumento->getArvoreDeJuntadaProcesso($processo);
    }

    /**
     * trata os documentos enviados via post para exibir na tela de assinatura.
     * Caso seja um processo administrativo será retornada a árvore de juntada.
     * @param array
     */
    public function trataDocumentosParaAssinatura($documentoPost)
    {
        $rn_documento = new Trf1_Sisad_Negocio_Documento();
        $qtdTotal = count($documentoPost);
        $idDocumentos = array();
        $processo = null;

        //valida os documentos
        foreach ($documentoPost as $json) {
            $documento = Zend_Json::decode($json);
            if ($documento['DTPD_ID_TIPO_DOC'] ==
                    Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
                if ($qtdTotal > 1) {
                    return array(
                        'sucesso' => false,
                        'message' => 'Selecione apenas um processo 
                            administrativo ou uma lista de documentos.',
                        'status' => 'notice'
                    );
                }
                $processo = $documento;
            } else {
                $idDocumentos[] = $documento['DOCM_ID_DOCUMENTO'];
            }
        }
        //se for um processo administrativo
        if (!is_null($processo)) {
            $arvore = $this->getArvoreJuntadaProcesso($processo);
            return array(
                'sucesso' => true,
                'dados' => $arvore,
                'arvore_processo' => true);
        } else if (count($idDocumentos) > 0) {

            //usar $idDocumentos para buscar os dados dos documentos
            $documentos = $rn_documento->getDocumento($idDocumentos, 'array');
            return array(
                'sucesso' => true,
                'dados' => $documentos,
                'arvore_processo' => false);
        } else {
            return array(
                'sucesso' => false,
                'message' => 'Não é possível assinar nenhum dos documentos 
                    selecionados.',
                'status' => 'notice'
            );
        }
    }

    /**
     * Guarda os dados da assinatura de um documento
     * @param array $documento
     * @return array

      /**
     * 
     * @param string $assinatura
     * @param array $certificado
     * @param array $documento
     * @return array
     */
    public function armazenarAssinaturaDigital($assinatura, $certificado, $documento)
    {
        $pessoa = array('PMAT_CD_MATRICULA' => 'TR18482PS');
        $retorno = $this->_rnDocumento->validaAssinaturaDigital($assinatura, $certificado, $pessoa, $documento);
        if ($retorno['sucesso']) {
            $retorno = $this->_rnDocumento->armazenarAssinaturaDigital($assinatura, $certificado, $documento, $pessoa);

            if ($retorno['sucesso'] == false) {
                $mensagem = array(
                    'sucesso' => $retorno['sucesso']
                    , 'mensagem' => 'A assinatura digital foi cancelada. Motivo:' .
                    $retorno['mensagem']);
            } else {
                $mensagem = array(
                    'sucesso' => $retorno['sucesso']
                    , 'mensagem' => 'Assinatura digital realizada com sucesso.'
                );
            }
        } else {
            $mensagem = array(
                'sucesso' => $retorno['sucesso']
                , 'mensagem' => 'A assinatura digital foi cancelada. Motivo:' .
                $retorno['mensagem']);
        }

        return $mensagem;
    }

    /**
     * Guarda os dados de assinatura de vários documentos
     * @param array $assinaturas
     * @param array $certificado
     * @return array
     */
    public function armazenarAssinaturasDigitais($jsonAssinaturas, $jsonCertificado)
    {
        $certificado = Zend_Json::decode($jsonCertificado);
        foreach ($jsonAssinaturas as $jsonAssinatura) {
            $assinatura = Zend_Json::decode($jsonAssinatura);
            $retorno = $this->armazenarAssinaturaDigital(
                    $assinatura['ASSINATURA']
                    , $assinatura['CHAVE_PUBLICA']
                    , $certificado
                    , $assinatura['DOCUMENTO']
            );

            if ($retorno['sucesso'] == false) {
                return $retorno;
            }
        }
        return array(
            'sucesso' => true
            , 'mensagem' => 'Assinatura digital realizada com sucesso.'
        );
    }

    public function getAssinaturasDigitais($documento)
    {
        
    }

}
