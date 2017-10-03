<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_JuntadaProcessoProcesso
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre qualquer tipo de juntada entre processos e processos
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
class Trf1_Sisad_Negocio_JuntadaProcessoProcesso {

    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;

    /**
     *
     * @var Zend_Session_Namespace 
     */
    private $_userNs;

    /**
     *
     * @var String 
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

        $zend_date = new Zend_Date(null, 'dd/MM/YY HH:mm:ss');
        $this->_dateTime = $zend_date->get(Zend_Date::DATETIME);
    }

    /**
     * 
     * - Pega os dados do documento pelo id
     * - Documento é processo administrativo (152)?
     *      - SIM
     * 		- O processo administrativo está na caixa atual da sessão do usuário logado?
     * 			- SIM
     * 				- Processo administrativo é público OU usuário logado possui vistas ao processo administrativo?
     * 					- SIM
     * 						- O documento já está (anexado ou apensado à um processo) ou finalizado?
     * 							- SIM
     * 								- Mensagem = 'Processo $NUMERO: o processo administrativo já está $TIPO_JUNTADA ao processo administrativo $NUMERO.'
     * 								- Remove o documento da listagem.
     * 							- NÃO
     *                                                          - A juntada é tipo tipo anexo e o processo possui vinculos?
     *                                                              - SIM
     *                                                                  - Mensagem = 'Processo administrativo $NUMERO: Não é possível anexar este processo enquanto estiver vinculado a outro. Para isso desvincule o processo administrativo. Depois, se necessário, vincule o processo administrativo principal.'
     *                                                                  - Remove o documento da listagem.
     * 								- Permite o processo administrativo.
     * 					- NÃO
     * 						- Mensagem = 'Processo administrativo $NUMERO: Processo administrativo não visível ao usuário.'
     * 						- Remove o documento da listagem.
     * 			- NÃO
     * 				- Mensagem = 'Processo $NUMERO: Solicite o encaminhamento do processo administrativo. Ele encontra-se na caixa $FAMILIA_CAIXA.'
     * 				- Remove o documento da listagem.
     *      - NÃO
     * 		- Mensagem = 'Documento $NUMERO: O documento não é um processo administrativo.'
     * 		- Remove o documento da listagem.
     * @param array $documento
     * @param int $tipoJuntada
     */
    public function validaProcessoFilho($documento, $tipoJuntada = null) {
        $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        //Se documento é processos administrativos
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //plugin para buscar a unidade atual na sessao
            $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
            //Se a caixa atual da sessão do usuário logado é a mesma que a caixa do documento
            if ($documento['MODE_SG_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade() && $documento['MODE_CD_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade()) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $processo = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
                $processo = array_merge($processo, $documento);
                $visivel = $rn_ParteVistas->statusSigiloVista($processo, $this->_userNs->matricula);
                $documento['CONF_ID_CONFIDENCIALIDADE'] = (isset($documento['DOCM_ID_CONFIDENCIALIDADE']) ? $documento['DOCM_ID_CONFIDENCIALIDADE'] : $documento['CONF_ID_CONFIDENCIALIDADE']);
                //Se processo é público OU processo pode ser visto pelo usuário

                if ($documento['CONF_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO || $visivel['tem_vista'] || $visivel['sigiloso'] == 'N') {
                    //Verifica se o processo administrativo pode participar do esquema de juntada
                    $isAnexado = $this->isAnexado($processo);
                    $isApensadoOuApensos = $this->isApensadoOuApensos($processo);
                    //Se o documento está com movimentação individual finalizada
                    if ($documento['DOCM_IC_MOVI_INDIVIDUAL'] == 'N') {
                        return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': O processo administrativo não está mais em tramitação.');
                    } elseif (!is_null($isAnexado)) {
                        return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': O processo administrativo já está anexado em outro processo administrativo.');
                    } elseif ($isApensadoOuApensos) {
                        return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': O processo está envolvido em uma juntada do tipo apensação. Logo não poderá ser juntado a outro processo na condição de não principal. ');
                    } else {
                        $hasVinculos = $this->isVinculos($processo);
                        if ($tipoJuntada == Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR) {
                            //se tem vinculos com outro processo
                            if ($hasVinculos) {
                                return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Não é possível anexar este processo enquanto estiver vinculado a outro. Para isso desvincule o processo administrativo. Depois, se necessário, vincule o processo administrativo principal.');
                            }
                        }
                        return array('validado' => true, 'STATUS_ANEXO' => 'original', 'STATUS_VINCULO' => $hasVinculos);
                    }
                } else {
                    return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Processo administrativo não visível ao usuário.');
                }
            } else {
                return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Solicite o encaminhamento do processo administrativo. Ele encontra-se na caixa ' . $documento['FAMILIA_DESTINO'] . '.');
            }
        } else {
            return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': O documento não é um processo administrativo.');
        }
    }

    /**
     * Verifica se processo administrativo é valido para esquema de juntada
     * 
     * @param array $documento
     * @return array
     */
    public function validaProcessoPai($documento, $tipoJuntada = null) {
        $rn_ParteVista = new Trf1_Sisad_Negocio_ParteVistas();
        $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        //se documento é processo administrativo
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //Se o processo possui movimentação individual
            if ($documento['DOCM_IC_MOVI_INDIVIDUAL'] == 'S') {
                //verifica se o processo está na caixa atual na sessão do usuário logado
                if ($documento['MODE_SG_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade() && $documento['MODE_CD_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade()) {
                    $rn_processo = new Trf1_Sisad_Negocio_Processo();
                    $processo = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
                    $processo = array_merge($processo, $documento);
                    $visivel = $rn_ParteVista->statusSigiloVista($processo, $this->_userNs->matricula);
                    //trata a coluna de confidencialidade caso venha como DOCM_ID_CONFIDENCIALIDADE passa para CONF_ID_CONFIDENCIALIDADE
                    //e se vier CONF_ID_CONFIDENCIALIDADE continua sendo ela mesmo
                    $documento['CONF_ID_CONFIDENCIALIDADE'] = (isset($documento['DOCM_ID_CONFIDENCIALIDADE']) ? $documento['DOCM_ID_CONFIDENCIALIDADE'] : $documento['CONF_ID_CONFIDENCIALIDADE']);
                    //Se processo é público OU processo pode ser visto pelo usuário
                    if ($documento['CONF_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO || $visivel['tem_vista'] || $visivel['sigiloso'] == 'N') {
                        $isApensado = ($tipoJuntada != Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR ? $this->isApensado($processo) : null);

                        //Se o processo estiver apensado em outro processo administrativo
                        if (!is_null($isApensado)) {
                            return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': O processo está apensado em outro processo.', 'motivo' => 'apensado', 'dados' => $isApensado);
                        } else {
                            return array('validado' => true);
                        }
                    } else {
                        return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Processo administrativo não visível ao usuário.');
                    }
                } else {
                    //não está na unidade atual na sessão do usuário logado
                    return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Solicite o encaminhamento do processo administrativo. Ele encontra-se na caixa ' . $documento['FAMILIA_DESTINO'] . '.');
                }
            } else {
                //processos não mais em tramitação
                return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': O processo administrativo não está mais em tramitação.');
            }
        } else {
            //não é um processo administrativo
            return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': O documento não é um processo administrativo.');
        }
    }

    /**
     * Junta DOCUMENTO a PROCESSO
     *
     * @param	array	$processoPai	
     * @param	array	$array $processoFilho
     * @param	array	$arrayVinculo	
     * @param	string	$dataJuntada	
     * @param	string	$matricula	
     * @param	array	$fase	
     * @param	boolean	$autoCommit	
     * @return	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function juntar(array $processoPai
    , array $processoFilho
    , array $arrayVinculo
    , $dataJuntada
    , $matricula
    , $fase
    , $autoCommit = true) {
        
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        try {
            if ($autoCommit) {
                $this->_db->beginTransaction();
            }

            $trf1_Sisad_Negocio_Processo = new Trf1_Sisad_Negocio_Processo();
            $dadosProcessoPai = $trf1_Sisad_Negocio_Processo->getProcessoPorIdDocumento($processoPai['DOCM_ID_DOCUMENTO']);
            $dadosProcessoFilho = $trf1_Sisad_Negocio_Processo->getProcessoPorIdDocumento($processoFilho['DOCM_ID_DOCUMENTO']);

            $dadosJuntada = array(
                'VIPD_ID_PROCESSO_DIGITAL_PRINC' => $dadosProcessoPai['PRDI_ID_PROCESSO_DIGITAL']
                , 'VIPD_ID_PROCESSO_DIGITAL_VINDO' => $dadosProcessoFilho['PRDI_ID_PROCESSO_DIGITAL']
                , 'VIPD_ID_TP_VINCULACAO' => $arrayVinculo['id']
                , 'VIPD_DH_VINCULACAO' => new Zend_Db_Expr("TO_DATE('$dataJuntada','dd/mm/YY HH24:MI:SS')")
                , 'VIPD_CD_MATR_VINCULACAO' => $matricula
                , 'VIPD_NR_VOL_PRINCIPAL' => null
                , 'VIPD_NR_FOLHA_PRINCIPAL' => null
                , 'VIPD_IC_ATIVO' => 'S'
                , 'VIPD_IC_ORIGINAL' => 'S');

            if ($arrayVinculo['id'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR) {
                //verifica se o processo possui apensos ou é apensado em outro
                if ($this->isApensadoOuApensos($processoPai)) {

                    $validacao['validado'] = false;
                    $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}): O processo administrativo possui apensos, para anexar é necessário desapensar os processos. Pois um processo administrativo não poderá estar anexado em mais de um processo administrativo.";
                    return $validacao;
                } else {
                    //passa as vistas do processo para os documentos
                    Trf1_Sisad_Negocio_Documento::alterar($processoFilho['DOCM_ID_DOCUMENTO'], array('DOCM_ID_DOCUMENTO_PAI' => $processoPai['DOCM_ID_DOCUMENTO'], 'DOCM_IC_MOVI_INDIVIDUAL' => 'N'));
                    $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();
                    $rn_ParteVistas->passaParteVista($processoPai, $processoFilho, Trf1_Sisad_Definicoes::PARTE_VISTA, false);
                    //busca os dados da juntada se tiver (de qualquer tipo não necessariamente anexo)
                    $juntada1 = $this->getJuntada($dadosProcessoPai, $dadosProcessoFilho);
                    //se não tiver juntada
                    if ($juntada1 == null) {
                        //insere uma juntada
                        $idJuntada = $sadTbVipdVincProcDigital->createRow($dadosJuntada)
                                ->save();
                        $dadosJuntada['VIPD_ID_VINCULACAO_PROCESSO'] = $idJuntada;
                        //auditar a juntada
                        $this->auditar('VIPD', Trf1_Sisad_Definicoes::AUDITORIA_INSERIR, null, null, $dadosJuntada);
                    } else {
                        if ($juntada1['VIPD_IC_ATIVO'] == 'S') {
                            if ($juntada1['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR) {
                                $validacao['validado'] = false;
                                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) já está anexado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}).";
                            } elseif ($juntada1['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR) {
                                $validacao['validado'] = false;
                                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) já está apensado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}). Desapense os processos e tente novamente.";
                            } elseif ($juntada1['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR) {
                                $validacao['validado'] = false;
                                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) está vinculado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}). Desvincule os processos e tente novamente.";
                            }
                            return $validacao;
                        } else {
                            //altera a juntada
                            $idJuntada = $this->ativarJuntada($dadosJuntada);
                        }
                    }
                }
            } elseif (in_array($arrayVinculo['id'], array(Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR, Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR))) {
                if ($arrayVinculo['id'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR) {
                    //um processo está apensado ao outro então deve-se alterar os dados dos mesmos na docm
                    Trf1_Sisad_Negocio_Documento::alterar($processoFilho['DOCM_ID_DOCUMENTO'], array('DOCM_ID_DOCUMENTO_PAI' => $processoPai['DOCM_ID_DOCUMENTO'], 'DOCM_IC_APENSADO' => 'S'));
                    //não colocar docm_id_documento_pai pois ele é o documento pai
                    Trf1_Sisad_Negocio_Documento::alterar($processoPai['DOCM_ID_DOCUMENTO'], array('DOCM_IC_APENSADO' => 'S'));
                    //verifica se existe algum tipo de juntada apensar do processo filho no processo pai
                }
                $juntada1 = $this->getJuntada($dadosProcessoPai, $dadosProcessoFilho);
                //se o processo filho não está apensado ao pai
                if ($juntada1 == null) {
                    //não foi detectado juntada para o primeiro teste
                    //busca a juntada tranformando filho em pai
                    $juntada2 = $this->getJuntada($dadosProcessoFilho, $dadosProcessoPai);
                    //Se o processo pai não está apensado no processo filho
                    if ($juntada2 == null) {
                        //não foi detectada nenhuma juntada
                        //tenta inserir juntada
                        $idJuntada = $sadTbVipdVincProcDigital->createRow($dadosJuntada)
                                ->save();
                        $dadosJuntada['VIPD_ID_VINCULACAO_PROCESSO'] = $idJuntada;
                        //auditar a juntada
                        $this->auditar('VIPD', Trf1_Sisad_Definicoes::AUDITORIA_INSERIR, null, null, $dadosJuntada);
                    } else {
                        //se a juntada está ativa
                        if ($juntada2['VIPD_IC_ATIVO'] == 'S') {
                            if ($juntada2['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR) {
                                $validacao['validado'] = false;
                                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}) já está apensado ao processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}).";
                            } elseif ($juntada2['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR) {
                                $validacao['validado'] = false;
                                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}) já está anexado ao processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}). Desanexe, se ainda estiverem na mesma movimentação, os processos e tente novamente.";
                            } elseif ($juntada2['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR) {
                                $validacao['validado'] = false;
                                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}) está vinculado ao processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}). Desvincule os processos e tente novamente.";
                            }
                            return $validacao;
                        } else {
                            //logo o processo filho é o pai mas é uma juntada que já foi inativada
                            //é necessário reativar esta juntada
                            //juntado o processo pai no processo filho (o filho torna-se pai)
                            $aux = $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_PRINC'];
                            $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_PRINC'] = $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_VINDO'];
                            $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_VINDO'] = $aux;
                            $idJuntada = $this->ativarJuntada($dadosJuntada);
                        }
                    }
                } else {
                    //foi detectada a juntada para o primeiro teste
                    if ($juntada1['VIPD_IC_ATIVO'] == 'S') {
                        if ($juntada1['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR) {
                            $validacao['validado'] = false;
                            $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) já está apensado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}).";
                        } elseif ($juntada1['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR) {
                            $validacao['validado'] = false;
                            $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) já está anexado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}). Desanexe, se ainda estiverem na mesma movimentação, os processos e tente novamente.";
                        } elseif ($juntada1['VIPD_ID_TP_VINCULACAO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR) {
                            $validacao['validado'] = false;
                            $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) está vinculado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}). Desvincule os processos e tente novamente.";
                        }
                        return $validacao;
                    } else {
                        $idJuntada = $this->ativarJuntada($dadosJuntada);
                    }
                }
            } else {
                throw new Exception('Não é um tipo de juntada válido.', '', '');
            }

            //guarda os dados no historico de juntada
            $this->registraHistorico($idJuntada, $fase);

            if ($autoCommit) {
                $this->_db->commit();
            }
            $validacao['validado'] = true;
            $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo administrativo({$processoFilho['MASC_NR_DOCUMENTO']}) juntado ao processo administrativo({$processoPai['MASC_NR_DOCUMENTO']}).";
        } catch (Zend_Exception $exception) {

            $validacao['validado'] = false;
            $validacao['mensagem'] = "{$arrayVinculo['nome']}: Processo({$processoFilho['MASC_NR_DOCUMENTO']}) ao Processo({$processoPai['MASC_NR_DOCUMENTO']}): {$exception->getMessage()}.";
            if ($autoCommit) {
                $this->_db->rollBack();
            }
        }

        return $validacao;
    }

    public function juntarVarios(array $dataRequest, $tipoRelacao) {
        $service_tipo = new Services_Sisad_Tipo();
        $service_documento = new Services_Sisad_Documento();
        $tiposJuntada = $service_tipo->getTipoJuntada($tipoRelacao, $dataRequest['TP_VINCULO']);
        $validacao = array();

        if ($tiposJuntada) {
            $qtd = count($dataRequest['documentoPrincipal']);
            for ($i = 0; $i < $qtd; $i++) {

                $zend_date = new Zend_Date(null, 'dd/MM/YY HH:mm:ss');
                $this->_dateTime = $zend_date->get(Zend_Date::DATETIME);

                $processo_json = $dataRequest['documentoPrincipal'][$i];
                $processoPai = Zend_Json::decode($processo_json);
                if ($dataRequest['TP_VINCULO'] == Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR) {
                    $apensos = $this->getProcessosApensadosAtivos($processoPai, false);
                    if (count($apensos) > 0) {
                        //inclui no final do array de documentos principais os processos apensados
                        foreach ($apensos as $value) {
                            $aux = $service_documento->getDocumento($value['DOCM_ID_DOCUMENTO']);
                            $value = array_merge($value, $aux);
                            $dataRequest['documentoPrincipal'][] = Zend_Json::encode($value);
                        }
                        $qtd += count($apensos);
                    }
                }
                $verificaProcesso = $this->validaProcessoPai($processoPai, $dataRequest['TP_VINCULO']);
                if ($verificaProcesso['validado']) {
                    try {
                        $this->_db->beginTransaction();
                        /* CRIA A FASE PRIMEIRO POR QUE ELA SERÁ REFERENCIADA NA TABELA DE JUNTADA. */
                        $arrayFase = array(
                            'MOFA_ID_MOVIMENTACAO' => $processoPai['MOFA_ID_MOVIMENTACAO']
                            , 'MOFA_ID_FASE' => Trf1_Sisad_Negocio_Fase::getFaseJuntada($tipoRelacao, $dataRequest['TP_VINCULO'])
                            , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                            , 'MOFA_DH_FASE' => new Zend_Db_Expr("TO_DATE('$this->_dateTime','dd/mm/YY HH24:MI:SS')")
                            , 'MOFA_DS_COMPLEMENTO' => $dataRequest['MOFA_DS_COMPLEMENTO']);
                        //lança a fase da juntada
                        Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
                        $qtdJuntados = 0;
                        foreach ($dataRequest['documentoVinculacao'] as $documento_v) {

                            $processoFilho = Zend_Json::decode($documento_v);

                            //VERIFICA SE O DOCUMENTO É VALIDO E PEGA O STATUS DE ORIGINAL OU CÓPIA PARA JUNTADA
                            $verificaProcessoFilho = $this->validaProcessoFilho($processoFilho);
                            //une o status ao documento
                            $processoFilho = array_merge($processoFilho, $verificaProcessoFilho);

                            // Se for apensação ou vinculação então o filho recebe a fase também
                            if (in_array($dataRequest['TP_VINCULO'], array(Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR, Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR))) {
                                $arrayFase['MOFA_ID_MOVIMENTACAO'] = $processoFilho['MOFA_ID_MOVIMENTACAO'];

                                //lança a fase da juntada
                                Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
                            }

                            if ($verificaProcessoFilho['validado']) {
                                //junta os documentos
                                $resultado = $this->juntar($processoPai, $processoFilho, $tiposJuntada, $this->_dateTime, $this->_userNs->matricula, $arrayFase, false);

                                $validacao[] = $resultado;
                                if ($resultado['validado']) {
                                    $qtdJuntados++;
                                }
                            } else {
                                $validacao[] = $verificaProcessoFilho;
                            }
                        }// FIM foreach($dataRequest['documentoVinculacao'] as $documento_v)
                        if ($qtdJuntados > 0) {
                            $this->_db->commit();
                        } else {
                            $this->_db->rollBack();
                        }
                    } catch (Zend_Exception $exception) {
                        //erro ao lançar a fase
                        $validacao[] = array('validado' => false, 'mensagem' => 'Erro com o documento ' . $processoPai['MASC_NR_DOCUMENTO'] . ': ' . $exception->getMessage());
                        $this->_db->rollBack();
                    }
                } else {
                    $validacao[] = $verificaProcesso;
                }
            }//FIM FOR
        } else {
            $validacao = array('validacao' => false, 'mensagem' => 'Não foi encontrado o tipo de vinculação selecionada.');
        }

        return $validacao;
    }

    /**
     * Realiza a auditoria do esquema de juntada de documentos a processos
     * 
     * @param string $shortName
     * @param string $acao
     * @param int $idProcessoPai
     * @param int $idProcessoFilho
     * @param array $dadosNovos
     */
    public function auditar($shortName, $acao, $idProcessoPai, $idProcessoFilho, $dadosNovos = null) {
        $tb_auditoria = new Application_Model_DbTable_Sisad_SadTbVipdAuditoria();
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $dual = new Application_Model_DbTable_Dual();

        $shortName = strtoupper($shortName);
        $dataTimeStamp = $dual->localtimestampDb();

        $data_audit[$shortName . '_TS_OPERACAO'] = $dataTimeStamp['DATA'];
        $data_audit[$shortName . '_CD_MATRICULA_OPERACAO'] = $this->_userNs->matricula;
        $data_audit[$shortName . '_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
        $data_audit[$shortName . '_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

        if ($acao == Trf1_Sisad_Definicoes::AUDITORIA_INSERIR) {

            $data_audit[$shortName . '_IC_OPERACAO'] = $acao;
            foreach ($dadosNovos as $key => $value) {
                $data_audit['NEW_' . $key] = $value;
            }

            $data_audit['NEW_VIPD_ID_VINCULACAO_PROC'] = $dadosNovos['VIPD_ID_VINCULACAO_PROCESSO'];
            $data_audit['NEW_VIPD_ID_PROC_DIG_PRINC'] = $dadosNovos['VIPD_ID_PROCESSO_DIGITAL_PRINC'];
            $data_audit['NEW_VIPD_ID_PROC_DIG_VINDO'] = $dadosNovos['VIPD_ID_PROCESSO_DIGITAL_VINDO'];
        } elseif ($acao == Trf1_Sisad_Definicoes::AUDITORIA_EXCLUIR) {

            $data_audit[$shortName . '_IC_OPERACAO'] = $acao;

            foreach ($dadosRow as $key => $value) {
                $data_audit['OLD_' . $key] = $value;
            }
            $data_audit['OLD_VIPD_ID_VINCULACAO_PROC'] = $dadosRow['VIPD_ID_VINCULACAO_PROCESSO'];
            $data_audit['NEW_VIPD_ID_VINCULACAO_PROC'] = $dadosNovos['VIPD_ID_VINCULACAO_PROCESSO'];

            $data_audit['OLD_VIPD_ID_PROC_DIG_PRINC'] = $dadosRow['VIPD_ID_PROCESSO_DIGITAL_PRINC'];
            $data_audit['NEW_VIPD_ID_PROC_DIG_PRINC'] = $dadosNovos['VIPD_ID_PROCESSO_DIGITAL_PRINC'];

            $data_audit['OLD_VIPD_ID_PROC_DIG_VINDO'] = $dadosRow['VIPD_ID_PROCESSO_DIGITAL_VINDO'];
            $data_audit['NEW_VIPD_ID_PROC_DIG_VINDO'] = $dadosNovos['VIPD_ID_PROCESSO_DIGITAL_VINDO'];
        } elseif ($acao == Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR) {

            $data_audit[$shortName . '_IC_OPERACAO'] = $acao;

            $dadosRow = $sadTbVipdVincProcDigital->fetchRow(
                            'VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $idProcessoPai
                            . ' AND VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $idProcessoFilho
                    )->toArray();

            foreach ($dadosRow as $key => $value) {
                $data_audit['OLD_' . $key] = $value;
                $data_audit['NEW_' . $key] = $dadosNovos[$key];
            }
            $data_audit['OLD_VIPD_ID_VINCULACAO_PROC'] = $dadosRow['VIPD_ID_VINCULACAO_PROCESSO'];
            $data_audit['NEW_VIPD_ID_VINCULACAO_PROC'] = $dadosNovos['VIPD_ID_VINCULACAO_PROCESSO'];

            $data_audit['OLD_VIPD_ID_PROC_DIG_PRINC'] = $dadosRow['VIPD_ID_PROCESSO_DIGITAL_PRINC'];
            $data_audit['NEW_VIPD_ID_PROC_DIG_PRINC'] = $dadosNovos['VIPD_ID_PROCESSO_DIGITAL_PRINC'];

            $data_audit['OLD_VIPD_ID_PROC_DIG_VINDO'] = $dadosRow['VIPD_ID_PROCESSO_DIGITAL_VINDO'];
            $data_audit['NEW_VIPD_ID_PROC_DIG_VINDO'] = $dadosNovos['VIPD_ID_PROCESSO_DIGITAL_VINDO'];
        }
        $tb_auditoria->createRow($data_audit)
                ->save();
    }

    /**
     * Verifica se o processo já esteja anexado à outro processo
     * @param array $processo
     * @return array
     */
    public function isAnexado($processo) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $row = $sadTbVipdVincProcDigital->fetchRow(
                '   VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                . ' AND VIPD_IC_ATIVO = \'S\' 
                            AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR
        );
        return (is_null($row) ? null : $row->toArray());
    }

    /**
     * Verifica se o processo possui algum tipo de juntada
     * @param array $processo
     * @return array
     */
    public function hasJuntada($processo, $flagAtivo = true) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $row = $sadTbVipdVincProcDigital->fetchRow(
                '   (
                        VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                . '     OR VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                . ' )'
                . ($flagAtivo ? " AND VIPD_IC_ATIVO = 'S'" : '')
        );
        return (is_null($row) ? null : $row->toArray());
    }

    /**
     * Verifica se o processo já está apensado à outro processo
     * @param array $processo
     * @return array
     */
    public function isApensado($processo) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $row = $sadTbVipdVincProcDigital->fetchRow(
                '   VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                . ' AND VIPD_IC_ATIVO = \'S\' 
                            AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR
        );
        return (is_null($row) ? null : $row->toArray());
    }

    /**
     * 
     */
    public function hasApensos() {
        throw new Exception('Function não desenvolvida.', 1, null);
    }

    /**
     * retorna se tem vinculos ou não com outros processos
     * @param array $processo
     * @param boolean $flagAtivo
     * @return boolean
     */
    public function isVinculos($processo, $flagAtivo = true) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $qtd = $sadTbVipdVincProcDigital->fetchAll(
                        '   (
                                VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                        . '     OR VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                        . ' )'
                        . ($flagAtivo ? " AND VIPD_IC_ATIVO = 'S'" : '')
                        . ' AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR
                )->count();
        return ($qtd > 0);
    }

    /**
     * Verifica se o processo administrativo tem apensos ou se é apensado
     * @param array $processo
     * @return bolean
     */
    public function isApensadoOuApensos($processo, $flagAtivo = true) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $qtd = $sadTbVipdVincProcDigital->fetchAll(
                        '   (
                                VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                        . '     OR VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processo['PRDI_ID_PROCESSO_DIGITAL']
                        . ' )'
                        . ($flagAtivo ? " AND VIPD_IC_ATIVO = 'S'" : '')
                        . ' AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR
                )->count();
        return ($qtd > 0);
    }

    /**
     * Registra o histórico de juntada
     * 
     * @param int $juntada
     * @param array $fase
     */
    private function registraHistorico($juntada, $fase) {

        $dados = array(
            'VPPF_ID_VINCULACAO_PROCESSO' => $juntada
            , 'VPPF_ID_MOVIMENTACAO' => $fase['MOFA_ID_MOVIMENTACAO']
            , 'VPPF_DH_FASE' => $fase['MOFA_DH_FASE']
        );
        $sadTbVppfVincProProFase = new Application_Model_DbTable_Sisad_SadTbVppfVincProProFase();
        $sadTbVppfVincProProFase->createRow($dados)->save();
    }

    /**
     * Retorna os processos anexados em outros processos
     * @param array $processo
     * @return array
     */
    public function getProcessosAnexados($processo) {
        $sql = '
            SELECT 
                *
            FROM (
                SELECT
                    DTPD.DTPD_NO_TIPO
                    , DTPD.DTPD_ID_TIPO_DOC
                    , PRDI_ID_PROCESSO_DIGITAL
                    , PRDI_DS_TEXTO_AUTUACAO
                    , AQVP_CD_PCTT
                    , DOCM.DOCM_ID_DOCUMENTO
                    , DOCM.DOCM_NR_DOCUMENTO
                    --TIPO DE JUNTADA ATUAL
                    , VIPD_ID_TP_VINCULACAO
                    , VIPD_IC_ATIVO
                    , VIPD_IC_ORIGINAL
                    ,DECODE(
                            LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                            14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                            sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                       ) MASC_NR_DOCUMENTO
                    , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                    , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                    , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                    , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                    , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                    , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,\'DD/MM/YYYY HH24:MI:SS\') VIPD_DH_VINCULACAO
                    , DOCM.DOCM_NR_DOCUMENTO_RED
                    , AQAT.AQAT_DS_ATIVIDADE
                    , CONF.CONF_ID_CONFIDENCIALIDADE
                    , \'PROCESSO ANEXADO\'
                    , \'\' AS PMAT_CD_MATRICULA_EXCLUIDOR
                    , \'\' AS PNAT_NO_PESSOA_EXCLUIDOR
                    , null AS VPPF_DH_FASE
                FROM
                    SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                    INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                        ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                        ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                    INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                        ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                    INNER JOIN RH_CENTRAL_LOTACAO LOTA
                        ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                        AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                    INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                        ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                    INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                        ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                    INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                        ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                    INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                        ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                    WHERE
                        VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processo['PRDI_ID_PROCESSO_DIGITAL'] . '
                        AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR . '
                        AND DOCM_IC_PROCESSO_AUTUADO = \'N\'
                        AND VIPD_IC_ATIVO = \'S\'
                        AND DOCM_ID_TIPO_DOC = ' . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . '


                UNION


                SELECT 
                    DTPD_NO_TIPO
                    , DTPD_ID_TIPO_DOC
                    , PRDI_ID_PROCESSO_DIGITAL  
                    , PRDI_DS_TEXTO_AUTUACAO
                    , AQVP_CD_PCTT
                    , DOCM_ID_DOCUMENTO
                    , DOCM_NR_DOCUMENTO
                    --TIPO DE JUNTADA ATUAL
                    , VIPD_ID_TP_VINCULACAO
                    , VIPD_IC_ATIVO
                    , VIPD_IC_ORIGINAL
                    ,DECODE(
                            LENGTH( DOCM_NR_DOCUMENTO),
                            14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                            sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                       ) MASC_NR_DOCUMENTO
                    , LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                    , LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                    , LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                    , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                    , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                    , TO_CHAR (VIPD_DH_VINCULACAO,\'DD/MM/YYYY HH24:MI:SS\') VIPD_DH_VINCULACAO
                    , DOCM_NR_DOCUMENTO_RED
                    , AQAT_DS_ATIVIDADE
                    , CONF_ID_CONFIDENCIALIDADE
                    , \'PROCESSO DESANEXADO\'
                    , PMAT_CD_MATRICULA PMAT_CD_MATRICULA_EXCLUIDOR
                    , PNAT_NO_PESSOA PNAT_NO_PESSOA_EXCLUIDOR
                    , TO_CHAR (VPPF_DH_FASE,\'DD/MM/YYYY HH24:MI:SS\') AS VPPF_DH_FASE
                FROM(
                    SELECT
                        *
                    FROM 
                        SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                        INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE VPPF
                            ON VIPD_ID_VINCULACAO_PROCESSO = VPPF_ID_VINCULACAO_PROCESSO
                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                            ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                            AND VPPF_DH_FASE = MOFA_DH_FASE
                        INNER JOIN OCS_TB_PMAT_MATRICULA
                        ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                        INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                            ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                        INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI

                            ON VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI_ID_PROCESSO_DIGITAL
                            AND VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processo['PRDI_ID_PROCESSO_DIGITAL'] . '

                        INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                            ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                        INNER JOIN RH_CENTRAL_LOTACAO LOTA
                            ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                            AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                        INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                            ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                        INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                            ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                        INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                            ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                        INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                            ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                        WHERE
                            MOFA_ID_FASE = ' . Trf1_Sisad_Definicoes::FASE_DESANEXAR_PROCESSO_PROCESSO . ' --DESANEXAR PROCESSO A PROCESSO
                            --PEGA A ULTIMA DESAPENSAÇÃO
                            AND VPPF_DH_FASE = (    SELECT MAX(VPPF_DH_FASE) FROM 
                                                         SAD_TB_VIPD_VINC_PROC_DIGITAL
                                                         INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE
                                                             ON VIPD_ID_VINCULACAO_PROCESSO = VPPF_ID_VINCULACAO_PROCESSO
                                                         INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                                             ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                                             AND VPPF_DH_FASE = MOFA_DH_FASE
                                                    WHERE 
                                                         MOFA_ID_FASE = ' . Trf1_Sisad_Definicoes::FASE_DESANEXAR_PROCESSO_PROCESSO . ' --DESANEXAR PROCESSO A PROCESSO
                                                         AND VIPD_ID_VINCULACAO_PROCESSO = VIPD.VIPD_ID_VINCULACAO_PROCESSO)
                            AND DOCM_ID_TIPO_DOC = ' . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . '
                            AND DOCM_IC_PROCESSO_AUTUADO = \'N\'
                    ) SUBQUERY
                WHERE
                    0 = (   SELECT COUNT(*) QTD
                            FROM SAD_TB_VPPF_VINC_PRO_PRO_FASE
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                    ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                    AND VPPF_DH_FASE = MOFA_DH_FASE
                            WHERE
                                VPPF_ID_VINCULACAO_PROCESSO = SUBQUERY.VPPF_ID_VINCULACAO_PROCESSO
                                AND VPPF_DH_FASE > SUBQUERY.VPPF_DH_FASE
                                AND MOFA_ID_FASE = ' . Trf1_Sisad_Definicoes::FASE_ANEXAR_PROCESSO_PROCESSO . '    )--FASE ANEXAR PROCESSO A PROCESSO
            ) ORDER BY TO_DATE (VIPD_DH_VINCULACAO,\'DD/MM/YYYY HH24:MI:SS\') DESC';
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos juntados em outros processos
     * @param array $processo
     * @return array
     */
    public function getProcessosApensados($processo) {

        $sql = "
            SELECT 
                *
            FROM (
                        -- NESTE SELECT SE UM PROCESSO FOR FILHO ENTAO ELE VAI LISTAR APENAS OS PAIS A QUERY QUE MOSTRA OS FILHOS NÃO APARECER
                        -- SE A LÓGICA DE INSERIR DA JUNTADA TIVER CORRETA É CLARO!
                        -- MAS SE O PROCESSO FOR UM PAI ENTAO ELE SO VAI LISTAR OS FILHOS,
                        -- O SELECT DE PAIS NÃO deverá RETORNAR RESULTADOS SE A LÓGICA DE INSERIR NÃO ESTIVER ERRADA!


                        -- PEGA OS PROCESSOS FILHOS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'APENSO' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO FILHO
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                -- ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC IN(
                                
                                    -- CASO SEJA UM PROCESSO FILHO PEGUE O PAI, CASO SEJA UM PAI PEGUE O FILHO
                                    -- A FIM DE RETORNAR UM JUNTADA COMPLETA DE FILHOS
                                    -- ABAIXO NO WHERE SERÁ ELIMINADO O PROCESSO PASSADO POR PARAMETRO
                                    
                                    SELECT VIPD_ID_PROCESSO_DIGITAL_PRINC
                                    FROM SAD_TB_VIPD_VINC_PROC_DIGITAL
                                    WHERE    
                                        VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                        AND VIPD_IC_ATIVO = 'S'
                                        AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "

                                    UNION

                                    SELECT VIPD_ID_PROCESSO_DIGITAL_VINDO
                                    FROM SAD_TB_VIPD_VINC_PROC_DIGITAL
                                    WHERE    
                                        VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                        AND VIPD_IC_ATIVO = 'S'
                                        AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "
                                    
                                    UNION
                                    
                                    SELECT " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . " FROM DUAL
                                )
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "


                        UNION


                        -- PEGA OS PROCESSOS PAIS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'APENSO PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO PAI
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                --ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "


                        UNION            

                        --PEGA OS PROCESSOS INATIVOS
                        SELECT  DTPD_NO_TIPO
                                , DTPD_ID_TIPO_DOC
                                , PRDI_ID_PROCESSO_DIGITAL
                                , PRDI_DS_TEXTO_AUTUACAO
                                , AQVP_CD_PCTT
                                , DOCM_ID_DOCUMENTO
                                , DOCM_NR_DOCUMENTO
                                --TIPO DE JUNTADA ATUAL
                                , VIPD_ID_TP_VINCULACAO
                                , VIPD_IC_ATIVO
                                , VIPD_IC_ORIGINAL
                                ,DECODE(
                                        LENGTH( DOCM_NR_DOCUMENTO),
                                        14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                        sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                   ) MASC_NR_DOCUMENTO
                                , LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                                , LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                                , LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                                , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                                , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                                , TO_CHAR (VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                                , DOCM_NR_DOCUMENTO_RED
                                , AQAT_DS_ATIVIDADE
                                , CONF_ID_CONFIDENCIALIDADE
                                , 'PROCESSO DESAPENSADO' AS STATUS_JUNTADA
                                , PMAT_CD_MATRICULA PMAT_CD_MATRICULA_EXCLUIDOR
                                , PNAT_NO_PESSOA PNAT_NO_PESSOA_EXCLUIDOR
                                , TO_CHAR (VPPF_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS VPPF_DH_FASE
                        FROM
                            (SELECT
                                 *
                                 FROM 
                                SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                                INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE VPPF
                                    ON VIPD_ID_VINCULACAO_PROCESSO = VPPF_ID_VINCULACAO_PROCESSO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                    ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                    AND VPPF_DH_FASE = MOFA_DH_FASE
                                INNER JOIN OCS_TB_PMAT_MATRICULA
                                    ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                                INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                    ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                                INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                    -- ID PROCESSO PAI
                                    ON (
                                            (
                                                VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI_ID_PROCESSO_DIGITAL
                                                AND VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                                --ELIMINA OS PROCESSOS PAIS DA RELAÇÃO POIS O SELECT MOSTRA TAMBEM OS DADOS DOS PROCESSOS PAIS
                                                --AND VIPD_ID_PROCESSO_DIGITAL_PRINC != prdi_id_processo_digital
                                            )
                                            OR
                                            (
                                                VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI_ID_PROCESSO_DIGITAL
                                                AND VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                                --ELIMINA OS PROCESSOS PAIS DA RELAÇÃO POIS O SELECT MOSTRA TAMBEM OS DADOS DOS PROCESSOS PAIS
                                                --AND VIPD_ID_PROCESSO_DIGITAL_PRINC != prdi_id_processo_digital
                                            )
                                       )
                                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                            ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                                        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                            ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                                        INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                            ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                            AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                                        INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                            ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                        INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                            ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                        INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                            ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                                        INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                            ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE 
                                MOFA_ID_FASE = " . Trf1_Sisad_Definicoes::FASE_DESAPENSAR_PROCESSO_PROCESSO . " --DESAPENSAR PROCESSO A PROCESSO
                                --PEGA A ULTIMA DESAPENSAÇÃO
                                AND VPPF_DH_FASE = (    SELECT MAX(VPPF_DH_FASE) FROM 
                                                             SAD_TB_VIPD_VINC_PROC_DIGITAL
                                                             INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE
                                                                 ON VIPD_ID_VINCULACAO_PROCESSO = VPPF_ID_VINCULACAO_PROCESSO
                                                             INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                                                 ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                                                 AND VPPF_DH_FASE = MOFA_DH_FASE
                                                        WHERE 
                                                             MOFA_ID_FASE = " . Trf1_Sisad_Definicoes::FASE_DESAPENSAR_PROCESSO_PROCESSO . " --DESAPENSAR PROCESSO A PROCESSO
                                                             AND VIPD_ID_VINCULACAO_PROCESSO = VIPD.VIPD_ID_VINCULACAO_PROCESSO)
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND DOCM_IC_PROCESSO_AUTUADO = 'N'
                            ) SUBQUERY
                        WHERE
                            0 = (   SELECT COUNT(*) QTD
                                    FROM SAD_TB_VPPF_VINC_PRO_PRO_FASE
                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                            ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                            AND VPPF_DH_FASE = MOFA_DH_FASE
                                    WHERE
                                        VPPF_ID_VINCULACAO_PROCESSO = SUBQUERY.VPPF_ID_VINCULACAO_PROCESSO
                                        AND VPPF_DH_FASE > SUBQUERY.VPPF_DH_FASE
                                        AND MOFA_ID_FASE = " . Trf1_Sisad_Definicoes::FASE_APENSAR_PROCESSO_PROCESSO . "   ) 
            )
            WHERE PRDI_ID_PROCESSO_DIGITAL != " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
            ORDER BY TO_DATE (VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DESC";

        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos apensados ao processo passado por parametro. Os processos estão ativos na juntada.
     * @param array $processo
     * @return array
     */
    public function getProcessosApensadosAtivos($processo, $pegaPais = true) {
        $sqlFilhos = "
                        -- NESTE SELECT SE UM PROCESSO FOR FILHO ENTAO ELE VAI LISTAR APENAS OS PAIS A QUERY QUE MOSTRA OS FILHOS NÃO APARECER
                        -- SE A LÓGICA DE INSERIR DA JUNTADA TIVER CORRETA É CLARO!
                        -- MAS SE O PROCESSO FOR UM PAI ENTAO ELE SO VAI LISTAR OS FILHOS,
                        -- O SELECT DE PAIS NÃO deverá RETORNAR RESULTADOS SE A LÓGICA DE INSERIR NÃO ESTIVER ERRADA!


                        -- PEGA OS PROCESSOS FILHOS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'NAO PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                            , 'APENSO' AS TIPO_JUNTADA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO FILHO
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                -- ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC IN(
                                
                                    -- CASO SEJA UM PROCESSO FILHO PEGUE O PAI, CASO SEJA UM PAI PEGUE O FILHO
                                    -- A FIM DE RETORNAR UM JUNTADA COMPLETA DE FILHOS
                                    -- ABAIXO NO WHERE SERÁ ELIMINADO O PROCESSO PASSADO POR PARAMETRO
                                    
                                    SELECT VIPD_ID_PROCESSO_DIGITAL_PRINC
                                    FROM SAD_TB_VIPD_VINC_PROC_DIGITAL
                                    WHERE    
                                        VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                        AND VIPD_IC_ATIVO = 'S'
                                        AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "

                                    UNION

                                    SELECT VIPD_ID_PROCESSO_DIGITAL_VINDO
                                    FROM SAD_TB_VIPD_VINC_PROC_DIGITAL
                                    WHERE    
                                        VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                        AND VIPD_IC_ATIVO = 'S'
                                        AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "
                                            
                                    UNION
                                    SELECT " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . " FROM DUAL
                                )
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR;
        $sqlPais = "
                        -- PEGA OS PROCESSOS PAIS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                            , 'APENSO' AS TIPO_JUNTADA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO PAI
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                --ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR;

        if ($pegaPais) {
            $completa = $sqlFilhos . ' UNION ' . $sqlPais;
        } else {
            $completa = $sqlFilhos;
        }
        $sql = 'SELECT 
                        *
                FROM (' . $completa . ') 
                WHERE PRDI_ID_PROCESSO_DIGITAL != ' . $processo['PRDI_ID_PROCESSO_DIGITAL'] . '
                ORDER BY TO_DATE (VIPD_DH_VINCULACAO,\'DD/MM/YYYY HH24:MI:SS\') DESC';

        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos juntados em outros processos
     * @param array $processo
     * @return array
     */
    public function getProcessosVinculados($processo) {

        $sql = "
            SELECT 
                *
            FROM (
                        -- NESTE SELECT SE UM PROCESSO FOR FILHO ENTAO ELE VAI LISTAR APENAS OS PAIS A QUERY QUE MOSTRA OS FILHOS NÃO APARECER
                        -- SE A LÓGICA DE INSERIR DA JUNTADA TIVER CORRETA É CLARO!
                        -- MAS SE O PROCESSO FOR UM PAI ENTAO ELE SO VAI LISTAR OS FILHOS,
                        -- O SELECT DE PAIS NÃO deverá RETORNAR RESULTADOS SE A LÓGICA DE INSERIR NÃO ESTIVER ERRADA!


                        -- PEGA OS PROCESSOS FILHOS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'NAO PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO FILHO
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                -- ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "


                        UNION


                        -- PEGA OS PROCESSOS PAIS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO PAI
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                --ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "


                        UNION            

                        --PEGA OS PROCESSOS INATIVOS
                        SELECT  DTPD_NO_TIPO
                                , DTPD_ID_TIPO_DOC
                                , PRDI_ID_PROCESSO_DIGITAL
                                , PRDI_DS_TEXTO_AUTUACAO
                                , AQVP_CD_PCTT
                                , DOCM_ID_DOCUMENTO
                                , DOCM_NR_DOCUMENTO
                                --TIPO DE JUNTADA ATUAL
                                , VIPD_ID_TP_VINCULACAO
                                , VIPD_IC_ATIVO
                                , VIPD_IC_ORIGINAL
                                ,DECODE(
                                        LENGTH( DOCM_NR_DOCUMENTO),
                                        14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                        sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                   ) MASC_NR_DOCUMENTO
                                , LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                                , LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                                , LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                                , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                                , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                                , TO_CHAR (VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                                , DOCM_NR_DOCUMENTO_RED
                                , AQAT_DS_ATIVIDADE
                                , CONF_ID_CONFIDENCIALIDADE
                                , 'PROCESSO DESVINCULADO' AS STATUS_JUNTADA
                                , PMAT_CD_MATRICULA PMAT_CD_MATRICULA_EXCLUIDOR
                                , PNAT_NO_PESSOA PNAT_NO_PESSOA_EXCLUIDOR
                                , TO_CHAR (VPPF_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS VPPF_DH_FASE
                        FROM
                            (SELECT
                                 *
                                 FROM 
                                SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                                INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE VPPF
                                    ON VIPD_ID_VINCULACAO_PROCESSO = VPPF_ID_VINCULACAO_PROCESSO
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                    ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                    AND VPPF_DH_FASE = MOFA_DH_FASE
                                INNER JOIN OCS_TB_PMAT_MATRICULA
                                    ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                                INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                    ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                                INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                                    -- ID PROCESSO PAI
                                    ON (
                                            (
                                                VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI_ID_PROCESSO_DIGITAL
                                                AND VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                                --ELIMINA OS PROCESSOS PAIS DA RELAÇÃO POIS O SELECT MOSTRA TAMBEM OS DADOS DOS PROCESSOS PAIS
                                                --AND VIPD_ID_PROCESSO_DIGITAL_PRINC != prdi_id_processo_digital
                                            )
                                            OR
                                            (
                                                VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI_ID_PROCESSO_DIGITAL
                                                AND VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                                --ELIMINA OS PROCESSOS PAIS DA RELAÇÃO POIS O SELECT MOSTRA TAMBEM OS DADOS DOS PROCESSOS PAIS
                                                --AND VIPD_ID_PROCESSO_DIGITAL_PRINC != prdi_id_processo_digital
                                            )
                                       )
                                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                            ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                                        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                            ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                                        INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                            ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                            AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                                        INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                            ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                        INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                            ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                        INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                            ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                                        INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                            ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                            WHERE 
                                MOFA_ID_FASE = " . Trf1_Sisad_Definicoes::FASE_DESVINCULAR_PROCESSO_PROCESSO . " --DESVINCULAR PROCESSO A PROCESSO
                                --PEGA A ULTIMA DESVINCULAÇÃO
                                AND VPPF_DH_FASE = (    SELECT MAX(VPPF_DH_FASE) FROM 
                                                             SAD_TB_VIPD_VINC_PROC_DIGITAL
                                                             INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE
                                                                 ON VIPD_ID_VINCULACAO_PROCESSO = VPPF_ID_VINCULACAO_PROCESSO
                                                             INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                                                 ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                                                 AND VPPF_DH_FASE = MOFA_DH_FASE
                                                        WHERE 
                                                             MOFA_ID_FASE = " . Trf1_Sisad_Definicoes::FASE_DESVINCULAR_PROCESSO_PROCESSO . " --DESVINCULAR PROCESSO A PROCESSO
                                                             AND VIPD_ID_VINCULACAO_PROCESSO = VIPD.VIPD_ID_VINCULACAO_PROCESSO)
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND DOCM_IC_PROCESSO_AUTUADO = 'N'
                            ) SUBQUERY
                        WHERE
                            0 = (   SELECT COUNT(*) QTD
                                    FROM SAD_TB_VPPF_VINC_PRO_PRO_FASE
                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                            ON VPPF_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                                            AND VPPF_DH_FASE = MOFA_DH_FASE
                                    WHERE
                                        VPPF_ID_VINCULACAO_PROCESSO = SUBQUERY.VPPF_ID_VINCULACAO_PROCESSO
                                        AND VPPF_DH_FASE > SUBQUERY.VPPF_DH_FASE
                                        AND MOFA_ID_FASE = " . Trf1_Sisad_Definicoes::FASE_VINCULA_PROCESSO_PROCESSO . "   ) 
            ) ORDER BY TO_DATE (VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DESC";

        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos juntados em outros processos
     * @param array $processo
     * @return array
     */
    public function getProcessosVinculadosAtivos($processo) {

        $sql = "
            SELECT 
                *
            FROM (
                        -- NESTE SELECT SE UM PROCESSO FOR FILHO ENTAO ELE VAI LISTAR APENAS OS PAIS A QUERY QUE MOSTRA OS FILHOS NÃO APARECER
                        -- SE A LÓGICA DE INSERIR DA JUNTADA TIVER CORRETA É CLARO!
                        -- MAS SE O PROCESSO FOR UM PAI ENTAO ELE SO VAI LISTAR OS FILHOS,
                        -- O SELECT DE PAIS NÃO deverá RETORNAR RESULTADOS SE A LÓGICA DE INSERIR NÃO ESTIVER ERRADA!


                        -- PEGA OS PROCESSOS FILHOS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'NAO PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                            , 'VINCULO' AS TIPO_JUNTADA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO FILHO
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                

                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                -- ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "


                        UNION


                        -- PEGA OS PROCESSOS PAIS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                            , 'VINCULO' AS TIPO_JUNTADA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO PAI
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                --ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "


                        
            ) ORDER BY TO_DATE (VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DESC";

        return $this->_db->fetchAll($sql);
    }

    /**
     * 
     * @param array $processoPai
     * @param array $processoFilho
     * @param array $tipoJuntada
     * 
     * @return mixed |array|null|
     */
    public function getJuntada($processoPai, $processoFilho, $tipoJuntada = null) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $row = $sadTbVipdVincProcDigital->fetchRow(
                'VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processoPai['PRDI_ID_PROCESSO_DIGITAL']
                . ' AND VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processoFilho['PRDI_ID_PROCESSO_DIGITAL']
                . (!is_null($tipoJuntada) ? ' AND VIPD_ID_TP_VINCULACAO = ' . $tipoJuntada : '')
        );
        return (is_null($row) ? null : $row->toArray());
    }

    /**
     * Ativa a juntada
     * @param array $dadosJuntada
     */
    public function ativarJuntada($dadosJuntada) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        $idJuntada = $sadTbVipdVincProcDigital->fetchRow(
                        'VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_PRINC']
                        . ' AND VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_VINDO']
                        //. ' AND VIPD_ID_TP_VINCULACAO = ' . $dadosJuntada['VIPD_ID_TP_VINCULACAO']
                )->setFromArray($dadosJuntada)->save();
        $dadosJuntada['VIPD_ID_VINCULACAO_PROCESSO'] = $idJuntada;
        $this->auditar('VIPD', Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR, $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_PRINC'], $dadosJuntada['VIPD_ID_PROCESSO_DIGITAL_VINDO'], $dadosJuntada);
        return $idJuntada;
    }

    /**
     * Desativa o processo Apenso na condição de filho. Não tem nem lógica desativar o pai.
     * @param array $processo1
     * @param array $processo2
     */
    public function desativaApenso($processo1, $processo2) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        try {
            $this->_db->beginTransaction();
            $row = $sadTbVipdVincProcDigital->fetchRow(
                    'VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo1['PRDI_ID_PROCESSO_DIGITAL']
                    . ' AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR
                    . ' AND VIPD_IC_ATIVO = \'S\''
            );

            //verifica se o outro processo é filho
            if (is_null($row)) {
                $row = $sadTbVipdVincProcDigital->fetchRow(
                        'VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processo2['PRDI_ID_PROCESSO_DIGITAL']
                        . ' AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR
                        . ' AND VIPD_IC_ATIVO = \'S\''
                );
            }

            if (is_null($row)) {
                return 'erro';
            } else {
                $dadosJuntadaApensacao = $row->toArray();
                $id = $row->setFromArray(array('VIPD_IC_ATIVO' => 'N'))->save();
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $dadosProcesso1 = $rn_processo->getDocumentoPorIdProcesso($processo1);
                $dadosProcesso2 = $rn_processo->getDocumentoPorIdProcesso($processo2);

                $arrayFase = array(
                    'MOFA_ID_MOVIMENTACAO' => $dadosProcesso1['MOFA_ID_MOVIMENTACAO']
                    , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_DESAPENSAR_PROCESSO_PROCESSO
                    , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                    , 'MOFA_DH_FASE' => new Zend_Db_Expr("TO_DATE('$this->_dateTime','dd/mm/YY HH24:MI:SS')")
                    , 'MOFA_DS_COMPLEMENTO' => 'Processo administrativo desapensado.');
                //lança a fase da juntada para o processo1
                Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
                $this->registraHistorico($id, $arrayFase);

                //lança a fase da juntada para o processo2
                $arrayFase['MOFA_ID_MOVIMENTACAO'] = $dadosProcesso2['MOFA_ID_MOVIMENTACAO'];
                Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
                $this->registraHistorico($id, $arrayFase);

                //um processo está apensado ao outro então deve-se alterar os dados dos mesmos na docm
                Trf1_Sisad_Negocio_Documento::alterar($dadosProcesso2['DOCM_ID_DOCUMENTO'], array('DOCM_ID_DOCUMENTO_PAI' => null, 'DOCM_IC_APENSADO' => 'N'));
                //não colocar docm_id_documento_pai pois ele é o documento pai
                Trf1_Sisad_Negocio_Documento::alterar($dadosProcesso1['DOCM_ID_DOCUMENTO'], array('DOCM_IC_APENSADO' => 'N'));

                $dadosNovos = $row->toArray();
                $this->auditar('VIPD', Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR, $dadosJuntadaApensacao['VIPD_ID_PROCESSO_DIGITAL_PRINC'], $dadosJuntadaApensacao['VIPD_ID_PROCESSO_DIGITAL_VINDO'], $dadosNovos);

                $this->_db->commit();
                return 'sucesso';
            }
        } catch (Zend_Exception $ze) {
            $this->_db->rollBack();
            return 'erro';
        }
    }

    /**
     * Desativa o processo Apenso
     * @param array $processoPai
     * @param array $processoFilho
     */
    public function desativaVinculo($processoPai, $processoFilho) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        try {
            $this->_db->beginTransaction();
            $row = $sadTbVipdVincProcDigital->fetchRow(
                    'VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processoPai['PRDI_ID_PROCESSO_DIGITAL']
                    . ' AND VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processoFilho['PRDI_ID_PROCESSO_DIGITAL']
                    . ' AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR
            );
            if (is_null($row)) {
                return 'erro';
            } else {
                $id = $row->setFromArray(array('VIPD_IC_ATIVO' => 'N'))->save();
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $dadosProcessoPai = $rn_processo->getDocumentoPorIdProcesso($processoPai);
                $dadosProcessoFilho = $rn_processo->getDocumentoPorIdProcesso($processoFilho);

                $arrayFase = array(
                    'MOFA_ID_MOVIMENTACAO' => $dadosProcessoPai['MOFA_ID_MOVIMENTACAO']
                    , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_DESVINCULAR_PROCESSO_PROCESSO
                    , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                    , 'MOFA_DH_FASE' => new Zend_Db_Expr("TO_DATE('$this->_dateTime','dd/mm/YY HH24:MI:SS')")
                    , 'MOFA_DS_COMPLEMENTO' => 'Processo administrativo desapensado.');
                //lança a fase da juntada para o processo pai
                Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
                //lança a fase da juntada para o processo pai
                $arrayFase['MOFA_ID_MOVIMENTACAO'] = $dadosProcessoFilho['MOFA_ID_MOVIMENTACAO'];
                Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);

                $this->registraHistorico($id, $arrayFase);
                $dadosNovos = $row->toArray();
                $this->auditar('VIPD', Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR, $processoPai['PRDI_ID_PROCESSO_DIGITAL'], $processoFilho['PRDI_ID_PROCESSO_DIGITAL'], $dadosNovos);

                $this->_db->commit();
                return 'sucesso';
            }
        } catch (Zend_Exception $ze) {
            $this->_db->rollBack();
            return 'erro';
        }
    }

    /**
     * Desativa o processo Apenso
     * @param array $processoPai
     * @param array $processoFilho
     */
    public function desativaAnexo($processoPai, $processoFilho) {
        $sadTbVipdVincProcDigital = new Application_Model_DbTable_Sisad_SadTbVipdVincProcDigital();
        try {
            $this->_db->beginTransaction();
            $row = $sadTbVipdVincProcDigital->fetchRow(
                    'VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processoPai['PRDI_ID_PROCESSO_DIGITAL']
                    . ' AND VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processoFilho['PRDI_ID_PROCESSO_DIGITAL']
                    . ' AND VIPD_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR
            );

            $id = $row->setFromArray(array('VIPD_IC_ATIVO' => 'N'))->save();
            $rn_processo = new Trf1_Sisad_Negocio_Processo();
            $dadosProcessoPai = $rn_processo->getDocumentoPorIdProcesso($processoPai);
            $dadosProcessoFilho = $rn_processo->getDocumentoPorIdProcesso($processoFilho);

            $arrayFase = array(
                'MOFA_ID_MOVIMENTACAO' => $dadosProcessoPai['MOFA_ID_MOVIMENTACAO']
                , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_DESANEXAR_PROCESSO_PROCESSO
                , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                , 'MOFA_DH_FASE' => new Zend_Db_Expr("TO_DATE('$this->_dateTime','dd/mm/YY HH24:MI:SS')")
                , 'MOFA_DS_COMPLEMENTO' => 'Processo administrativo desanexado.');
            //lança a fase da juntada para o processo pai
            Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);

            //Ativa a movimentação do processo e remove o documento pai
            Trf1_Sisad_Negocio_Documento::alterar($dadosProcessoFilho['DOCM_ID_DOCUMENTO'], array('DOCM_ID_DOCUMENTO_PAI' => null, 'DOCM_IC_MOVI_INDIVIDUAL' => 'S'));


            $this->registraHistorico($id, $arrayFase);
            $dadosNovos = $row->toArray();
            $this->auditar('VIPD', Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR, $processoPai['PRDI_ID_PROCESSO_DIGITAL'], $processoFilho['PRDI_ID_PROCESSO_DIGITAL'], $dadosNovos);

            $this->_db->commit();
            return 'sucesso';
        } catch (Zend_Exception $ze) {
            $this->_db->rollBack();
            return 'erro';
        }
    }

    /**
     * Retorna os processos que foram juntados ao documento na fase especificada.
     * Vale resaltar que pega os processos pais
     * @param array $processo
     * @param array $processoFilho
     * @param array $fase
     * @param int $idFase
     * @return type
     */
    public function getJuntadaPorFase($processo, $processoFilho, $fase = null, $idFase = null) {
        $completaFaseDesejada = '';
        $completaFase = '';
        if (!is_null($fase)) {

            $fase['MOFA_ID_FASE'] = (isset($fase['MOFA_ID_FASE']) ? $fase['MOFA_ID_FASE'] : $fase['FADM_ID_FASE']);
            $completaFase = "AND MOFA_ID_FASE = " . $fase['MOFA_ID_FASE'] . "
                AND MOFA_DH_FASE = TO_DATE('" . $fase['MOFA_DH_FASE'] . "','DD/MM/YYYY HH24:MI:SS')
                --AND MOFA_ID_MOVIMENTACAO = " . $fase['MOFA_ID_MOVIMENTACAO'];
        }
        if (!is_null($idFase)) {
            $completaFaseDesejada = 'AND MOFA_ID_FASE = ' . $idFase;
        }

        $sql = "
        SELECT
            MOFA_ID_FASE
            , MOFA_DH_FASE
            , PMAT_CD_MATRICULA
            , PNAT_NO_PESSOA
            ,PRDI_ID_PROCESSO_DIGITAL
            ,VIPD_ID_PROCESSO_DIGITAL_PRINC
            ,VIPD_ID_PROCESSO_DIGITAL_VINDO
            ,DOCM_ID_DOCUMENTO
            ,DECODE(
                  LENGTH( DOCM_NR_DOCUMENTO),
                  14, SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(DOCM_NR_DOCUMENTO),
                  SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(DOCM_NR_DOCUMENTO)
             ) MASC_NR_DOCUMENTO
             ,DOCM_NR_DOCUMENTO
        FROM
            SAD_TB_MOFA_MOVI_FASE
            INNER JOIN OCS_TB_PMAT_MATRICULA
                ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
            INNER JOIN SAD_TB_VPPF_VINC_PRO_PRO_FASE
                ON MOFA_ID_MOVIMENTACAO = VPPF_ID_MOVIMENTACAO
                AND MOFA_DH_FASE = VPPF_DH_FASE
                " . $completaFase . "
            INNER JOIN SAD_TB_VIPD_VINC_PROC_DIGITAL
                ON VPPF_ID_VINCULACAO_PROCESSO = VIPD_ID_VINCULACAO_PROCESSO
            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                ON VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI_ID_PROCESSO_DIGITAL
                OR VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI_ID_PROCESSO_DIGITAL
            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                ON PRDI_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
            INNER JOIN SAD_TB_DOCM_DOCUMENTO
                ON DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
        WHERE
           (
               --PEGA OS PROCESSOS PAIS CASO SEJA UMA FASE DE APENSAÇÃO, DESAPENSAÇÃO, VINCULAÇÃO OU DESVINCULAÇÃO
               VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI_ID_PROCESSO_DIGITAL
               AND VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
               " . (!is_null($processoFilho) ? 'AND VIPD_ID_PROCESSO_DIGITAL_PRINC = ' . $processoFilho['PRDI_ID_PROCESSO_DIGITAL'] : '') . "
               AND MOFA_ID_FASE IN (" . Trf1_Sisad_Definicoes::FASE_APENSAR_PROCESSO_PROCESSO . "," . Trf1_Sisad_Definicoes::FASE_DESAPENSAR_PROCESSO_PROCESSO . "," . Trf1_Sisad_Definicoes::FASE_DESVINCULAR_PROCESSO_PROCESSO . "," . Trf1_Sisad_Definicoes::FASE_VINCULA_PROCESSO_PROCESSO . ")
           )
           OR
           (
               --PEGA OS PROCESSOS FILHOS
               VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI_ID_PROCESSO_DIGITAL
               AND VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
               " . (!is_null($processoFilho) ? 'AND VIPD_ID_PROCESSO_DIGITAL_VINDO = ' . $processoFilho['PRDI_ID_PROCESSO_DIGITAL'] : '') . "
           )
           $completaFaseDesejada";

        return $this->_db->fetchAll($sql);
    }

}