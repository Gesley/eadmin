<?php

/**
 * @category	Services
 * @package		Services_Sisad_Juntada
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço sobre juntada de documentos
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
class Services_Sisad_Juntada {

    /**
     * Armazena mensagens de erro
     *
     * @var array $_erro
     */
    private $_erro = array();

    /**
     * Armazena mensagens de sucesso
     *
     * @var array $_sucesso
     */
    private $_sucesso = array();

    /**
     * Armazena a quantidade de vinculos
     *
     * @var Trf1_Sisad_Negocio_Juntada $_rnJuntada
     */
    private $_rnJuntada;

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    private $_db;

    /**
     * Armazena dados de sessao do usuário logado
     *
     * @var Zend_Session_Namespace $_userNs
     */
    private $_userNs;

    /**
     * Armazena dados de sessao do usuário logado
     *
     * @var string $_dateTime
     */
    private $_dateTime;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    /**
     * Valida se os documentos/processos filhos estão aptos para o esquema de juntada
     * retorna um array com os filhos já validados e as mensagens geradas por inconsistências
     * resultando assim em um filtro dos documentos/processos filhos
     * @param array $documentos
     * @param string $tipoRelacao
     * @return array array('documentos' => array(), 'mensagens' => string)
     */
    public function filtraFilhos(array $documentos, $tipoRelacao) {
        $rn_documento = new Trf1_Sisad_Negocio_Documento();
        if ($tipoRelacao == 'documentoaprocesso') {
            $rn_JuntadaDocumentoProcesso = new Trf1_Sisad_Negocio_JuntadaDocumentoProcesso();
            $documentosFiltrados = array();
            $mensagens = array();
            foreach ($documentos as $json) {
                $documentoPost = Zend_Json::decode($json);
                $documento = $rn_documento->getDocumento($documentoPost['DOCM_ID_DOCUMENTO']);
                $validacao = $rn_JuntadaDocumentoProcesso->validaDocumento($documento);
                if ($validacao['validado']) {
                    $documento['STATUS_ANEXO'] = $validacao['STATUS_ANEXO'];
                    $documentosFiltrados[] = $documento;
                } else {
                    $mensagens[] = $validacao['mensagem'];
                }
            }
        } elseif ($tipoRelacao == 'processoaprocesso') {
            $rn_JuntadaProcessoProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
            $documentosFiltrados = array();
            $mensagens = array();
            foreach ($documentos as $json) {
                $documentoPost = Zend_Json::decode($json);
                $documento = $rn_documento->getDocumento($documentoPost['DOCM_ID_DOCUMENTO']);
                $validacao = $rn_JuntadaProcessoProcesso->validaProcessoFilho($documento);

                if ($validacao['validado']) {
                    $documento['STATUS_ANEXO'] = $validacao['STATUS_ANEXO'];
                    $documento['STATUS_VINCULO'] = $validacao['STATUS_VINCULO'];
                    $documentosFiltrados[] = $documento;
                } else {
                    $mensagens[] = $validacao['mensagem'];
                }
            }
        } elseif ($tipoRelacao == 'documentoadocumento') {
            
        }
        return array('documentos' => $documentosFiltrados, 'mensagens' => $mensagens);
    }

    /**
     * Valida se os documentos/processos principais estão aptos para o esquema de juntada
     * @param array $documentos
     * @param string $tipoRelacao
     * @param array $documentosExcluidos
     * @return mixed |array ou string|
     */
    public function filtraPrincipais(array $documentos, $tipoRelacao, array $documentosExcluidos = null) {
        $documentosFiltrados = array();
        $mensagens = array();
        $juntada = array();
        $processosComApensos = array();

        if ($tipoRelacao == 'documentoaprocesso') {
            //Se for uma juntada de documentos em processos
            $rn_JuntadaDocumentoProcesso = new Trf1_Sisad_Negocio_JuntadaDocumentoProcesso();
            foreach ($documentos as $documento) {
                $validacao = $rn_JuntadaDocumentoProcesso->validaProcesso($documento);
                if ($validacao['validado']) {
                    //caso algum processo administrativo tiver informado 
                    //que o processo dessa volta do foreach é seu processo principal, setar como true a flag
                    //caso nenhum processo tiver informado q ele é pai setar como false a flag.
                    //
                    //OBS: não tem problema setar como false um processo administrativo
                    //que realmente é principal, pois a flag será trocada quando for detectado um processo apenso (especificamente no else do if)
                    $documento['FLAG_HAS_APENSOS'] = (isset($processosComApensos[$documento['PRDI_ID_PROCESSO_DIGITAL']]) ? true : false);
                    $documentosFiltrados[$documento['PRDI_ID_PROCESSO_DIGITAL']] = $documento;
                } else {

                    //Caso o processo administrativo principal ja tiver passado pelo filtro ($documentosFiltrados), alterar a flag para dizer que ele possui um apenso pelo menos
                    if (isset($documentosFiltrados[$validacao['PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL']])) {
                        $documentosFiltrados[$validacao['PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL']]['FLAG_HAS_APENSOS'] = true;
                    }

                    //alimenta um array cujo objetivo é informar processos administrativos principais (possuem apensos).
                    //o index do array é o id do processo administrativo
                    //quando o valor vier como 0 significa que o processo não foi validado por algum outro motivo
                    //mas mesmo assim a posição 0 recebe um array.
                    //A posição 0 é a posição de escape
                    $processosComApensos[$validacao['PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL']] = array();

                    $mensagens[] = $validacao['mensagem'];
                }
            }
        } elseif ($tipoRelacao == 'processoaprocesso') {
            //Se for uma juntada entre processos
            $rn_JuntadaProcessoProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
            foreach ($documentos as $documento) {
                $validacao = $rn_JuntadaProcessoProcesso->validaProcessoPai($documento);

                if ($validacao['validado']) {
                    $documentosFiltrados[] = $documento;
                } elseif (isset($validacao['motivo']) && $validacao['motivo'] == 'apensado') {
                    //Se for um processo apensado a outro
                    //array na posição id processo pai na posição +1 igual aos dados do documento
                    $juntada[$validacao['dados']['VIPD_ID_PROCESSO_DIGITAL_PRINC']][] = $documento;
                } else {
                    //Se ele não for apto para juntada
                    $mensagens[] = $validacao['mensagem'];
                }
            }
        } elseif ($tipoRelacao == 'documentoadocumento') {
            //Se for uma juntada em documentos
        }
        return array('documentos' => $documentosFiltrados, 'mensagens' => $mensagens, 'juntada' => $juntada);
    }

    /**
     * Junta vários a vários
     *
     * Validação gasta em média 30% do tempo de execução
     * 
     * @param	array   $dataRequest
     * @param	string  $tipoRelacao
     * @return	array   array('erros' => '', 'sucessos' => '')
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function juntarVarios(array $dataRequest, $tipoRelacao) {

        if ($tipoRelacao == 'documentoaprocesso') {
            $rn_juntadaDocumentoProcesso = new Trf1_Sisad_Negocio_JuntadaDocumentoProcesso();
            return $rn_juntadaDocumentoProcesso->juntarVarios($dataRequest, $tipoRelacao);
        } elseif ($tipoRelacao == 'processoaprocesso') {
            $rn_juntadaProcessos = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
            $dataRequest['documentoPrincipal'] = array(0 => $dataRequest['documentoPrincipal']);
            return $rn_juntadaProcessos->juntarVarios($dataRequest, $tipoRelacao);
        }
    }

    public function desativar($processoPai, $processoFilho, $tipoJuntada, $tipoRelacao) {
        if ($tipoRelacao == 'documentoaprocesso') {
            throw new Exception('ainda não construido', 1, null);
        } elseif ($tipoRelacao == 'processoaprocesso') {
            $rn_juntadaProcessos = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
            if ($tipoJuntada == Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR) {
                //busca os dados da juntada
                $juntada = $rn_juntadaProcessos->getJuntada(array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho), $tipoJuntada);
                if (is_null($juntada)) {
                    $juntada = $rn_juntadaProcessos->getJuntada(array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho), array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), $tipoJuntada);
                    //então o pai não é o processoPai e sim o processoFilho
                    //as vezes o processo pai pode ser o filho no detalhe documento pois um processo esta apensado um no outro
                    //logo não existe um pai de fato. Porém no banco de dados é necessário existir
                    return $rn_juntadaProcessos->desativaApenso(array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho), array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai));
                } else {
                    return $rn_juntadaProcessos->desativaApenso(array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho));
                }
            } elseif ($tipoJuntada == Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR) {
                return $rn_juntadaProcessos->desativaAnexo(array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho));
            } elseif ($tipoJuntada == Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR) {
                //busca os dados da juntada
                $juntada = $rn_juntadaProcessos->getJuntada(array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho), $tipoJuntada);
                if (is_null($juntada)) {
                    $juntada = $rn_juntadaProcessos->getJuntada(array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho), array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), $tipoJuntada);
                    //então o pai não é o processoPai e sim o processoFilho
                    //as vezes o processo pai pode ser o filho no detalhe documento pois um processo esta apensado um no outro
                    //logo não existe um pai de fato. Porém no banco de dados é necessário existir
                    return $rn_juntadaProcessos->desativaVinculo(array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho), array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai));
                } else {
                    return $rn_juntadaProcessos->desativaVinculo(array('PRDI_ID_PROCESSO_DIGITAL' => $processoPai), array('PRDI_ID_PROCESSO_DIGITAL' => $processoFilho));
                }
            }
        }
    }

    public function getVinculos($processo) {
        $rn_juntadaProcessos = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
        return $rn_juntadaProcessos->getProcessosVinculadosAtivos($processo);
    }

    public function getApensos($processo) {
        $rn_juntadaProcessos = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
        return $rn_juntadaProcessos->getProcessosApensadosAtivos($processo);
    }

    public function completaComApensados($documentos, $formato = 'json', $buscaDadosDocumentoPor = 'documento') {
        if ($formato == 'json') {
            $service_processo = new Services_Sisad_Processo();
            $service_documento = new Services_Sisad_Documento();
            $dbTableMovi = new Application_Model_DbTable_SadTbMoviMovimentacao();
            $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
            $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
            $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
            $aNamespace = new Zend_Session_Namespace('userNs');
            $idDocumentosTeste = array();
            $novoArrayDocumentos = array();
            foreach ($documentos as $value) {
                $documentoParaTeste = Zend_Json::decode($value);
                $idDocumento = $documentoParaTeste['DOCM_ID_DOCUMENTO'];
                //se não tiver armazenado o documento
                if (!isset($idDocumentosTeste[$idDocumento])) {
                    //armazena o id do documento como posição do array de ids de documento
                    //para verificar se o documento já foi inserido
                    $idDocumentosTeste[$idDocumento] = 'existe';

                    if ($documentoParaTeste['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
                        $processoParaTeste = $service_processo->getProcessoPorIdDocumento($documentoParaTeste);
                        $apensados = $this->getApensos($processoParaTeste);
                        //se tiver apensados
                        if (count($apensados) > 0) {
                            foreach ($apensados as $apenso) {
                                $idDocumento = $apenso['DOCM_ID_DOCUMENTO'];
                                //se ainda não tiver armazenado
                                if (!isset($idDocumentosTeste[$idDocumento])) {
                                    //busca todos os dados do processo apensado
                                    if ($buscaDadosDocumentoPor == 'documento') {
                                        $documentoApensado = $service_documento->getDocumento($apenso['DOCM_ID_DOCUMENTO']);
                                        $documentoApensado['CAIXA_REQUISICAO'] = $documentoParaTeste['CAIXA_REQUISICAO'];
                                    } elseif ($buscaDadosDocumentoPor == 'encaminhados_caixa_pessoal') {
                                        $documentoApensado = $dbTableMovi->getDocumentoCaixaPessoalEncaminhados($apenso, $aNamespace->siglasecao, $aNamespace->codlotacao, $aNamespace->matricula);
                                    } elseif ($buscaDadosDocumentoPor == 'encaminhados_caixa_unidade') {
                                        $documentoApensado = $dbTableMovi->getDocumentoCaixaUnidadeEncaminhados($apenso, $codlotacao, $siglasecao);
                                    } elseif ($buscaDadosDocumentoPor == 'recebidos_caixa_pessoal') {
                                        $documentoApensado = $dbTableMovi->getCaixaPessoalRecebidos($aNamespace->matricula, $codlotacao, $siglasecao, null, ' AND DOCM.DOCM_ID_DOCUMENTO = ' . $apenso['DOCM_ID_DOCUMENTO']);
                                        $documentoApensado[0]['CAIXA_REQUISICAO'] = $documentoParaTeste['CAIXA_REQUISICAO'];
                                        $documentoApensado = $documentoApensado[0];
                                    } elseif ($buscaDadosDocumentoPor == 'arquivados_pessoal') {
                                        $documentoApensado = $dbTableMovi->getArquivadosPessoal($aNamespace->matricula, $aNamespace->codlotacao, $aNamespace->siglasecao, null, ' AND DOCM.DOCM_ID_DOCUMENTO = ' . $apenso['DOCM_ID_DOCUMENTO']);
                                        $documentoApensado = $documentoApensado[0];
                                    }
                                    if ($documentoApensado != false) {
                                        $TimeInterval = new App_TimeInterval();
                                        $documentoApensado['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($documentoApensado['MOVI_DH_ENCAMINHAMENTO_CHAR']);
                                        $documentoParaTeste['apensados'][$documentoApensado['DOCM_ID_DOCUMENTO']] = $documentoApensado;
                                        $documentoApensado['apensados'][$documentoParaTeste['DOCM_ID_DOCUMENTO']] = $documentoParaTeste;
                                        $novoArrayDocumentos[] = Zend_Json::encode($documentoApensado);
                                        $idDocumentosTeste[$idDocumento] = 'existe';
                                    }
                                }
                            }
                        }
                    }
                    //armazena o documento
                    $auxJson = Zend_Json::encode($documentoParaTeste);
                    $novoArrayDocumentos[] = $auxJson;
                }
            }
            return $novoArrayDocumentos;
        } else {
            Zend_Debug::dump('nao implementado para não jsons');
            exit;
            return $documentos;
        }
    }

}