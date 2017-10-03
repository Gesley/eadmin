<?php

class Application_Model_DbTable_SadTbPrdiProcessoDigital extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_PRDI_PROCESSO_DIGITAL';
    protected $_primary = array('PRDI_ID_PROCESSO_DIGITAL');
    protected $_sequence = 'SAD_SQ_PRDI';

    public function autuarProcesso(array $dataDocmDocumento, array $dataPrdiProcessoDigital, array $dataDocumentos_vicular) {
        /**
         * Autua Processo
         * Com ou sem troca de nível.
         */
        $Dual = new Application_Model_DbTable_Dual();
        $datahora = $Dual->sysdate();


        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();

        $dataDocmDocumento["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($dataDocmDocumento['DOCM_SG_SECAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'], $dataDocmDocumento['DOCM_ID_TIPO_DOC']);

        $dataDocmDocumento["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO(
                $dataDocmDocumento['DOCM_SG_SECAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_GERADORA'], $dataDocmDocumento['DOCM_ID_TIPO_DOC'], $dataDocmDocumento['DOCM_NR_SEQUENCIAL_DOC']);

        /* ---------------------------------------------------------------------------------------- */
        /* Primeira tabela a ser inserida */

        Zend_Debug::dump($dataDocmDocumento);
        unset($dataDocmDocumento["DOCM_ID_DOCUMENTO"]);
//            $dataDocmDocumento["DOCM_NR_DOCUMENTO"] = ;
//            $dataDocmDocumento["DOCM_NR_SEQUENCIAL_DOC"] = ;
//            unset($dataDocmDocumento["DOCM_NR_DCMTO_USUARIO"]);
        $dataDocmDocumento["DOCM_DH_CADASTRO"] = $datahora;
//            $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = ;
//            $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = ;
//            $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = ;
//            $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = ;
//            $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = ;
//            $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = ;
//            $dataDocmDocumento["DOCM_ID_PCTT"] = ;
//            $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] =  ;
//            $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = ;
//            $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = ;
//              $dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"];
        $dataSadTbDocmDocumento["DOCM_IC_PROCESSO_AUTUADO"] = 'N';
        Zend_Debug::dump($dataDocmDocumento);
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $rowDocmDocumento = $tabelaSadTbDocmDocumento->createRow($dataDocmDocumento);
        $idDocmDocumento = $rowDocmDocumento->save();
        Zend_Debug::dump($idDocmDocumento, "Id do documento.");
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
        //$dataPrdiProcessoDigital =  $SadTbPrdiProcessoDigital->fetchNew()->toArray();

        Zend_Debug::dump($dataPrdiProcessoDigital);

        unset($dataPrdiProcessoDigital["PRDI_ID_PROCESSO_DIGITAL"]);
        /*         * **************temporário até a sequence */
//            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//            $stmt = $db->query("SELECT NVL(MAX(PRDI_ID_PROCESSO_DIGITAL)+1,0) PRDI_ID_PROCESSO_DIGITAL
//                                FROM SAD_TB_PRDI_PROCESSO_DIGITAL");
//            $temp = $stmt->fetch();          
//            $dataPrdiProcessoDigital["PRDI_ID_PROCESSO_DIGITAL"] = $temp["PRDI_ID_PROCESSO_DIGITAL"];
        /*         * **************temporário até a sequence */


        $dataPrdiProcessoDigital["PRDI_DH_AUTUACAO"] = $datahora;
//            $dataPrdiProcessoDigital["PRDI_CD_MATR_AUTUADOR"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_SG_SECAO_AUTUADORA"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_CD_UNID_AUTUADORA"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_CD_MATR_SERV_RELATOR"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_ID_AQVP"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_DS_TEXTO_AUTUACAO"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_CD_JUIZ_RELATOR_PROCESSO"] = INFORMAR;
//            //$dataPrdiProcessoDigital["PRDI_DH_DISTRIBUICAO"] = INFORMAR;
//            //$dataPrdiProcessoDigital["PRDI_CD_MATR_DISTRIBUICAO"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_IC_TP_DISTRIBUICAO"] = INFORMAR;
//            $dataPrdiProcessoDigital["PRDI_IC_SIGILOSO"] = INFORMAR;
        $dataPrdiProcessoDigital["PRDI_IC_CANCELADO"] = 'N';
        Zend_Debug::dump($dataPrdiProcessoDigital);

        $rowProcessoDigital = $SadTbPrdiProcessoDigital->createRow($dataPrdiProcessoDigital);
        $idProcessoDigital = $rowProcessoDigital->save();
        Zend_Debug::dump($idProcessoDigital, "Id do documento.");

        /* ---------------------------------------------------------------------------------------- */

        /**
         * Encaminha o processo para caixa da unidade
         * 
         */
        /* primeira tabela */
        $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        //$dataMoviMovimentacao =  $SadTbMoviMovimentacao->fetchNew()->toArray();
        $dataMoviMovimentacao = array();

        Zend_Debug::dump($dataMoviMovimentacao);
        unset($dataMoviMovimentacao["MODO_ID_MOVIMENTACAO"]);
        $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $datahora;
        $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dataPrdiProcessoDigital["PRDI_SG_SECAO_AUTUADORA"];
        $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dataPrdiProcessoDigital["PRDI_CD_UNID_AUTUADORA"];
        $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $dataPrdiProcessoDigital["PRDI_CD_MATR_AUTUADOR"];
        unset($dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"]);
        Zend_Debug::dump($dataMoviMovimentacao);
        // exit;
        $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
        $idMoviMovimentacao = $rowMoviMovimentacao->save();
        Zend_Debug::dump($idMoviMovimentacao, "id da movimentacao");
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* segunda tabela */
        $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
        //$dataModoMoviDocumento =  $SadTbModoMoviDocumento->fetchNew()->toArray();
        $dataModoMoviDocumento = array();

        Zend_Debug::dump($dataModoMoviDocumento);
        $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $idDocmDocumento;
        Zend_Debug::dump($dataModoMoviDocumento);

        $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
        $rowModoMoviDocumento->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* terceira tabela */
        $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
        //$dataModeMoviDestinatario=  $SadTbModeMoviDestinatario->fetchNew()->toArray();
        $dataModeMoviDestinatario = array();

        Zend_Debug::dump($dataModeMoviDestinatario);
        $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dataPrdiProcessoDigital["PRDI_SG_SECAO_AUTUADORA"];
        $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dataPrdiProcessoDigital["PRDI_CD_UNID_AUTUADORA"];
        $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
        unset($dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"]);
        unset($dataModeMoviDestinatario["MODE_DH_RECEBIMENTO"]);
        unset($dataModeMoviDestinatario["MODE_CD_MATR_RECEBEDOR"]);
        Zend_Debug::dump($dataModeMoviDestinatario);

        $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
        $rowModeMoviDestinatario->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* quarta tabela */
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
        $dataMofaMoviFase = array();
        Zend_Debug::dump($dataMofaMoviFase);
        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
        /**
         * 1020 AUTUAÇÃO DE PROCESSO ADMINISTRATIVO
         */
        $dataMofaMoviFase["MOFA_ID_FASE"] = 1020;
        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $dataPrdiProcessoDigital["PRDI_CD_MATR_AUTUADOR"];
        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "AUTUAÇÃO DE PROCESSO ADMINISTRATIVO";
        Zend_Debug::dump($dataMofaMoviFase);

        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
        $rowMofaMoviFase->save();
        /* ---------------------------------------------------------------------------------------- */

        /*
         * Vincula um documento a um processo
         */
        /* ---------------------------------------------------------------------------------------- */
        $SadTbDcprDocumentoProcesso = new Application_Model_DbTable_SadTbDcprDocumentoProcesso();
        //$dataPrdiProcessoDigital =  $SadTbPrdiProcessoDigital->fetchNew()->toArray();

        $dataDcprDocumentoProcesso = array();
        Zend_Debug::dump($dataDcprDocumentoProcesso);
        $dataDcprDocumentoProcesso["DCPR_ID_PROCESSO_DIGITAL"] = $idProcessoDigital;
        $dataDcprDocumentoProcesso["DCPR_ID_DOCUMENTO"] = $idDocmDocumento;
        $dataDcprDocumentoProcesso["DCPR_ID_TP_VINCULACAO"] = Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR;
        //$dataDcprDocumentoProcesso["DCPR_ID_MOVIMENTACAO"] = $datahora;
        $dataDcprDocumentoProcesso["DCPR_DH_VINCULACAO_DOC"] = $datahora;
        $dataDcprDocumentoProcesso["DCPR_IC_ATIVO"] = 'S';
        $dataDcprDocumentoProcesso["DCPR_IC_ORIGINAL"] = 'S';
        Zend_Debug::dump($dataDcprDocumentoProcesso);

        $rowDocumentoProcesso = $SadTbDcprDocumentoProcesso->createRow($dataDcprDocumentoProcesso);
        $rowDocumentoProcesso->save();

        $rn_JuntadaDocumentoProcesso = new Trf1_Sisad_Negocio_JuntadaDocumentoProcesso();
        $rn_JuntadaDocumentoProcesso->auditar('DCPR', Trf1_Sisad_Definicoes::AUDITORIA_INSERIR, null, null, $dataDcprDocumentoProcesso);

        //$rn_JuntadaDocumentoProcesso->registraHistorico($idProcessoDigital, $idDocmDocumento, $fase);
        /* ---------------------------------------------------------------------------------------- */

        foreach ($dataDocumentos_vicular as $documento_vicular) {
            /**
             * Vinculando os documentos ao processo
             */
            Zend_Debug::dump($dataDcprDocumentoProcesso, 'antes de vincular');
            $dataDcprDocumentoProcesso["DCPR_ID_PROCESSO_DIGITAL"] = $idProcessoDigital;
            $dataDcprDocumentoProcesso["DCPR_ID_DOCUMENTO"] = $documento_vicular["DOCM_ID_DOCUMENTO"];
            $dataDcprDocumentoProcesso["DCPR_DH_VINCULACAO_DOC"] = $datahora;
            Zend_Debug::dump($dataDcprDocumentoProcesso, 'depois de popular');
            $rowDocumentoProcesso = $SadTbDcprDocumentoProcesso->createRow($dataDcprDocumentoProcesso);
            $rowDocumentoProcesso->save();

            /**
             * Setando os documentos como autuados 
             */
            $dataDocumentos_vicular["DOCM_IC_PROCESSO_AUTUADO"] = "S";
            $dataDocumentos_vicular["DOCM_IC_MOVI_INDIVIDUAL"] = "N";
            $rowDocmDocumento_vincular = $tabelaSadTbDocmDocumento->find($documento_vicular["DOCM_ID_DOCUMENTO"])->current();
            ;
            $rowDocmDocumento_vincular->setFromArray($dataDocumentos_vicular);
            Zend_Debug::dump($rowDocmDocumento_vincular->toArray());
            $rowDocmDocumento_vincular->save();
        }

        $retorno = array(
            'PRDI_ID_PROCESSO_DIGITAL' => $idProcessoDigital,
            'DOCM_ID_DOCUMENTO' => $idDocmDocumento
        );

        return $retorno;
    }

    public function addDocsProcesso(array $dadosProcesso, array $dataDocumentos_vicular) {
        /**
         * Autua Processo
         * Com ou sem troca de nível.
         */
        $Dual = new Application_Model_DbTable_Dual();
        $userNs = new Zend_Session_Namespace('userNs');
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $SadTbDcprDocumentoProcesso = new Application_Model_DbTable_SadTbDcprDocumentoProcesso();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();

        $datahora = $Dual->sysdate();
        foreach ($dadosProcesso["processo"] as $processos) {
            $dataprocesso = Zend_Json::decode($processos);
            $idDocmDocumentoProc = $dataprocesso["DOCM_ID_DOCUMENTO"];
            $idMoviMovimentacao = $dataprocesso["MOFA_ID_MOVIMENTACAO"];

            /**
             * Recupera o id do processo digital
             */
            $dataPrdiProcessoDigital = $this->getProcesso($idDocmDocumentoProc);
            $idProcessoDigital = $dataPrdiProcessoDigital["PRDI_ID_PROCESSO_DIGITAL"];

            foreach ($dataDocumentos_vicular as $documento_vicular) {
                /**
                 * Vinculando os documentos ao processo
                 */
                $dataDcprDocumentoProcesso["DCPR_ID_PROCESSO_DIGITAL"] = $idProcessoDigital;
                $dataDcprDocumentoProcesso["DCPR_ID_DOCUMENTO"] = $documento_vicular["DOCM_ID_DOCUMENTO"];
                $dataDcprDocumentoProcesso["DCPR_DH_VINCULACAO_DOC"] = $datahora;

                $rowDocumentoProcesso = $SadTbDcprDocumentoProcesso->createRow($dataDcprDocumentoProcesso);
                $rowDocumentoProcesso->save();

                /**
                 * Setando os documentos como autuados 
                 */
                $dadaDocmDocumento["DOCM_IC_PROCESSO_AUTUADO"] = "S";
                $rowDocmDocumento_vincular = $tabelaSadTbDocmDocumento->find($documento_vicular["DOCM_ID_DOCUMENTO"])->current();
                ;
                $rowDocmDocumento_vincular->setFromArray($dadaDocmDocumento);
                $rowDocmDocumento_vincular->save();
            }
            /* ---------------------------------------------------------------------------------------- */
            /* quarta tabela */
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $dadosProcesso["obs"];
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1023; //1023 ADIÇÃO DE DOCUMENTOS A PROCESSO
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /* ---------------------------------------------------------------------------------------- */
        }
    }

    public function getdocsProcesso($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM_ID_TP_EXTENSAO,
                                       LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA,
                                       LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
                                       LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,
                                       RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
                                       RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
                                       TO_CHAR(DCPR.DCPR_DH_VINCULACAO_DOC,'DD/MM/YYYY HH24:MI:SS') DCPR_DH_VINCULACAO_DOC,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       PRDI_ID_PROCESSO_DIGITAL ID_PROCESSO,
                                       CONF.CONF_ID_CONFIDENCIALIDADE,
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                            ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                            ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                            ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                            INNER JOIN SAD_TB_TPSD_TIPO_SITUACAO_DOC TPSD
                            ON DOCM.DOCM_ID_TIPO_SITUACAO_DOC = TPSD.TPSD_ID_TIPO_SITUACAO_DOC
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                            ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                            ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                            AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                            ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                            ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            WHERE PRDI_ID_PROCESSO_DIGITAL IN 
                            (
                            SELECT PRDI_ID_PROCESSO_DIGITAL
                            FROM SAD_TB_DOCM_DOCUMENTO
                                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                WHERE DOCM_ID_DOCUMENTO = $idDocumento
                            )
                            AND DOCM_ID_DOCUMENTO <> $idDocumento
                            ORDER BY TO_DATE(DCPR_DH_VINCULACAO_DOC,'dd/mm/yyyy HH24:MI:SS') DESC");
        return $stmt->fetchAll();
    }

    /*
     * Retorna o id do processo digital
     * @param int $idDocumento - id do documento
     * 
     * @return int $idProcesso 
     */

    public function getProcesso($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PRDI_ID_PROCESSO_DIGITAL,
                                   PRDI_DS_TEXTO_AUTUACAO
                            FROM SAD_TB_DOCM_DOCUMENTO
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                            ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                            ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                            WHERE DOCM_ID_DOCUMENTO = $idDocumento");
        return $stmt->fetch();
    }

    /*
     * Retorna os dados do processo passando como parâmetro um documento
     */

    public function getProcessosDocumento($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA,
                                       LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
                                       LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,
                                       RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
                                       RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
                                       TO_CHAR(DCPR.DCPR_DH_VINCULACAO_DOC,'DD/MM/YYYY HH24:MI:SS') DCPR_DH_VINCULACAO_DOC,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       PRDI_ID_PROCESSO_DIGITAL ID_PROCESSO,
                                       CONF.CONF_ID_CONFIDENCIALIDADE
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM
                                 INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                 ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                 ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                 INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                 ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                 INNER JOIN SAD_TB_TPSD_TIPO_SITUACAO_DOC TPSD
                                 ON DOCM.DOCM_ID_TIPO_SITUACAO_DOC = TPSD.TPSD_ID_TIPO_SITUACAO_DOC
                                 INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                 ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                                 INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                 ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                 AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                                 INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                 ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                 INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                 ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            WHERE DOCM_ID_TIPO_DOC = 152 --PROCESSO
                            AND DOCM_ID_DOCUMENTO <> $idDocumento
                            AND DCPR_ID_PROCESSO_DIGITAL IN (SELECT DCPR_ID_PROCESSO_DIGITAL
                                                            FROM SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                                            WHERE DCPR_ID_DOCUMENTO = $idDocumento)");
        return $stmt->fetchAll();
    }

    public function getDadosJuntadaDocumentoProcesso($idDocumento) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM.DOCM_ID_DOCUMENTO
                                    , DOCM.DOCM_NR_DOCUMENTO
                                    , DOCM_PROC.DOCM_ID_DOCUMENTO AS DOCM_ID_DOCUMENTO_PROC
                                    , DOCM_PROC.DOCM_NR_DOCUMENTO AS DOCM_NR_DOCUMENTO_PROC
                                    , DCPR_DOC.DCPR_ID_PROCESSO_DIGITAL
                                    , TO_CHAR(DCPR_DOC.DCPR_DH_VINCULACAO_DOC,'DD/MM/YYYY HH24:MI:SS') AS DCPR_DH_VINCULACAO_DOC
                                    , PMAT_PROC.PMAT_CD_MATRICULA
                                    , PNAT_PROC.PNAT_NO_PESSOA
                                    , MOFA_JUNTADA.MOFA_DS_COMPLEMENTO
                                    , MOFA_JUNTADA.MOFA_ID_MOVIMENTACAO
                              FROM
                                  /* DADOS DO DOCUMENTO */
                                  SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_DOC
                                  INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                      ON DCPR_DOC.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                      AND DCPR_DOC.DCPR_ID_DOCUMENTO = $idDocumento
                                      AND DOCM.DOCM_ID_TIPO_DOC != 152
                                  /* DADOS DO PROCESSO */
                                  INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_PROC
                                      ON DCPR_PROC.DCPR_ID_PROCESSO_DIGITAL = DCPR_DOC.DCPR_ID_PROCESSO_DIGITAL
                                  INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_PROC
                                      ON DCPR_PROC.DCPR_ID_DOCUMENTO = DOCM_PROC.DOCM_ID_DOCUMENTO
                                      AND DOCM_PROC.DOCM_ID_TIPO_DOC = 152
                                  /* DADOS DA JUNTADA */
                                  INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_JUNTADA
                                      ON MOFA_JUNTADA.MOFA_DH_FASE = DCPR_DOC.DCPR_DH_VINCULACAO_DOC -- do documento pois é a data/hora da juntada
                                      AND MOFA_JUNTADA.MOFA_ID_FASE IN (1023,1020) ------------------------------- colocar a contante da fase adição de documento a processo
                                  INNER JOIN OCS_TB_PMAT_MATRICULA PMAT_PROC
                                      ON MOFA_JUNTADA.MOFA_CD_MATRICULA = PMAT_PROC.PMAT_CD_MATRICULA
                                  INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT_PROC
                                      ON PMAT_PROC.PMAT_ID_PESSOA = PNAT_PROC.PNAT_ID_PESSOA
                                  INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_DOC
                                      ON MOFA_JUNTADA.MOFA_ID_MOVIMENTACAO = MODO_DOC.MODO_ID_MOVIMENTACAO
                                      AND MODO_DOC.MODO_ID_DOCUMENTO = DOCM_PROC.DOCM_ID_DOCUMENTO");
        return $stmt->fetchAll();
    }

    public function setExcluirDocsProc($dcprDocProcesso) {
        $Dual = new Application_Model_DbTable_Dual();
        $datahora = $Dual->sysdate();
        $userNs = new Zend_Session_Namespace('userNs');
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $SadTbDcprDocumentoProcesso = new Application_Model_DbTable_SadTbDcprDocumentoProcesso();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();

        try {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            $rowExcluirDocPro = $SadTbDcprDocumentoProcesso->find($dcprDocProcesso["DCPR_ID_PROCESSO_DIGITAL"], $dcprDocProcesso["DCPR_ID_DOCUMENTO"])->current();

            $rn_juntadaDocumentoProcesso = new Trf1_Sisad_Negocio_JuntadaDocumentoProcesso();
            $rn_juntadaDocumentoProcesso->auditar('DCPR', Trf1_Sisad_Definicoes::AUDITORIA_EXCLUIR, $dcprDocProcesso["DCPR_ID_PROCESSO_DIGITAL"], $dcprDocProcesso["DCPR_ID_DOCUMENTO"]);

            $rowExcluirDocPro->delete();
            $dadosProcesso = $mapperDocumento->getDadosDCMTO($dcprDocProcesso['DOCM_ID_DOCUMENTO_PRINCIPAL']);
            $dadosDocumento = $mapperDocumento->getDadosDCMTO($dcprDocProcesso['DCPR_ID_DOCUMENTO']);
            $arrayFase = array(
                'MOFA_ID_MOVIMENTACAO' => $dadosProcesso['MOFA_ID_MOVIMENTACAO']
                , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_REMOVER_DOCUMENTO_PROCESSO
                , 'MOFA_CD_MATRICULA' => $userNs->matricula
                , 'MOFA_DH_FASE' => $datahora
                , 'MOFA_DS_COMPLEMENTO' => 'Documento nº ' . $dadosDocumento['DOCM_NR_DOCUMENTO'] . ' desanexado.');
            //lança a fase da juntada para o processo
            Trf1_Sisad_Negocio_Fase::lancaFase($arrayFase);
            

            if ($dcprDocProcesso['MOFA_ID_MOVIMENTACAO'] && $dcprDocProcesso['MOFA_DH_FASE']) {
                $SadTbMofaMoviFase->deleteMovimentacao($dcprDocProcesso['MOFA_ID_MOVIMENTACAO'], $dcprDocProcesso['MOFA_DH_FASE']);
            }

            $qtdDocsPro = $SadTbDcprDocumentoProcesso->getQtdDocsPro($dcprDocProcesso["DCPR_ID_DOCUMENTO"]);
            
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            
            if ($qtdDocsPro["QTD"] <= 1) {
                $dataDocumentos_vicular["DOCM_IC_MOVI_INDIVIDUAL"] = "S";
                $dataDocumentos_vicular["DOCM_IC_PROCESSO_AUTUADO"] = "N";
                $rowDocmDocumento_vincular = $tabelaSadTbDocmDocumento->find($dcprDocProcesso["DCPR_ID_DOCUMENTO"])->current();
                $rowDocmDocumento_vincular->setFromArray($dataDocumentos_vicular);
                $rowDocmDocumento_vincular->save();
            }
            $db->commit();
            return 1;
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            return 0;
        }
    }

}
