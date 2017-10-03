<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_JuntadaDocumentoProcesso
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre qualquer tipo de documentos e processos
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
class Trf1_Sisad_Negocio_JuntadaDocumentoProcesso {

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
     * Verifica se o documento é valido para a juntada de documentos a processos administrativos
     * 
     * - Documentos são tipo != 152(processo administrativo)?
     *      - SIM
     * 		- Documento é divulgado?
     * 			- SIM
     * 				- PERMITE DOCUMENTO (Cópia Anexada)
     * 				
     * 			- NÃO
     * 				- Documento é público OU usuário logado possui vistas ao documento?
     * 					- SIM
     * 						- Documento está com movimentação individual finalizada?
     * 							- SIM
     * 								- PERMITE DOCUMENTO(Cópia Anexada)
     * 								
     * 							- NÃO
     * 								- Documento está NA CAIXA ATUAL DA SESSION do usuário logado?
     * 									- SIM
     * 										- PERMITE DOCUMENTO(Original Anexado)
     * 										
     * 									- NÃO //Usuário deve solicitar o encaminhamento do documento pois o mesmo encontra-se em tramitação em outra caixa.
     * 										- UNSET NO ARRAY
     * 										- ADD MENSAGEM DE NEGAÇÃO INFORMANDO A CAIXA QUE O DOCUMENTO ENCONTRA-SE TAMBÉM
     * 					- NÃO //Documento não é permitido(não público ou não vistas) ao usuário logado
     * 						- UNSET NO ARRAY
     * 						- ADD MENSAGEM DE NEGAÇÃO solicitar vistas ao documento
     * 						- Se o documento for sigiloso não poderá solicitar o documento, somente vistas (item anterior)
     *      - NÃO //Não pode anexar um processo à outro processo por esta funcionalidade
     * 		- UNSET NO ARRAY
     * 		- ADD MENSAGEM DE NEGAÇÃO
     * 
     * @param array $documento
     * @return array 
     */
    public function validaDocumento($documento) {
        $rn_documento = new Trf1_Sisad_Negocio_Documento();
        //Se documento não é processos administrativos
        if ($documento['DTPD_ID_TIPO_DOC'] != Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //Se documento é divulgado
            //Trocar validação quando entrar esquema de divulgação
            if ($rn_documento->isDivulgado($documento['DOCM_ID_DOCUMENTO'])) {
                //NUNCA VAI CAIR AQUI ENQUANTO NÃO FOR IMPLEMENTADO
                //A DIVULGAÇÃO DE DOCUMENTOS
                //
                //PERMITE DOCUMENTO (Cópia Anexada)
                return array('validado' => true, 'STATUS_ANEXO' => 'cópia para anexação');
            } else {
                //Se documento é público OU documento pode ser visto pelo usuário
                $visivel = $rn_documento->isVisivel($documento, $this->_userNs->matricula);
                $documento['CONF_ID_CONFIDENCIALIDADE'] = (isset($documento['DOCM_ID_CONFIDENCIALIDADE']) ? $documento['DOCM_ID_CONFIDENCIALIDADE'] : $documento['CONF_ID_CONFIDENCIALIDADE']);
                if ($documento['CONF_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO || $visivel['tem_vista'] || $visivel['sigiloso'] == 'N') {
                    //Se não possui movimentação individual finalizada
                    //No momento é usado a flag IC_AUTUADO usar a DOCM_IC_MOVI_INDIVIDUAL no futuro
                    if ($documento['DOCM_IC_PROCESSO_AUTUADO'] == 'S') {
                        return array('validado' => true, 'STATUS_ANEXO' => 'cópia para anexação');
                    } else {
                        //plugin para buscar a unidade atual na sessao
                        $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
                        //Se a caixa atual é a mesma que a caixa do documento
                        if ($documento['MODE_SG_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade() && $documento['MODE_CD_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade()) {
                            return array('validado' => true, 'STATUS_ANEXO' => 'original');
                        } else {
                            return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Solicite o encaminhamento do documento. Ele encontra-se na caixa ' . $documento['FAMILIA_DESTINO'] . '.');
                        }
                    }
                } else {
                    return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Documento não visível ao usuário.');
                }
            }
        } else {
            return array('validado' => false, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Não é permitido o uso de processos administrativos.');
        }
    }

    /**
     * Verifica se processo administrativo é valido para esquema de juntada
     * REGRA
     * - O documento é processo administrativo?
      - SIM
      - Documento está NA CAIXA ATUAL DA SESSION do usuário logado?
      - SIM
      - Processo administrativo não está apensado ou possui apensos?
      - SIM
      - Documento é público OU usuário logado possui vistas ao documento?
      - SIM
      - Permite o processo
      - NÃO
      - Elimina o processo
      - NÃO
      - Elimina o processo
      - NÃO
      - Elimina processo.
      - NÃO
      - Elimina processo.
     * 
     * @param array $documento
     * @param boolean $aceitaApensos
     * @return array
     */
    public function validaProcesso($documento, $aceitaApensos = false) {
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            $rn_documento = new Trf1_Sisad_Negocio_Documento();
            $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
            //verifica se o processo está na caixa
            if ($documento['MODE_SG_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade() && $documento['MODE_CD_SECAO_UNID_DESTINO'] == $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade()) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $processo = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
                $processo = array_merge($processo, $documento);
                $juntadaProcessoProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
                $isApensado = $juntadaProcessoProcesso->isApensado($processo);
                // Se o processo administrativo não está apensado em outro processo ou possui processos apensados
                if (!$isApensado || $aceitaApensos == true) {
                    //Se processo é público OU processo pode ser visto pelo usuário
                    $visivel = $rn_documento->isVisivel($processo, $this->_userNs->matricula);
                    //trata a coluna de confidencialidade caso venha como DOCM_ID_CONFIDENCIALIDADE passa para CONF_ID_CONFIDENCIALIDADE
                    //e se vier CONF_ID_CONFIDENCIALIDADE continua sendo ela mesmo
                    $documento['CONF_ID_CONFIDENCIALIDADE'] = (isset($documento['DOCM_ID_CONFIDENCIALIDADE']) ? $documento['DOCM_ID_CONFIDENCIALIDADE'] : $documento['CONF_ID_CONFIDENCIALIDADE']);
                    if ($documento['CONF_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO || $visivel['tem_vista'] || $visivel['sigiloso'] == 'N') {
                        return array('validado' => true, 'PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL' => 0);
                    } else {
                        return array('validado' => false, 'PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL' => 0, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Processo administrativo não visível ao usuário.');
                    }
                } else {
                    //processo não validado pois é um apenso e a função foi configurada para não aceitar apensos flag $aceitaApensos
                    //informa o id do processo pai pelo index PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL
                    return array(
                        'validado' => false
                        , 'PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL' => $isApensado['VIPD_ID_PROCESSO_DIGITAL_PRINC']
                        , 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': O processo está apensado, utilize apenas o processo principal na juntada.'
                    );
                }
            } else {
                return array('validado' => false, 'PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL' => 0, 'mensagem' => 'Processo administrativo ' . $documento['MASC_NR_DOCUMENTO'] . ': Solicite o encaminhamento do documento. Ele encontra-se na caixa ' . $documento['FAMILIA_DESTINO'] . '.');
            }
        } else {
            return array('validado' => false, 'PRDI_ID_PROCESSO_DIGITAL_PRINCIPAL' => 0, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': É apenas permitido o uso de processos administrativos principais para esta funcionalidade.');
        }
    }

    /**
     * Junta DOCUMENTO a PROCESSO
     *
     * @param	array	$processo	
     * @param	array	$documento	
     * @param	array	$arrayVinculo	
     * @param	string	$dataJuntada	
     * @param	array	$dataJuntada	
     * @param	boolean	$fase	
     * @return	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function juntar(array $processo
    , array $documento
    , array $arrayVinculo
    , $dataJuntada
    , $fase
    , $autoCommit = true) {
        $validacao = array();
        try {
            if ($autoCommit) {
                $this->_db->beginTransaction();
            }

            /* Busca o processo pelo id do documento */
            $trf1_Sisad_Negocio_Processo = new Trf1_Sisad_Negocio_Processo();
            $dadosProcesso = $trf1_Sisad_Negocio_Processo->getProcessoPorIdDocumento($processo['DOCM_ID_DOCUMENTO']);

            $dadosJuntada = array(
                'DCPR_ID_PROCESSO_DIGITAL' => $dadosProcesso['PRDI_ID_PROCESSO_DIGITAL']
                , 'DCPR_ID_DOCUMENTO' => $documento['DOCM_ID_DOCUMENTO']
                , 'DCPR_ID_MOVIMENTACAO' => $documento['MOFA_ID_MOVIMENTACAO']
                , 'DCPR_DH_FASE' => new Zend_Db_Expr("TO_DATE('$dataJuntada','dd/mm/YY HH24:MI:SS')")
                , 'DCPR_ID_TP_VINCULACAO' => $arrayVinculo['id']
                , 'DCPR_DH_VINCULACAO_DOC' => new Zend_Db_Expr("TO_DATE('$dataJuntada','dd/mm/YY HH24:MI:SS')")
                , 'DCPR_IC_ATIVO' => 'S');

            //Verificar se o documento a ser juntado é um documento original ou é uma cópia juntada.
            //STATUS_ANEXO VEM DA FUNÇÃO validaDocumento()
            if ($documento['STATUS_ANEXO'] == 'original') {
                //É um documento original juntado ao processo
                $dadosJuntada['DCPR_IC_ORIGINAL'] = 'S';
                $dadosAutuacao = array(
                    'DOCM_IC_PROCESSO_AUTUADO' => 'S'
                    , 'DOCM_IC_MOVI_INDIVIDUAL' => 'N'
                );
                /* Seta para autuado */
                Trf1_Sisad_Negocio_Documento::alterar($documento['DOCM_ID_DOCUMENTO'], $dadosAutuacao);
            } else {
                //É uma cópia juntada ao processo
                $dadosJuntada['DCPR_IC_ORIGINAL'] = 'N';
            }

            /* Junta */
            $sadTbDcprDocumentoProcesso = new Application_Model_DbTable_Sisad_SadTbDcprDocumentoProcesso();
            $sadTbDcprDocumentoProcesso->createRow($dadosJuntada)
                    ->save();

            //auditar a juntada
            $this->auditar('DCPR', Trf1_Sisad_Definicoes::AUDITORIA_INSERIR, null, null, $dadosJuntada);
            //guarda os dados no historico de juntada
            //$this->registraHistorico($dadosProcesso['PRDI_ID_PROCESSO_DIGITAL'], $documento['DOCM_ID_DOCUMENTO'], $fase);
            if ($autoCommit) {
                $this->_db->commit();
            }


            $validacao['validado'] = true;
            $validacao['mensagem'] = "{$arrayVinculo['nome']}: Documento({$documento['MASC_NR_DOCUMENTO']}) juntado ao processo({$processo['MASC_NR_DOCUMENTO']}).";
        } catch (Zend_Exception $exception) {
            if (!is_bool(strpos($exception->getMessage(), 'ORA-00001'))) {
                $validacao['validado'] = false;
                $validacao['mensagem'] = "{$arrayVinculo['nome']}: Documento({$documento['MASC_NR_DOCUMENTO']}) já juntado ao processo({$processo['MASC_NR_DOCUMENTO']}).";
            } else {
                $validacao['validado'] = false;
                $validacao['mensagem'] = $arrayVinculo['nome'] . ': Documento(' . $documento['MASC_NR_DOCUMENTO'] . ') ao Processo(' . $processo['MASC_NR_DOCUMENTO'] . '): ' . $exception->getMessage();
            }

            if ($autoCommit) {
                $this->_db->rollBack();
            }
        }
        return $validacao;
    }

    public function juntarVarios(array $dataRequest, $tipoRelacao) {
        $service_tipo = new Services_Sisad_Tipo();
        $rn_juntadaProcessoProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
        $service_documento = new Services_Sisad_Documento();
        $tiposJuntada = $service_tipo->getTipoJuntada($tipoRelacao, $dataRequest['TP_VINCULO']);
        $validacao = array();

        $qtdJuntados = 0;

        if ($tiposJuntada) {

            $qtd = count($dataRequest['documentoPrincipal']);
            for ($i = 0; $i < $qtd; $i++) {

                $zend_date = new Zend_Date(null, 'dd/MM/YY HH:mm:ss');
                $this->_dateTime = $zend_date->get(Zend_Date::DATETIME);

                $processo_json = $dataRequest['documentoPrincipal'][$i];
                $processo = Zend_Json::decode($processo_json);
                $apensos = $rn_juntadaProcessoProcesso->getProcessosApensadosAtivos($processo, false);
                if (count($apensos) > 0) {
                    //inclui no final do array de documentos principais os processos apensados
                    foreach ($apensos as $value) {
                        $aux = $service_documento->getDocumento($value['DOCM_ID_DOCUMENTO']);
                        $value = array_merge($value, $aux);
                        $dataRequest['documentoPrincipal'][] = Zend_Json::encode($value);
                    }
                    $qtd += count($apensos);
                }
                //valida o processo mas aceita os filhos
                $verificaProcesso = $this->validaProcesso($processo, true);

                if ($verificaProcesso['validado']) {
                    try {
                        $this->_db->beginTransaction();
                        /* CRIA A FASE PRIMEIRO POR QUE ELA SERÁ REFERENCIADA NA TABELA DE JUNTADA. */
                        $arrayFase = array(
                            'MOFA_ID_MOVIMENTACAO' => $processo['MOFA_ID_MOVIMENTACAO']
                            , 'MOFA_ID_FASE' => Trf1_Sisad_Negocio_Fase::getFaseJuntada($tipoRelacao, $dataRequest['TP_VINCULO'])
                            , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                            , 'MOFA_DH_FASE' => new Zend_Db_Expr("TO_DATE('$this->_dateTime','dd/mm/YY HH24:MI:SS')")
                            , 'MOFA_DS_COMPLEMENTO' => $dataRequest['MOFA_DS_COMPLEMENTO']);
                        //lança a fase da juntada
                        Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
                        foreach ($dataRequest['documentoVinculacao'] as $documento_v) {

                            $documento = Zend_Json::decode($documento_v);

                            //VERIFICA SE O DOCUMENTO É VALIDO E PEGA O STATUS DE ORIGINAL OU CÓPIA PARA JUNTADA
                            $verificaDocumento = $this->validaDocumento($documento);
                            $documento = array_merge($documento, $verificaDocumento);
                            //une o status ao documento
                            if ($verificaDocumento['validado']) {
                                $resultado = $this->juntar($processo, $documento, $tiposJuntada, $this->_dateTime, $arrayFase, false);
                                //pega as vistas do processo e insere no documento
                                $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();

                                $rn_ParteVistas->passaParteVista($processo, $documento, Trf1_Sisad_Definicoes::PARTE_VISTA, false);
                                $validacao[] = $resultado;
                                if ($resultado['validado']) {
                                    $qtdJuntados++;
                                }
                            } else {
                                $validacao[] = $verificaDocumento;
                            }
                        }// FIM foreach($dataRequest['documentoVinculacao'] as $documento_v)
                        if ($qtdJuntados > 0) {
                            $this->_db->commit();
                        } else {
                            $this->_db->rollBack();
                        }
                    } catch (Zend_Exception $exception) {
                        //erro ao lançar a fase
                        $validacao[] = array('validado' => false, 'mensagem' => 'Erro com o documento ' . $processo['MASC_NR_DOCUMENTO'] . ': ' . $exception->getMessage());
                        $this->_db->rollBack();
                    }
                } else {
                    $validacao[] = $verificaProcesso;
                }
            }
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
     * @param int $idProcesso
     * @param int $idDocumento
     * @param array $dadosNovos
     */
    public function auditar($shortName, $acao, $idProcesso, $idDocumento, $dadosNovos = null) {
        $tb_auditoria = new Application_Model_DbTable_Sisad_SadTbDcprAuditoria();
        $sadTbDcprDocumentoProcesso = new Application_Model_DbTable_Sisad_SadTbDcprDocumentoProcesso();
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
        } elseif ($acao == Trf1_Sisad_Definicoes::AUDITORIA_EXCLUIR) {

            $data_audit[$shortName . '_IC_OPERACAO'] = $acao;
            $dadosRow = $sadTbDcprDocumentoProcesso->fetchRow(
                            'DCPR_ID_PROCESSO_DIGITAL = ' . $idProcesso
                            . ' AND DCPR_ID_DOCUMENTO = ' . $idDocumento
                    )->toArray();
            foreach ($dadosRow as $key => $value) {
                $data_audit['OLD_' . $key] = $value;
            }
        } elseif ($acao == Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR) {

            $data_audit[$shortName . '_IC_OPERACAO'] = $acao;

            $dadosRow = $sadTbDcprDocumentoProcesso->fetchRow(
                            'DCPR_ID_PROCESSO_DIGITAL = ' . $idProcesso
                            . ' AND DCPR_ID_DOCUMENTO = ' . $idDocumento
                    )->toArray();

            foreach ($dadosRow as $key => $value) {
                $data_audit['OLD_' . $key] = $value;
                $data_audit['NEW_' . $key] = $dadosNovos[$key];
            }
        }
        $tb_auditoria->createRow($data_audit)
                ->save();
    }

    public function registraHistorico($idProcesso, $idDocumento, $fase) {
        $dados = array(
            'VDPF_ID_PROCESSO_DIGITAL' => $idProcesso
            , 'VDPF_ID_DOCUMENTO' => $idDocumento
            , 'VDPF_ID_MOVIMENTACAO' => $fase['MOFA_ID_MOVIMENTACAO']
            , 'VDPF_DH_FASE' => $fase['MOFA_DH_FASE']
        );

        $sadTbVdpfVincDocProFase = new Application_Model_DbTable_Sisad_SadTbVdpfVincDocProFase();
        $sadTbVdpfVincDocProFase->createRow($dados)->save();
    }

    /**
     * Verifica se o documento está anexado a um processo administrativo
     * 
     * @param array $documento
     * @param boolean $flagAtivo
     * @return boolean
     */
    public function isAnexado($documento, $flagAtivo = true) {
        $sadTbDcprDocumentoProcesso = new Application_Model_DbTable_Sisad_SadTbDcprDocumentoProcesso();
        $qtd = $sadTbDcprDocumentoProcesso->fetchAll(
                        '   DCPR_ID_DOCUMENTO = ' . $documento['DOCM_ID_DOCUMENTO']
                        . ($flagAtivo ? " AND DCPR_IC_ATIVO = 'S'" : '')
                        . ' AND DCPR_ID_TP_VINCULACAO = ' . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR
                )->count();
        return ($qtd > 0);
    }

}