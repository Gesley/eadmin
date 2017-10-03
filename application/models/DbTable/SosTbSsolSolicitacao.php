<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_SosTbSsolSolicitacao extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SSOL_SOLICITACAO';
    protected $_primary = array('SSOL_ID_DOCUMENTO');

    public function getHistoricoSolInformacao ($idDoc, $data_enc) {
        $stmt = "
            SELECT
              to_char(MOFA.MOFA_DH_FASE, 'YYYY-MM-DD HH24:MI:SS') as MOFA_DH_FASE,
              MOFA.MOFA_ID_FASE
            FROM
              SAD_TB_DOCM_DOCUMENTO DOCM
              INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                ON DOCM.DOCM_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
              INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI
                ON MODO_MOVI.MODO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO
              INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                ON MOVI.MOVI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
            WHERE DOCM.DOCM_ID_DOCUMENTO = $idDoc
                  AND MOFA.MOFA_ID_FASE IN (1024, 1025)
                  AND MOFA.MOFA_DH_FASE >= to_date('$data_enc', 'YYYY-MM-DD HH24:MI:SS')
            ORDER BY MOFA.MOFA_DH_FASE ASC
        ";
        try {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            return $db->fetchAll($stmt);
        } catch (Zend_Db_Table_Exception $e) {
            Zend_Debug::dump($data_enc);
        }
    }

    public function getCaixaHelpDesk ($idCaixa, $nivel, $order) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(1);
        $stmt .= "," . $CaixasQuerys->whereStatusVideoconferencia();
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
        $stmt .= $CaixasQuerys->whereNivel(true, $nivel);
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($stmt);

//        Zend_Debug::dump($stmt);
//        exit;
        $solics_cx = $stmt->fetchAll();

        //esconde as solicitações filhas vinculadas
        $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");

        return $solics_cx;
    }

    public function getDefeitosSolicitacao ($idMovimentacao) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT A.MDSI_ID_MOVIM_DEF_SISTEMA, 
       A.MDSI_ID_MOVIMENTACAO,
       A.MDSI_ID_TIPO_DEFEITO_SISTEMA, 
       A.MDSI_DS_JUSTIF_DEFEITO,
       
       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MDSI_CD_MATRICULA_INCLUSAO) AS MDSI_CD_MATRICULA_INCLUSAO,  
       TO_CHAR(A.MDSI_DH_INCLUSAO ,'dd/mm/yyyy HH24:MI:SS') AS MDSI_DH_INCLUSAO,
       A.MDSI_IC_CANCELAMENTO, 
       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MDSI_CD_MATRIC_CANCELAMENTO) AS MDSI_CD_MATRIC_CANCELAMENTO,
       TO_CHAR(A.MDSI_DH_CANCELAMENTO ,'dd/mm/yyyy HH24:MI:SS') AS MDSI_DH_CANCELAMENTO,
       A.MDSI_DS_CANCELAMENTO,
       A.MDSI_NR_DEFEITOS
       FROM SOS.SOS_TB_MDSI_MOVIM_DEF_SISTEMA A
       WHERE  A.MDSI_ID_MOVIMENTACAO = $idMovimentacao ORDER BY MDSI_DH_INCLUSAO DESC";

        return $db->query($stmt)->fetchAll();
    }

    public function getCaixaUnidadeCentral ($params, $order) {

        $lot = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
        $cd_lotacao_geradora = trim($lot[2]);
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(1);
        if (substr($params["TRF1_SECAO"], 0, 2) == 'DF') {
            $stmt .= ',' . $CaixasQuerys->whereStatusVideoconferencia();
        }
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();


        /* Atendente */
        $matricula_atendente = explode('-', $params['SSOL_CD_MATRICULA_ATENDENTE']);
        $stmt .= ( $params['SSOL_CD_MATRICULA_ATENDENTE']) ? (" AND SSOL_CD_MATRICULA_ATENDENTE = '" . strtoupper($matricula_atendente[0]) . "' ") : ('');
        /* Unidade fase */
        $stmt .= ( $params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' ") : ('');

        /* Unidade solicitante */
        $stmt .= ( $params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $stmt .= ( $params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $cd_lotacao_geradora . " ") : ('');

        /* Solicitante */
        $matricula_cadastro = explode('-', $params['DOCM_CD_MATRICULA_CADASTRO']);
        $stmt .= ( $params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . strtoupper(trim($matricula_cadastro[0])) . "' ") : ('');

        if (!empty($params['MODE_ID_CAIXA_ENTRADA'])) {
            foreach ($params['MODE_ID_CAIXA_ENTRADA'] as $value) {

                $caixas2 .= $value . ",";
            }
            $caixas2 = substr($caixas2, 0, -1);
            $caixaIdsDefault = (string) $caixas2;
        } else {
            $caixaIdsDefault = "5,6,18,7,8,9,10,11,12,13,14,15,16,17";
        }

        $stmt .= " AND MODE_ID_CAIXA_ENTRADA IN(" . $caixaIdsDefault . ") ";

        $sser_id_servico = explode('|', $params['SSER_ID_SERVICO']);
        //$stmt .= ($params['SSER_ID_SERVICO'])?(" AND SSER_ID_SERVICO = '".strtoupper(trim($sser_id_servico[0]))."' "):('');

        /* Serviço */

        if (is_array($params['SSER_ID_SERVICO'])) {

            foreach ($params['SSER_ID_SERVICO'] as $value) {
                $id_raw = explode('|', $value);
                if (!empty($value)) {
                    $servicos_id .= $id_raw[0] . ",";
                }
            }

            // Retira a utima virgula
            $servicos_id = substr($servicos_id, 0, -1);
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (strlen($servicos_id) > 0) {
                //Concatena os valores separados por vírgula
                //$value_query = implode(',', $servico_ids[]);

                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $servicos_id . ") ") : ('');
            }
        } else {
            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $sser_id_servico[0] . " ") : ('');
        }
        $stmt .= ( $params['SSER_DS_SERVICO']) ? (" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Data de cadastro */
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Data da Ultima fase */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Número da solicitação */
        $stmt .= ( $params['DOCM_NR_DOCUMENTO'] ) ? (" AND DOCM_NR_DOCUMENTO = " . $params['DOCM_NR_DOCUMENTO'] . " ") : ('');

        $stmt .= ( $params['SNAT_CD_NIVEL'] ) ? (" AND SNAT_CD_NIVEL = " . $params['SNAT_CD_NIVEL'] . " ") : ('');
        $stmt .= $CaixasQuerys->ordemCaixa($order);

        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();

        if ($params['SOMENTE_PRINCIPAL'] == 'N') {
            //esconde as solicitações filhas vinculadas
            $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");
        }

        return $solics_cx;
    }

    public function getCaixaSemNivel ($idCaixa, $order) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(2);
        if ($idCaixa == 4) {
            $stmt .= "," . $CaixasQuerys->whereStatusVideoconferencia();
        }
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();

        if ($order == "MOVI_DH_ENCAMINHAMENTO DESC") {
            $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') DESC";
        } else if ($order == "MOVI_DH_ENCAMINHAMENTO ASC") {
            $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') ASC";
        }

        $stmt .= $CaixasQuerys->ordemCaixa($order);

        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();

        //esconde as solicitações filhas vinculadas
        $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");

        return $solics_cx;
    }

    public function getCaixaSemNivelPesq ($idCaixa, $params, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(2);
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->colunasServicosSistemas();
        }
        if ($idCaixa == 4) {
            $stmt .= "," . $CaixasQuerys->whereStatusVideoconferencia();
        }
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->leftJoinServicosSistemas();
        }
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();


        /* Atendente */
        $stmt .= ( $params['SSOL_CD_MATRICULA_ATENDENTE']) ? (" AND SSOL_CD_MATRICULA_ATENDENTE = '" . $params['SSOL_CD_MATRICULA_ATENDENTE'] . "' ") : ('');

        /* Unidade fase */
        $stmt .= ( $params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' ") : ('');
//        $stmt .= ( $params['SOMENTE_PRINCIPAL']=='N') ? (" AND 1 = SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL_ID_DOCUMENTO)"):('');
        /* Unidade solicitante */
        $stmt .= ( $params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $stmt .= ( $params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " ") : ('');

        /* Solicitante */
        $stmt .= ( $params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' ") : ('');
        /* Categorias */
        if (is_array($params['CATE_ID_CATEGORIA'])) {
            //Remove valores vazios da array
            if (array_search("", $params['CATE_ID_CATEGORIA']) !== false) {
                unset($params['CATE_ID_CATEGORIA'][array_search("", $params['CATE_ID_CATEGORIA'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['CATE_ID_CATEGORIA']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['CATE_ID_CATEGORIA']);
                // Retira a utima virgula
                $stmt .= ( $params['CATE_ID_CATEGORIA']) ? (" 
                    AND SSOL_ID_DOCUMENTO IN( " .
                    "(
                    SELECT B.CASO_ID_DOCUMENTO 
                    FROM SOS.SOS_TB_CATE_CATEGORIA A,
                    SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                    WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                    AND A.CATE_ID_CATEGORIA IN ($value_query)
                    AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                    AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                    )"
                    . ") ") : ('');
            }
        }

        /* Serviço */
        if (is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $stmt .= ( $params['SSER_DS_SERVICO']) ? (" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Data de cadastro */
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Data da Ultima fase */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Número da solicitação */
        $docm_nr_documento = $params['DOCM_NR_DOCUMENTO'];
        $stmt .= ($docm_nr_documento) ? ("AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)
                                        AND DOCM_NR_DOCUMENTO LIKE '%'|| SUBSTR($docm_nr_documento,5)") : ('');

        if ($order == "MOVI_DH_ENCAMINHAMENTO DESC") {
            $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') DESC";
        } else if ($order == "MOVI_DH_ENCAMINHAMENTO ASC") {
            $order = "TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') ASC";
        }
        /* Ordem */
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        //  Zend_Debug::Dump($stmt);
        //   exit();
        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();

        if ($params['SOMENTE_PRINCIPAL'] == 'N') {
            //esconde as solicitações filhas vinculadas
            $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");
        }

        return $solics_cx;
    }

    public function getCaixaComNivelPesq ($idCaixa, $nivel, $params, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(1);
        $stmt .= "," . $CaixasQuerys->whereStatusVideoconferencia();
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
        $stmt .= $CaixasQuerys->whereNivel(true, $nivel);
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();

        /* Atendente */
        $stmt .= ( $params['SSOL_CD_MATRICULA_ATENDENTE']) ? (" AND SSOL_CD_MATRICULA_ATENDENTE = '" . $params['SSOL_CD_MATRICULA_ATENDENTE'] . "' ") : ('');

        /* Unidade fase */
        $stmt .= ( $params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' ") : ('');

        /* Unidade solicitante */
        $stmt .= ( $params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $stmt .= ( $params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " ") : ('');

        /* Solicitante */
        $stmt .= ( $params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' ") : ('');

        /* Categorias */
        if (is_array($params['CATE_ID_CATEGORIA'])) {
            //Remove valores vazios da array
            if (array_search("", $params['CATE_ID_CATEGORIA']) !== false) {
                unset($params['CATE_ID_CATEGORIA'][array_search("", $params['CATE_ID_CATEGORIA'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['CATE_ID_CATEGORIA']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['CATE_ID_CATEGORIA']);
                // Retira a utima virgula
                $stmt .= ( $params['CATE_ID_CATEGORIA']) ? (" 
                    AND SSOL_ID_DOCUMENTO IN( " .
                    "(
                    SELECT B.CASO_ID_DOCUMENTO 
                    FROM SOS.SOS_TB_CATE_CATEGORIA A,
                    SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                    WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                    AND A.CATE_ID_CATEGORIA IN ($value_query)
                    AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                    AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                    )"
                    . ") ") : ('');
            }
        }

        /* Serviço */
        if (is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $stmt .= ( $params['SSER_DS_SERVICO']) ? (" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Data de cadastro */
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Data da Ultima fase */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Número da solicitação */
        $docm_nr_documento = $params['DOCM_NR_DOCUMENTO'];
        $stmt .= ($docm_nr_documento) ? ("AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)
                                        AND DOCM_NR_DOCUMENTO LIKE '%'|| SUBSTR($docm_nr_documento,5)") : ('');

        /* Ordem */
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        // Zend_Debug::dump($stmt);
        // exit;
        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();

        if ($params['SOMENTE_PRINCIPAL'] == 'N') {
            //esconde as solicitações filhas vinculadas
            $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");
        }

        return $solics_cx;
    }

    public function getSolicitacaoMaisAntiga ($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(16);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinLotacaoGeradora();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereCaixa(true, 1);
        $stmt .= $CaixasQuerys->whereNivel(true, 1);
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereNaoEstaEmEspera();
        $stmt .= $CaixasQuerys->whereSemAtendenteOuComAtendentePorMatricula(true, $matricula);
        $stmt .= $CaixasQuerys->ordemCaixa('TEMPO_TOTAL DESC');
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
        ;
    }

    public function getCaixaEncamnhadosSecoesParaTrf ($idCaixaOrigem, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(1);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereEncaminhadasdeUmaCaixa(true, $idCaixaOrigem);
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getSolicitacaoMaisNova ($numero) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SSOL_ID_DOCUMENTO,DOCM_NR_DOCUMENTO, TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME,
                                   DOCM_CD_MATRICULA_CADASTRO, SSER_DS_SERVICO, TRUNC((SYSDATE - MOFA_DH_FASE)*24,2) TEMPO_TOTAL, 
                                   B.DOCM_CD_LOTACAO_GERADORA, A.SSOL_ED_LOCALIZACAO, A.SSOL_DS_EMAIL_EXTERNO, A.SSOL_NR_TELEFONE_EXTERNO, L.LOTA_DSC_LOTACAO,
                                   L.LOTA_SIGLA_LOTACAO, B.DOCM_DS_ASSUNTO_DOC, A.SSOL_NM_USUARIO_EXTERNO, A.SSOL_DS_EMAIL_EXTERNO, C.MOVI_ID_MOVIMENTACAO
                            FROM   SOS_TB_SSOL_SOLICITACAO A,
                                   SAD_TB_DOCM_DOCUMENTO B,
                                   SAD_TB_MOVI_MOVIMENTACAO C,
                                   SAD_TB_MODO_MOVI_DOCUMENTO D,
                                   SAD_TB_MODE_MOVI_DESTINATARIO E,
                                   SAD_TB_MOFA_MOVI_FASE F,
                                   SOS_TB_SSES_SERVICO_SOLIC G,
                                   SOS_TB_SSER_SERVICO H,
                                   SOS_TB_SNAS_NIVEL_ATEND_SOLIC I,
                                   RH_CENTRAL_LOTACAO L
                            WHERE  A.SSOL_ID_DOCUMENTO    = B.DOCM_ID_DOCUMENTO
                            AND    B.DOCM_ID_DOCUMENTO    = D.MODO_ID_DOCUMENTO
                            AND    C.MOVI_ID_MOVIMENTACAO = D.MODO_ID_MOVIMENTACAO
                            AND    C.MOVI_ID_MOVIMENTACAO = E.MODE_ID_MOVIMENTACAO
                            AND    C.MOVI_ID_MOVIMENTACAO = F.MOFA_ID_MOVIMENTACAO
                            AND    F.MOFA_ID_MOVIMENTACAO = G.SSES_ID_MOVIMENTACAO(+)
                            AND    F.MOFA_DH_FASE         = G.SSES_DH_FASE(+)
                            AND    G.SSES_ID_SERVICO      = H.SSER_ID_SERVICO(+)
                            AND    F.MOFA_DH_FASE         = I.SNAS_DH_FASE
                            AND    I.SNAS_ID_NIVEL        = 1
                            AND    E.MODE_ID_CAIXA_ENTRADA = 1
                            AND    B.DOCM_CD_LOTACAO_GERADORA = L.LOTA_COD_LOTACAO
                            AND    A.SSOL_CD_MATRICULA_ATENDENTE IS NULL
                            AND    A.SSOL_ID_DOCUMENTO = $numero");
        $solicitacao = $stmt->fetchAll();
        return $solicitacao;
    }

    public function getCaixaPessoal ($matricula, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(1);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereAtendente(true, $matricula);
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;

        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();

        $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");

        return $solics_cx;
    }

    public function getCaixaPessoalPesq ($idCaixa, $matricula, $params, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = $CaixasQuerys->selectCaixa(12);
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->colunasServicosSistemas();
        }
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->leftJoinServicosSistemas();
        }
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereAtendente(true, $matricula);
        //idCaixa = 0 representa todas as caixas
        if ($idCaixa != 0) {
            $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
        }

        /* Atendente */
        $stmt .= ( isset($params['SSOL_CD_MATRICULA_ATENDENTE']) && $params['SSOL_CD_MATRICULA_ATENDENTE']) ? (" AND SSOL_CD_MATRICULA_ATENDENTE = '" . $params['SSOL_CD_MATRICULA_ATENDENTE'] . "' ") : ('');

        /* Unidade fase */
        $stmt .= ( isset($params['MOFA_ID_FASE']) && $params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' ") : ('');

        /* Unidade solicitante */
        $stmt .= ( isset($params['DOCM_SG_SECAO_GERADORA']) && $params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $stmt .= ( isset($params['DOCM_CD_LOTACAO_GERADORA']) && $params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " ") : ('');

        /* Mostrar solicitações filhas */
//        $stmt .= ( $params['SOMENTE_PRINCIPAL']=='N') ? (" AND 1 = SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(A.SSOL_ID_DOCUMENTO)"):('');

        /* Solicitante */
        $stmt .= ( isset($params['DOCM_CD_MATRICULA_CADASTRO']) && $params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' ") : ('');

        /* Categorias */
        if (isset($params['CATE_ID_CATEGORIA']) && is_array($params['CATE_ID_CATEGORIA'])) {
            //Remove valores vazios da array
            if (array_search("", $params['CATE_ID_CATEGORIA']) !== false) {
                unset($params['CATE_ID_CATEGORIA'][array_search("", $params['CATE_ID_CATEGORIA'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['CATE_ID_CATEGORIA']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['CATE_ID_CATEGORIA']);
                // Retira a utima virgula
                $stmt .= ( $params['CATE_ID_CATEGORIA']) ? (" 
                    AND SSOL_ID_DOCUMENTO IN( " .
                    "(
                    SELECT B.CASO_ID_DOCUMENTO 
                    FROM SOS.SOS_TB_CATE_CATEGORIA A,
                    SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                    WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                    AND A.CATE_ID_CATEGORIA IN ($value_query)
                    AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                    AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                    )"
                    . ") ") : ('');
            }
        }
        /* Serviço */
        if (isset($params['SSER_ID_SERVICO']) && is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $stmt .= ( isset($params['SSER_ID_SERVICO']) && $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $stmt .= ( isset($params['SSER_DS_SERVICO']) && $params['SSER_DS_SERVICO']) ? (" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Data de cadastro */
        ((isset($params['DATA_INICIAL_CADASTRO']) && $params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        ((isset($params['DATA_INICIAL_CADASTRO']) && $params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        ((isset($params['DATA_INICIAL_CADASTRO']) && $params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Data da Ultima fase */
        ((isset($params['DATA_INICIAL']) && $params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        ((isset($params['DATA_INICIAL']) && $params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        ((isset($params['DATA_INICIAL']) && $params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Número da solicitação */
        $stmt .= "AND (DOCM_NR_DOCUMENTO LIKE '%" . $params["DOCM_NR_DOCUMENTO"] . "%' OR SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) LIKE '%" . $params["DOCM_NR_DOCUMENTO"] . "%')";
        /* Ordem */
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();
        if ($params['SOMENTE_PRINCIPAL'] == 'N') {
            //esconde as solicitações filhas vinculadas
            $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");
        }
        return $solics_cx;
    }

    public function sysdate () {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux = $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATAHORA"];
        $datahora = new Zend_Db_Expr("TO_DATE('$datahora','dd/mm/yyyy HH24:MI:SS')");
        return $datahora;
    }

    /**
     * Recebe como parametros de entrada
     * @param array $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = ;
     * @param array $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
     * @param array $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = ;
     * @param array $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = ;
     * @param array $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = ;
     * @param array $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = ;
     * @param array $dataDocmDocumento["DOCM_ID_PCTT"] = 414; //PCTT Solicitação de TI
     * @param array $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = ;
     * @param array $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
     * @param array $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
     * @param array $dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"]; 

     * @param array $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = ;
     * @param array $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = ;
     * @param array $dataSsolSolicitacao["SSOL_NR_TOMBO"] = ;
     * @param array $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = ;
     * @param array $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = ;
     * @param array $dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = ;
     * @param array $dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO'] = ;
     * @param array $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = ;
     * @param array $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = ;
     * 
     * @param array $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = ;
     * @param array $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = ;
     * @param array $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = ;

     * @param array $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = ;
     * @param array $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = 1146; //Unidade de Destino DIATU PRIMEIRO ATENDIMENTO
     * @param array $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
     * @param array $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU


     * @param array $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
     * @param array $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * @param array $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação para primeiro atendiamento no HELPDESK";


     * @param array $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = 1; //Primeiro Nivel HelpDesk ;

     * @param array $dataSsesServicoSolic["SSES_ID_SERVICO"] = ;
     * 
     * 
     * @example      
     * $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = ;
     * $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
     * $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = ;
     * $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = ;
     * $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = ;
     * $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = ;
     * $dataDocmDocumento["DOCM_ID_PCTT"] = 414; //PCTT Solicitação de TI
     * $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = ;
     * $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
     * $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
     * unset($dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"]);

     * $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = ;
     * $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = ;
     * $dataSsolSolicitacao["SSOL_NR_TOMBO"] = ;
     * $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = ;
     * $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = ;
     * $dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = ;
     * $dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO'] = ;
     * $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = ;
     * $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = ;
     * 
     * $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = ;
     * $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = ;
     * $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU
     * $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = ;
     * 
     * $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = ;
     * $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = ;
     * $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
     * $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU


     * $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
     * $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação para primeiro atendiamento no HELPDESK";


     * $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = 1; //Primeiro Nivel HelpDesk ;

     * $dataSsesServicoSolic["SSES_ID_SERVICO"] = ;
     * 
     * $dataAnexAnexo["ANEX_ID_DOCUMENTO"] = $idDocmDocumento;
     * $dataAnexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = ;
     * $dataAnexAnexo["ANEX_ID_MOVIMENTACAO"] = ;
     * $dataAnexAnexo["ANEX_DH_FASE"] = ;
     * 
     * @return $dataRetorno 
     * $dataRetorno["DOCM_ID_DOCUMENTO"] = $idDocmDocumento;
     * $dataRetorno["DOCM_NR_DOCUMENTO"] = $dataDocmDocumento["DOCM_NR_DOCUMENTO"];
     * 
     * 
     */
    public function cadastraSolicitacao (array $dataDocmDocumento, array $dataSsolSolicitacao, array $dataMoviMovimentacao, array $dataModeMoviDestinatario, array $dataMofaMoviFase, array $dataSsesServicoSolic, array $dataSnasNivelAtendSolic, $nrDocsRed = null, $dataAcompanhantes = null, $dataPorOrdemDe = null) {
        /*
         * Cadastro da Solicitação Interna
         */
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();

        $dataDocmDocumento["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($dataDocmDocumento['DOCM_SG_SECAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'], $dataDocmDocumento['DOCM_ID_TIPO_DOC']);

        $dataDocmDocumento["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($dataDocmDocumento['DOCM_SG_SECAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_GERADORA'], $dataDocmDocumento['DOCM_ID_TIPO_DOC'], $dataDocmDocumento['DOCM_NR_SEQUENCIAL_DOC']);

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();
            Zend_Debug::dump($data);
            /* ---------------------------------------------------------------------------------------- */
            /* Primeira tabela a ser inserida */

            Zend_Debug::dump($dataDocmDocumento);
            $assuntoSolitacao = $dataDocmDocumento['DOCM_DS_ASSUNTO_DOC'];
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
            $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = new Zend_Db_Expr("'" . substr($dataDocmDocumento['DOCM_DS_ASSUNTO_DOC'], 0, 4000) . "'");
//            $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = ;
//            $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = ;
//              $dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"];
            Zend_Debug::dump($dataDocmDocumento);
            $rowDocmDocumento = $tabelaSadTbDocmDocumento->createRow($dataDocmDocumento);
            $idDocmDocumento = $rowDocmDocumento->save();
            
            
            Zend_Debug::dump($idDocmDocumento, "Id do documento.");
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* Segunda tabela */
            Zend_Debug::dump($dataSsolSolicitacao);
            $dataSsolSolicitacao["SSOL_ID_DOCUMENTO"] = $idDocmDocumento;
//        $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"];
//        $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"];
//        $dataSsolSolicitacao["SSOL_NR_TOMBO"];
//        $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"];
            $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = new Zend_Db_Expr("'" . $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] . "'");
            unset($dataSsolSolicitacao["SSOL_HH_INICIO_ATEND"]);
            unset($dataSsolSolicitacao["SSOL_HH_FINAL_ATEND"]);
//        $dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'];
//        $dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO'];
//        $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'];
//        $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'];
            Zend_Debug::dump($dataSsolSolicitacao);

            $rowSsolSolicitacao = $this->createRow($dataSsolSolicitacao);
            $idSsolSolicitacao = $rowSsolSolicitacao->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* terceira tabela */
            $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
            //$dataMoviMovimentacao =  $SadTbMoviMovimentacao->fetchNew()->toArray();

            Zend_Debug::dump($dataMoviMovimentacao);
            unset($dataMoviMovimentacao["MODO_ID_MOVIMENTACAO"]);
            $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $datahora;
//            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = ;
//            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = ;
//            $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = ;
//            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = ;
            Zend_Debug::dump($dataMoviMovimentacao);

            $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
            $idMoviMovimentacao = $rowMoviMovimentacao->save();
            Zend_Debug::dump($idMoviMovimentacao, "id da movimentacao");
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* quarta tabela */
            $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
            //$dataModoMoviDocumento =  $SadTbModoMoviDocumento->fetchNew()->toArray();

            Zend_Debug::dump($dataModoMoviDocumento);
            $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $idDocmDocumento;
            Zend_Debug::dump($dataModoMoviDocumento);

            $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
            $rowModoMoviDocumento->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* quinta tabela */
            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
            //$dataModeMoviDestinatario=  $SadTbModeMoviDestinatario->fetchNew()->toArray();

            Zend_Debug::dump($dataModeMoviDestinatario);
            $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
//            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = ;
//            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = ; 
//            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
//            $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = ;
            unset($dataModeMoviDestinatario["MODE_DH_RECEBIMENTO"]);
            unset($dataModeMoviDestinatario["MODE_CD_MATR_RECEBEDOR"]);
            Zend_Debug::dump($dataModeMoviDestinatario);

            $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
            $rowModeMoviDestinatario->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* sexta tabela */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();

            Zend_Debug::dump($dataMofaMoviFase);
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
//            $dataMofaMoviFase["MOFA_ID_FASE"] = ;
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /* ---------------------------------------------------------------------------------------- */

            //Ultima Fase do lançada na Solicitação.//
            /* ---------------------------------------------------------------------------------------- */
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();

            $rowUltima_fase->setFromArray($dataUltima_fase);
            Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* setima tabela */
            $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
            //$dataSsesServicoSolic=  $SosTbSsesServicoSolic->fetchNew()->toArray();

            Zend_Debug::dump($dataSsesServicoSolic);
            $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataSsesServicoSolic["SSES_DH_FASE"] = $datahora;
//            $dataSsesServicoSolic["SSES_ID_SERVICO"] = ;
//            $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = ;
            if (isset($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]) && !is_null($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"])) {
                $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = new Zend_Db_Expr("TO_DATE('" . $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] . "','dd/mm/yyyy HH24:MI:SS')");
                $dataSsesServicoSolic["SSES_IC_VIDEO_REALIZADA"] = "N";
            }
            $dataSsesServicoSolic['SSES_ID_DOCUMENTO'] = $idDocmDocumento;
            Zend_Debug::dump($dataSsesServicoSolic);

            $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
            $rowSsesServicoSolic->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* oitava tabela */
            if ($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] && isset($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"])) {
                $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
                //$dataSnasNivelAtendSolic=  $SosTbSnasNivelAtendSolic->fetchNew()->toArray();

                Zend_Debug::dump($dataSnasNivelAtendSolic);
                $dataSnasNivelAtendSolic["SNAS_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                $dataSnasNivelAtendSolic["SNAS_DH_FASE"] = $datahora;
                //            $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = ;
                $dataSnasNivelAtendSolic["SNAS_ID_DOCUMENTO"] = $idDocmDocumento;
                Zend_Debug::dump($dataSnasNivelAtendSolic);

                $rowSnasNivelAtendSolic = $SosTbSnasNivelAtendSolic->createRow($dataSnasNivelAtendSolic);
                $rowSnasNivelAtendSolic->save();
            }
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* nona tabela */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            if ($nrDocsRed) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /* ---------------------------------------------------------------------------------------- */

            /* -----------------ACOMPANHAMENTO DE BAIXA DE SOLICITAÇÃO NO CADASTRO -------------------- */
            if (!is_null($dataAcompanhantes)) {
                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                foreach ($dataAcompanhantes as $acompanhante) {
                    $arr_exploded = explode(" - ", $acompanhante);
                    $matricula = $arr_exploded[0];
                    $tabelaPapd->addAcompanhanteSostiCadastroSolicitacao($idDocmDocumento, $matricula);
                }
            }
            /* ---------------------------------------------------------------------------------------- */

            /* -----------------CADASTRO DE SOLICITAÇÃO POR ORDEM DE  -------------------- */
            if (!is_null($dataPorOrdemDe)) {
                $arr_exploded_porordemde = explode(" - ", $dataPorOrdemDe);
                $matricula = $arr_exploded_porordemde[0];
                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                $tabelaPapd->addPorOrdemDeCadastroSolicitacao($idDocmDocumento, $matricula);
            }
            /* ---------------------------------------------------------------------------------------- */
            
            /** 
             * Se a solicitação for cadastrada para um usuário externo lança a fase:
             * PRIMEIRO ATENDIMENTO DE SOLICITAÇÃO DE USUÁRIO EXTERNO
             * para não cair no índice: "Índice de Inicio de Atendimento no Prazo"
             */
            if ($dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] != null) {
                $dataMofaMoviFase["MOFA_ID_FASE"] = 2007;
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'Considerado como primeiro atendimento.';
                $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                sleep(1);
                $this->setLancarFase($idDocmDocumento, $dataMofaMoviFase);
            }
            $db->commit();
//            $db->closeConnection();
            $dataRetorno["DOCM_ID_DOCUMENTO"] = $idDocmDocumento;
            $dataRetorno["DOCM_NR_DOCUMENTO"] = $dataDocmDocumento["DOCM_NR_DOCUMENTO"];
            /** Atualiza quando é necessário gravar CLOB */
            App_Clob::saveClob(
                'DOCM_DS_ASSUNTO_DOC',
                'SAD_TB_DOCM_DOCUMENTO',
                "DOCM_ID_DOCUMENTO = $idDocmDocumento",
                $assuntoSolitacao
            );
            $result = $dataRetorno;
        } catch (Exception $exc) {
            $db->rollBack();
            $result = $exc->getMessage();
        }

        return $result;
    }

    /**
     * Recebe como parametros de entrada
     * 
     * @param type $idDocmDocumento
     * @param array $dataMoviMovimentacao ('MOVI_SG_SECAO_UNID_ORIGEM'=>'', 'MOVI_CD_SECAO_UNID_ORIGEM'=>'', 'MOVI_CD_MATR_ENCAMINHADOR'=>'', 'MOVI_ID_CAIXA_ENTRADA'=>'')
     * @param array $dataModeMoviDestinatario ('MODE_SG_SECAO_UNID_DESTINO'=>'', 'MODE_CD_SECAO_UNID_DESTINO'=>'', 'MODE_IC_RESPONSAVEL'=>'', 'MODE_ID_CAIXA_ENTRADA'=>'')
     * @param array $dataMofaMoviFase ('MOFA_ID_FASE'=>'', 'MOFA_CD_MATRICULA'=>'', 'MOFA_DS_COMPLEMENTO'=>'')
     * @param array $dataSsesServicoSolic ('SNAS_ID_NIVEL'=>'')
     * @param array $dataSnasNivelAtendSolic ('SNAS_ID_NIVEL'=>'') 
     * 
     * 
     * @return void
     */
    public function encaminhaSolicitacao ($idDocmDocumento, array $dataMoviMovimentacao, array $dataModeMoviDestinatario, array $dataMofaMoviFase, array $dataSsesServicoSolic, array $dataSnasNivelAtendSolic, $nrDocsRed = null, $acompanhar = null
    ) {
        /**
         * Encaminha Solicitação
         * Com ou sem troca de nível.
         */
        $datahora = $this->sysdate();
        Zend_Debug::dump($datahora, 'data e hora');

        /**
         * Verifica se a solicitação é de videoconferência e está sendo enviada a um serviço de videoconferência. 
         */
        $DadosSolicitacao = $this->getDadosSolicitacao($idDocmDocumento);
        if (!array_key_exists("SSES_DT_INICIO_VIDEO", $DadosSolicitacao)) {
            throw new Zend_Exception('O valor da variável obrigatória SSES_DT_INICIO_VIDEO está ausente');
        }
        if (!is_null($DadosSolicitacao["SSES_DT_INICIO_VIDEO"])) {
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServicoRow = $SosTbSserServico->fetchRow("SSER_ID_SERVICO = " . $dataSsesServicoSolic["SSES_ID_SERVICO"]);
            if (!is_null($SosTbSserServicoRow)) {
                $SosTbSserServicoRowArray = $SosTbSserServicoRow->toArray();
                if ($SosTbSserServicoRowArray["SSER_IC_VIDEOCONFERENCIA"] == "S") {
                    $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = new Zend_Db_Expr("TO_DATE('" . $DadosSolicitacao["SSES_DT_INICIO_VIDEO"] . "','dd/mm/yyyy HH24:MI:SS')");
                    $dataSsesServicoSolic["SSES_IC_VIDEO_REALIZADA"] = "N";
                } else {
                    throw new Zend_Exception('ATENÇÃO: Solicitações com o serviço de Videoconferência somente podem ser enviadas a serviços de Videoconferência correspondentes do grupo ao qual elas serão enviadas. Selecione um serviço de Videoconferência correspondete do grupo de envio.', 2);
                }
            }
        } else {
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServicoRow = $SosTbSserServico->fetchRow("SSER_ID_SERVICO = " . $dataSsesServicoSolic["SSES_ID_SERVICO"]);
            if (!is_null($SosTbSserServicoRow)) {
                $SosTbSserServicoRowArray = $SosTbSserServicoRow->toArray();
                if ($SosTbSserServicoRowArray["SSER_IC_VIDEOCONFERENCIA"] == "S") {
                    throw new Zend_Exception('ATENÇÃO: As solicitações escolhidas são de videoconferência se sim, uma ou mais solicitações de videoconferência dentre as escolhidas não possui(em) a data de inicio. Resolva este problema trocando o serviço para Videoconferencia e informando a data de início da mesma. Se não para enviar como videoconfência é preciso trocar o serviço para videoconferência primeiro. ', 2);
                }
            }
        }

        /* ---------------------------------------------------------------------------------------- */
        /* primeira tabela */
        $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        //$dataMoviMovimentacao =  $SadTbMoviMovimentacao->fetchNew()->toArray();
        // $dataMoviMovimentacao = array();

        Zend_Debug::dump($dataMoviMovimentacao);
        unset($dataMoviMovimentacao["MODO_ID_MOVIMENTACAO"]);
        $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $datahora;
//            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = INFORMAR;
//            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = INFORMAR;
//            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = INFORMAR;
//            $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = INFORMAR;
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
        //$dataModoMoviDocumento = array();

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
        //$dataModeMoviDestinatario = array();

        Zend_Debug::dump($dataModeMoviDestinatario);
        $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
//            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = INFORMAR;
//            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = INFORMAR;
//            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = INFORMAR;
//            $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = INFORMAR;
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
        //$dataMofaMoviFase = array();

        Zend_Debug::dump($dataMofaMoviFase);
        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
//            $dataMofaMoviFase["MOFA_ID_FASE"] = INFORMAR;
        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
        Zend_Debug::dump($dataMofaMoviFase);

        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
        $rowMofaMoviFase->save();
        /* ---------------------------------------------------------------------------------------- */

        //Ultima Fase do lançada na Solicitação.//
        /* ---------------------------------------------------------------------------------------- */

        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();

        $rowUltima_fase->setFromArray($dataUltima_fase);
        Zend_Debug::dump($rowUltima_fase->toArray());
        $rowUltima_fase->save();
        /* ---------------------------------------------------------------------------------------- */
        /* setima tabela */
        //if( $dataSsesServicoSolic["SSES_ID_SERVICO"] && isset($dataSsesServicoSolic["SSES_ID_SERVICO"]) ) {
        $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
        //$dataSsesServicoSolic=  $SosTbSsesServicoSolic->fetchNew()->toArray();
        // $dataSsesServicoSolic = array();

        Zend_Debug::dump($dataSsesServicoSolic);
        $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataSsesServicoSolic["SSES_DH_FASE"] = $datahora;
//                $dataSsesServicoSolic["SSES_ID_SERVICO"] = INFORMAR;
        $dataSsesServicoSolic["SSES_ID_DOCUMENTO"] = $idDocmDocumento;
        Zend_Debug::dump($dataSsesServicoSolic);

        $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
        $rowSsesServicoSolic->save();
        //}
        /* ---------------------------------------------------------------------------------------- */
        /* quinta tabela */
        if ($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] && isset($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"])) {
            $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
            //$dataSnasNivelAtendSolic=  $SosTbSnasNivelAtendSolic->fetchNew()->toArray();
            // $dataSnasNivelAtendSolic = array();

            Zend_Debug::dump($dataSnasNivelAtendSolic);
            $dataSnasNivelAtendSolic["SNAS_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataSnasNivelAtendSolic["SNAS_DH_FASE"] = $datahora;
//                $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = INFORMAR;
            $dataSnasNivelAtendSolic["SNAS_ID_DOCUMENTO"] = $idDocmDocumento;
            Zend_Debug::dump($dataSnasNivelAtendSolic);

            $rowSnasNivelAtendSolic = $SosTbSnasNivelAtendSolic->createRow($dataSnasNivelAtendSolic);
            $rowSnasNivelAtendSolic->save();
        }
        /* ---------------------------------------------------------------------------------------- */

        /* Retira do atendente */
        /* ---------------------------------------------------------------------------------------- */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
        $rowSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();
        $rowSolicitacao->setFromArray($dataSsolSolicitacao);
        $rowSolicitacao->save();
        /* ---------------------------------------------------------------------------------------- */

        // Insere o anexo
        /* ---------------------------------------------------------------------------------------- */

        $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
        $anexAnexo['ANEX_DH_FASE'] = $datahora;
        $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        /**
         * Cadastra os documentos que ainda não existe no red.
         */
        if ($nrDocsRed['incluidos']) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                $rowAnexAnexo->save();
            }
        }
        /**
         *  Verifica se o documento que já existe no red já pertence a esta solicitação
         * caso negativo, cadastra o nr do documento para a solicitação.
         */
        if ($nrDocsRed['existentes']) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idDocmDocumento AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                if (!$SadTbAnexAnexofetchRow) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
        }
        /* ----------------------ACOMPANHAMENTO DE BAIXA DA SOLICITAÇÃO NO ENCAMINHAMENTO  --------- */
        if ($acompanhar == "S") {
            $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
            $tabelaPapd->addAcompanhanteSostiCaixaAtendimento($idDocmDocumento);
        }
        /* ---------------------------------------------------------------------------------------- */

        $retorno['ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $retorno['DATA_HORA'] = $datahora;
//        Zend_Debug::dump($retorno);exit;
        return $retorno;
    }

    /**
     * Baixa Solicitação
     * Recebe como parametros de entrada
     * @param $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
     * @param $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * @param $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = ;
     * 
     * @return void
     */
    public function baixaSolicitacao (array $dataMofaMoviFase, $idSolicitacao, $nrDocsRed = null, $autoCommit = true) {
        /**
         * Baixa Solicitação
         */
        if ($autoCommit) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }

        try {
            $datahora = $this->sysdate();
            Zend_Debug::dump($data);

            /* ---------------------------------------------------------------------------------------- */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();

            Zend_Debug::dump($dataMofaMoviFase);
//            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = ;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1000; /* BAIXA */
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = TR300544;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();
            /* ---------------------------------------------------------------------------------------- */
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $dataSsolSolicitacao['SSOL_ID_DOCUMENTO'] = $idSolicitacao;
            $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
            $dataSsolSolicitacao['SSOL_IC_REPLICA_AVALIACAO_OS'] = $dataMofaMoviFase['REPLICA_AVALIACAO_OS'] == 1 ? 'S' : 'N';
            $rowSolicitacao = $SosTbSsolSolicitacao->find($dataSsolSolicitacao['SSOL_ID_DOCUMENTO'])->current();
            $rowSolicitacao->setFromArray($dataSsolSolicitacao);
            $rowSolicitacao->save();


            //Ultima Fase do lançada na Solicitação.//
            /* ---------------------------------------------------------------------------------------- */

            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idSolicitacao)->current();

            $rowUltima_fase->setFromArray($dataUltima_fase);
            Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();
            /* ---------------------------------------------------------------------------------------- */


            /* ---------------------------------------------------------------------------------------- */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idSolicitacao;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            /**
             * Cadastra os documentos que ainda não existe no red.
             */
            if ($nrDocsRed['incluidos']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /**
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idSolicitacao AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                    if (!$SadTbAnexAnexofetchRow) {
                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                        $rowAnexAnexo->save();
                    }
                }
            }

            /**
             * Tratamento especial para solicitações automaticas
             */
            $rowDadosSolicitacao = $this->getDadosSolicitacao($idSolicitacao);
            if ($rowDadosSolicitacao["SSOL_ID_TIPO_CAD"] == 7) {
                $SosTbSavsAvaliacaoServico = new Application_Model_DbTable_SosTbSavsAvaliacaoServico();

                /* Dados da fase */
                $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
                $dataMofaMoviFase["MOFA_ID_FASE"] = 1014; /* Avaliacao positiva */
                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $rowDadosSolicitacao["DOCM_CD_MATRICULA_CADASTRO"]; /* Usuario cadastrante */
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Avaliação automática.";
                /* Dados da avaliacao */
                $dataMofaMoviFaseAvaliacao["SAVS_ID_TIPO_SAT"] = 1; /* Avalicao otima */

                /* Como as avaliações de SOSTI’s automáticos ocorrem no momento da baixa, deve ser dado uma espera de 1 segundo
                  para não correr o risco de a fase de avaliação e baixa ficarem com a mesma hora e apresentar erro ORA-00001 */
                sleep(1);

                $SosTbSavsAvaliacaoServico->setAvaliaSolicitacao($idSolicitacao, $dataMofaMoviFase, $dataMofaMoviFaseAvaliacao, null, false);
            }

            if ($autoCommit) {
                $db->commit();
            }
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            throw $exc;
        }
        $retorno = array("DATA_HORA" => $datahora);
        return $retorno;
    }

    /**
     * setVideoConfRealizada
     * Recebe como parametros de entrada
     * @param $idMovimentacao
     * @param $icVideoRealizada
     * @return func_get_args
     */
    public function setVideoConfRealizada ($idMovimentacao, $icVideoRealizada, $autoCommit = true) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ($autoCommit) {
            $db->beginTransaction();
        }
        try {
            $q = "UPDATE SOS_TB_SSES_SERVICO_SOLIC
                    SET SSES_IC_VIDEO_REALIZADA = '" . $icVideoRealizada . "'
                    WHERE SSES_ID_MOVIMENTACAO = " . $idMovimentacao
                . "AND TO_CHAR(SSES_DH_FASE,'DD/MM/YYYY HH24:MI:SS') = (SELECT TO_CHAR(MAX(SSES_DH_FASE),'DD/MM/YYYY HH24:MI:SS') SSES_DH_FASE 
                                                                            FROM   SOS_TB_SSES_SERVICO_SOLIC A
                                                                            WHERE  A.SSES_ID_MOVIMENTACAO = " . $idMovimentacao . ")"
            ;
            $db->query($q);

            if ($autoCommit) {
                $db->commit();
            }
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            throw $exc;
        }
        return func_get_args();
    }

    public function getDadosSolicitacao ($idDocumento, $idCaixa = null, $reConn = false) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(4);
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->colunasServicosSistemas();
        }
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinFasePrazo();
        $stmt .= $CaixasQuerys->leftJoinLotacaoGeradora();
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->leftJoinServicosSistemas();
        }
        $stmt .= $CaixasQuerys->innerJoinMovimentacaoDestinatarioCaixaDeEntrada();
        $stmt .= $CaixasQuerys->innerJoinCaixaDeEntradaGrupoServico();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereUltimoPrazo();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereIdSolicitacao(true, $idDocumento);
        $stmt = $db->query($stmt);
        $arrayData = $stmt->fetch();
        if ($reConn) {
            $arrayData["DOCM_DS_ASSUNTO_DOC"] = App_Clob::selectClob(
                'DOCM_DS_ASSUNTO_DOC', 
                'SAD_TB_DOCM_DOCUMENTO',
                'DOCM_ID_DOCUMENTO = '.$arrayData["SSOL_ID_DOCUMENTO"]
            );
        }
        return $arrayData;
    }

    public function getDadosVariasSolicitacoesJson ($solicitacoes) {
        $DadosSolicitacao = null;
        $i = 0;
        foreach ($solicitacoes as $value) {
            $solic = Zend_Json_Decoder::decode($value);
            $DadosSolicitacao[$i] = $this->getDadosSolicitacao($solic['SSOL_ID_DOCUMENTO']);
            $DadosSolicitacao[$i] = Zend_Json_Encoder::encode($DadosSolicitacao[$i]);
            $i++;
        }
        return $DadosSolicitacao;
    }

    public function getDadosSolicitacaoGarantia ($idMovimentacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(13);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->innerJoinGarantia();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimaFasePorMovimentacao(true, $idMovimentacao);
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        return $db->query($stmt)->fetch();
    }

    public function getDadosVariasSolicitacoesGarantiaJson ($solicitacoes = array()) {
        $DadosSolicitacao = null;
        $DadosSolicitacaoGarantia = array();
        $i = 0;
        $solic = array();
        foreach ($solicitacoes as $value) {
            $solic = Zend_Json_Decoder::decode($value);
            $DadosSolicitacaoGarantia = $this->getDadosSolicitacaoGarantia($solic['MOVI_ID_MOVIMENTACAO']);
            if (!empty($DadosSolicitacaoGarantia)) {
                $DadosSolicitacao[$i] = $DadosSolicitacaoGarantia;
                $DadosSolicitacao[$i] = Zend_Json::encode($DadosSolicitacao[$i]);
            }
            $i++;
        }
        return $DadosSolicitacao;
    }

    public function getHistoricoSolicitacao ($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT
            DTPD.DTPD_NO_TIPO,
            MOVIM.*,
            DOCM.DOCM_ID_DOCUMENTO,
            DOCM.DOCM_NR_DOCUMENTO,
            DOCM.DOCM_NR_DCMTO_USUARIO,
            MOVI.MOVI_DH_ENCAMINHAMENTO,
            MOVI.MOVI_ID_MOVIMENTACAO,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
            DOCM.DOCM_NR_DOCUMENTO_RED,
            MOFA.MOFA_DS_COMPLEMENTO,
            MOFA_ID_MOVIMENTACAO,
            MOFA_CD_MATR_ENCAMINHADO,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA.MOFA_CD_MATR_ENCAMINHADO) MOFA_CD_MATR_ENCAMINHADO_NOME,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA.MOFA_CD_MATRICULA) MOFA_CD_MATRICULA_NOME,
            MOFA.MOFA_CD_MATRICULA,
            TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
            FADM.FADM_ID_FASE,
            FADM.FADM_DS_FASE,
            TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
            --ANEX.ANEX_NR_DOCUMENTO_INTERNO NR_RED,
            CXEN.CXEN_DS_CAIXA_ENTRADA,
            SNAT_CD_NIVEL,
            SNAT_DS_NIVEL,
            STSA_DS_TIPO_SAT,
            TO_CHAR(SSPA_DT_PRAZO ,'dd/mm/yyyy HH24:MI:SS') SSPA_DT_PRAZO,
            SSPA_IC_CONFIRMACAO,
            SSER_ID_SERVICO,
            SSER_DS_SERVICO,
            TO_CHAR(SSES_DT_INICIO_VIDEO,'dd/mm/yyyy HH24:MI:SS') SSES_DT_INICIO_VIDEO,
            SSES_IC_VIDEO_REALIZADA

            FROM   SAD_TB_DOCM_DOCUMENTO DOCM
            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI ON DOCM.DOCM_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI ON MODO_MOVI.MODO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO
            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ON MOVI.MOVI_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA ON MOVI.MOVI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN ON MODE_MOVI.MODE_ID_CAIXA_ENTRADA = CXEN.CXEN_ID_CAIXA_ENTRADA
            LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS ON MOFA.MOFA_ID_MOVIMENTACAO = SNAS.SNAS_ID_MOVIMENTACAO AND MOFA.MOFA_DH_FASE = SNAS.SNAS_DH_FASE LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT ON SNAT.SNAT_ID_NIVEL =  SNAS.SNAS_ID_NIVEL
            LEFT JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS ON MOFA.MOFA_ID_MOVIMENTACAO = SAVS.SAVS_ID_MOVIMENTACAO AND  MOFA.MOFA_DH_FASE = SAVS.SAVS_DH_FASE
            LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA ON SAVS.SAVS_ID_TIPO_SAT = STSA.STSA_ID_TIPO_SAT
            LEFT JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA ON MOFA.MOFA_ID_MOVIMENTACAO = SSPA.SSPA_ID_MOVIMENTACAO AND  MOFA.MOFA_DH_FASE = SSPA.SSPA_DH_FASE
            LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES ON MOFA.MOFA_ID_MOVIMENTACAO = SSES.SSES_ID_MOVIMENTACAO AND  MOFA.MOFA_DH_FASE  = SSES.SSES_DH_FASE
            LEFT JOIN SOS_TB_MDSI_MOVIM_DEF_SISTEMA MOVIM ON MOVIM.MDSI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
            LEFT JOIN SOS_TB_SSER_SERVICO SSER ON SSES.SSES_ID_SERVICO = SSER.SSER_ID_SERVICO
            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
            --LEFT JOIN SAD_TB_ANEX_ANEXO ANEX ON ANEX.ANEX_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO AND ANEX.ANEX_DH_FASE = MOFA.MOFA_DH_FASE AND ANEX.ANEX_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
            INNER JOIN SAD_TB_FADM_FASE_ADM FADM ON FADM.FADM_ID_FASE = MOFA.MOFA_ID_FASE WHERE DOCM.DOCM_ID_DOCUMENTO = $idDocumento
            ORDER BY MOFA.MOFA_DH_FASE DESC");

        return $stmt->fetchAll();
    }

    public function getAnalistaResponsavel ($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD.DTPD_NO_TIPO,
                					   MOVIM.*,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
									   MOVI.MOVI_ID_MOVIMENTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MOFA.MOFA_DS_COMPLEMENTO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA.MOFA_CD_MATRICULA) MOFA_CD_MATRICULA_NOME,
                                       MOFA.MOFA_CD_MATRICULA,
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       FADM.FADM_ID_FASE,
                                       FADM.FADM_DS_FASE,
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       --ANEX.ANEX_NR_DOCUMENTO_INTERNO NR_RED,
                                       CXEN.CXEN_DS_CAIXA_ENTRADA,
                                       SNAT_CD_NIVEL,
                                       SNAT_DS_NIVEL,
                                       STSA_DS_TIPO_SAT,
                                       TO_CHAR(SSPA_DT_PRAZO ,'dd/mm/yyyy HH24:MI:SS') SSPA_DT_PRAZO,
                                       SSPA_IC_CONFIRMACAO,
                                       SSER_ID_SERVICO,
                                       SSER_DS_SERVICO,
                                       TO_CHAR(SSES_DT_INICIO_VIDEO,'dd/mm/yyyy HH24:MI:SS') SSES_DT_INICIO_VIDEO,
                                       SSES_IC_VIDEO_REALIZADA
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                                       ON MODE_MOVI.MODE_ID_CAIXA_ENTRADA = CXEN.CXEN_ID_CAIXA_ENTRADA
                                       LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS
                                       ON  MOFA.MOFA_ID_MOVIMENTACAO  = SNAS.SNAS_ID_MOVIMENTACAO
                                       AND MOFA.MOFA_DH_FASE = SNAS.SNAS_DH_FASE
                                       LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                                       ON  SNAT.SNAT_ID_NIVEL         =  SNAS.SNAS_ID_NIVEL
                                       LEFT JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS
                                       ON MOFA.MOFA_ID_MOVIMENTACAO = SAVS.SAVS_ID_MOVIMENTACAO 
                                       AND  MOFA.MOFA_DH_FASE = SAVS.SAVS_DH_FASE
                                       LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA
                                       ON   SAVS.SAVS_ID_TIPO_SAT = STSA.STSA_ID_TIPO_SAT
                                       LEFT JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA
                                       ON   MOFA.MOFA_ID_MOVIMENTACAO = SSPA.SSPA_ID_MOVIMENTACAO 
                                       AND  MOFA.MOFA_DH_FASE = SSPA.SSPA_DH_FASE
                                       LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
                                       ON  MOFA.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
                                       AND  MOFA.MOFA_DH_FASE  = SSES.SSES_DH_FASE
                					   LEFT JOIN SOS_TB_MDSI_MOVIM_DEF_SISTEMA MOVIM
                					   ON MOVIM.MDSI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                       LEFT JOIN SOS_TB_SSER_SERVICO SSER
                                       ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO 
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       --LEFT JOIN SAD_TB_ANEX_ANEXO ANEX
                                       --ON ANEX.ANEX_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO 
                                       --AND    ANEX.ANEX_DH_FASE = MOFA.MOFA_DH_FASE
                                       --AND    ANEX.ANEX_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_FADM_FASE_ADM FADM
                                       ON FADM.FADM_ID_FASE = MOFA.MOFA_ID_FASE
                                        WHERE  DOCM.DOCM_ID_DOCUMENTO = $idDocumento
                                            AND MOFA.MOFA_ID_FASE = '1001'
                                        ORDER BY MOFA.MOFA_DH_FASE DESC");
        return $stmt->fetchAll();
    }

    /**
     * Retorna todas as solicitações em atendimento.
     * O parametro tipoSolicitacao é utilizado para diferenciar as fases.
     * O parametro tipoVisao está associado ao parametro tipoSolicitacao.
     * Ex: Quando o tipoSolicitação for 'solicitacao de informacao'
     * o parametro tipoVisao deverá ser 'aousuario' ou 'aoencaminhador'.
     * Se não for colocado nenhum parametro após o order então a função listará
     * por default todas as solicitações cadastradas pela matricula que ainda 
     * estão em atendimento.
     * 
     * @param type $matricula
     * @param type $order
     * @param type $tipoSolicitacao
     * @param type $tipoVisao
     * @param type $documentoID
     * @param type $tipo
     * @return type 
     */
    public function getMinhasSolicitacoesAtendimento ($matricula, $order, $tipoSolicitacao = null, $tipoVisao = null, $documentoID = null, $tipo = null) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        if ($tipoSolicitacao == 'solicitacao de informacao') {
            if ($tipoVisao == 'aunidade') {
                $stmt .= " AND MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'";
            } else {
                $stmt .= $CaixasQuerys->whereMatriculaSolicitacaoInformacao($matricula, $tipoVisao);
            }
        } else {
            $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        }

        $stmt .= $CaixasQuerys->ordemCaixa($order);
        if ($tipoSolicitacao == 'solicitacao de informacao') {
            $stmt = 'SELECT * FROM (' . $stmt . ') SUB_QUERY WHERE ';
            $stmt .= $CaixasQuerys->whereRegraSolicitacaoInformacao($tipoVisao, false);
        }
        return $db->fetchAll($stmt);
    }
    
    public function getQtdeMinhasSolicitacoesAtendimento ($matricula, $order, $tipoSolicitacao = null, $tipoVisao = null, $documentoID = null, $tipo = null) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        if ($tipoSolicitacao == 'solicitacao de informacao') {
            if ($tipoVisao == 'aunidade') {
                $stmt .= " AND MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'";
            } else {
                $stmt .= $CaixasQuerys->whereMatriculaSolicitacaoInformacao($matricula, $tipoVisao);
            }
        } else {
            $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        }

        $stmt .= $CaixasQuerys->ordemCaixa($order);
        if ($tipoSolicitacao == 'solicitacao de informacao') {
            $stmt = 'SELECT * FROM (' . $stmt . ') SUB_QUERY WHERE ';
            $stmt .= $CaixasQuerys->whereRegraSolicitacaoInformacao($tipoVisao, false);
        }
        $stmt2 .= "SELECT COUNT(*) QTDE FROM ( ";
        $stmt2 .= $stmt;
        $stmt2 .= " ) ";
        return $db->fetchAll($stmt2);
    }

    public function getMinhasSolicitacoesBaixadas ($matricula, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new Sisad_Model_DataMapper_HistoricoBaixadas();

        $stmt .= $CaixasQuerys->historicoDeSostiBaixados($matricula, $order);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getMinhasSolicitacoesAvaliacao ($matricula, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        $stmt .= $CaixasQuerys->whereUltimafaseBaixa(true);
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }
    
    public function getQtdeMinhasSolicitacoesAvaliacao ($matricula, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(15);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        $stmt .= $CaixasQuerys->whereUltimafaseBaixa(true);
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getMinhasSolicitacoesAvaliacaoAutomatica ($idCaixa, $dataInicial, $dataFinal, $idService = NULL, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $stmt = "";
        $select = " SELECT
                    DISTINCT 
                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO, 
                    DOCM_CD_MATRICULA_CADASTRO,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO_TIPE_CHAR,
                    DOCM_DH_CADASTRO DOCM_DH_CADASTRO_TIPE_DATE,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_CD_MATRICULA,
                    MOFA_ID_MOVIMENTACAO, 
                    TRUNC((SYSDATE - MOFA_DH_FASE)) TEMPO_PENDENTE,

                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO
                    ";
        $stmt .= $select;
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereUltimafaseBaixa(true);
        $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
        $stmt .= " AND MOFA_DH_FASE BETWEEN TO_DATE( '$dataInicial', 'DD/MM/YYYY HH24:MI:SS' ) AND TO_DATE( '$dataFinal', 'DD/MM/YYYY HH24:MI:SS' ) ";
        if (!$idService == NULL) {
            $stmt .= "AND SSER_ID_SERVICO = '$idService'";
        }
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getMinhasSolicitacoesPedidoInfRespondido ($matricula, $order, $pedeInfo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= " 
                --deve ser a última matricula de pedido igual a matricula do usuário
                AND 0 < (
                SELECT COUNT(MOFA_ID_FASE)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND    MOFA_ID_FASE = 1024
                AND    MOFA_CD_MATRICULA = '$matricula'
                AND    MOFA_DH_FASE = (
                                        SELECT MAX(MOFA_DH_FASE)
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                        WHERE  MOFA_3.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                        AND    MOFA_ID_FASE = 1024
                                      )
                     )
                ";
        $stmt .= ( $pedeInfo == 1024) ? (" 
                -- deve ser igual a zero o número de fases de pergunta posteriores a última fase de resposta
                AND  0 =
                (
                SELECT COUNT(MOFA_ID_FASE)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND    MOFA_ID_FASE = 1024
                -- Pedido de Inf. Respondido depois de pedir informacao
                AND    MOFA_DH_FASE > (
                                        SELECT MAX(MOFA_DH_FASE)
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                        WHERE  MOFA_3.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                        AND    MOFA_ID_FASE = 1025
                                        )
                )
                -- Pedido de Informacao respondido
                AND 0 < 
                (
                SELECT COUNT(MOFA_ID_FASE)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND    MOFA_ID_FASE = 1025
                )
                ") : ('');
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }
    
    public function getQtdeMinhasSolicitacoesPedidoInfRespondido ($matricula, $order, $pedeInfo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereEmAtendimento();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= " 
                --deve ser a última matricula de pedido igual a matricula do usuário
                AND 0 < (
                SELECT COUNT(MOFA_ID_FASE)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND    MOFA_ID_FASE = 1024
                AND    MOFA_CD_MATRICULA = '$matricula'
                AND    MOFA_DH_FASE = (
                                        SELECT MAX(MOFA_DH_FASE)
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                        WHERE  MOFA_3.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                        AND    MOFA_ID_FASE = 1024
                                      )
                     )
                ";
        $stmt .= ( $pedeInfo == 1024) ? (" 
                -- deve ser igual a zero o número de fases de pergunta posteriores a última fase de resposta
                AND  0 =
                (
                SELECT COUNT(MOFA_ID_FASE)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND    MOFA_ID_FASE = 1024
                -- Pedido de Inf. Respondido depois de pedir informacao
                AND    MOFA_DH_FASE > (
                                        SELECT MAX(MOFA_DH_FASE)
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                        WHERE  MOFA_3.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                        AND    MOFA_ID_FASE = 1025
                                        )
                )
                -- Pedido de Informacao respondido
                AND 0 < 
                (
                SELECT COUNT(MOFA_ID_FASE)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                AND    MOFA_ID_FASE = 1025
                )
                ") : ('');
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt2 .= " SELECT COUNT(*) QTDE FROM ( ";
        $stmt2 .= $stmt;
        $stmt2 .= " ) ";
        $stmt3 = $db->query($stmt2);
        return $stmt3->fetchAll();
    }

    public function getMinhasSolicitacoesAvaliadas ($matricula, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        $stmt .= $CaixasQuerys->whereUltimafaseAvaliadaPositivamente();
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getTodasSolicitacoes ($params, $order, $localidade = null) {
        $mat = explode(' - ', $params["DOCM_CD_MATRICULA_CADASTRO"]);
        $lot = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
        $trf_secao = explode('|', $params['TRF1_SECAO']);
        $secao = explode('|', $params['SECAO_SUBSECAO']);
        $servicoID = explode('|', $params['SSER_ID_SERVICO']);
        $sServicoID = $servicoID[0];
        $usuarioExterno = $params['SSOL_NM_USUARIO_EXTERNO'];



        $grupo = Zend_Json::decode($params["SGRS_ID_GRUPO"]);
        $docm_cd_matricula_cadastro = $mat[0];
        $docm_cd_lotacao_geradora = $lot[2];
        /**
         * Verifica se foi escolhida uma Subsecao
         * Se não, fazer a busca pela Secao ou TRF
         */
        if ($secao[0] == "" && $trf_secao[0] != "") {
            $sigla_secao = $trf_secao[0];
        } else {
            if ($secao[0] != "") {
                $sigla_secao = $secao[0];
            }
        }
        $ssol_nr_telefone_externo = $params["SSOL_NR_TELEFONE_EXTERNO"];
        $ssol_ds_email_externo = $params["SSOL_DS_EMAIL_EXTERNO"];
        $ssol_ed_localizacao = $params["SSOL_ED_LOCALIZACAO"];
        $mofa_id_fase = $params["STATUS_SOLICITACAO"];
        $ssol_nr_tombo = $params["SSOL_NR_TOMBO"];
        $docm_ds_assunto_doc = $params["DOCM_DS_ASSUNTO_DOC"];
        $ssol_ds_observacao = $params["SSOL_DS_OBSERVACAO"];
        $data_inicial = $params["DATA_INICIAL"];
        $data_final = $params["DATA_FINAL"];
        $docm_nr_documento = $params["DOCM_NR_DOCUMENTO"];
        $mode_id_caixa_entrada = $grupo["CXEN_ID_CAIXA_ENTRADA"];
//        $sser_id_servico = $servico[0];
//        $sser_ds_servico = $params["SSER_DS_SERVICO"];


        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(6);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();

        $stmt .= ( $docm_cd_matricula_cadastro) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '$docm_cd_matricula_cadastro'") : ('');
        $stmt .= ( $docm_cd_lotacao_geradora) ? (" AND DOCM_CD_LOTACAO_GERADORA = '$docm_cd_lotacao_geradora'") : ('');
        if ($secao[0] != "" && $secao[2] == 2) {
            $lotacao = $secao[1];
            $tipolotacao = $secao[2];
            $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
            $Lotacao_array = $RhCentralLotacao->getLotacaobySecao($sigla_secao, $lotacao, $tipolotacao);
            foreach ($Lotacao_array as $key => $value) {
                $ids[$key] = $value["LOTA_COD_LOTACAO"];
            }
            $ids = implode(',', $ids);

            $stmt .= " AND DOCM_CD_LOTACAO_REDATORA IN($ids) 
                       AND DOCM_SG_SECAO_REDATORA = '$sigla_secao'";
        } else {
            /**
             * Se for escolhido TRF/Secao então fazer busca pela Secao
             */
            if ($trf_secao[0] != "") {
                $stmt .= ( $sigla_secao) ? (" AND DOCM_SG_SECAO_REDATORA = '$sigla_secao'") : ("");
            }
        }
        $stmt .= ( $ssol_nr_telefone_externo) ? (" AND SSOL_NR_TELEFONE_EXTERNO = '$ssol_nr_telefone_externo'") : ('');
        $stmt .= ( $ssol_ds_email_externo) ? (" AND SSOL_DS_EMAIL_EXTERNO = '$ssol_ds_email_externo'") : ('');
        $stmt .= ( $ssol_ed_localizacao) ? (" AND SSOL_ED_LOCALIZACAO = '$ssol_ed_localizacao'") : ('');
        $stmt .= ( ($mofa_id_fase == '1026') || ($mofa_id_fase == '1000') || ($mofa_id_fase == '1014')) ? (" AND MOFA_ID_FASE = $mofa_id_fase") : ('');
        $stmt .= ( $mofa_id_fase == '9999') ? (" AND MOFA_ID_FASE NOT IN ('1000', '1014','1026')") : ('');
        $stmt .= ( $sServicoID) ? (" AND SSER_ID_SERVICO IN( " . $sServicoID . ") ") : ('');

        /* Serviço */
//        if (is_array($params['SSER_ID_SERVICO'])) {
//            //Remove valores vazios da array
//            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
//                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
//            }
//            //Verifica se a array não é vazia
//            if (count($params['SSER_ID_SERVICO']) > 0) {
//                //Concatena os valores separados por vírgula
//                $value_query = implode(',', $params['SSER_ID_SERVICO']);
//                // Retira a utima virgula
//                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
//            }
//        } else {
//            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
//        }
//        $stmt .= ($params['SSER_DS_SERVICO'])?(" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%".$params['SSER_DS_SERVICO']."%')"):(''); 

        $stmt .= ($ssol_nr_tombo) ? (" AND SSOL_NR_TOMBO = '$ssol_nr_tombo'") : ('');

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);

        if ($params["DOCM_DS_ASSUNTO_DOC"]) {
            $docm_ds_assunto_doc = explode(',', $params["DOCM_DS_ASSUNTO_DOC"]);
            $num_complementos_docm_assunto = count($docm_ds_assunto_doc);

            $i = 0;
            foreach ($docm_ds_assunto_doc as $complemento_assunto_doc) {
                $array_html_docm[$i] = $Zend_Filter_HtmlEntities->filter($complemento_assunto_doc);
                $i++;
            }
            for ($i = 0; $i < $num_complementos_docm_assunto; $i++) {
                $stmt .= " AND (UPPER(DOCM_DS_ASSUNTO_DOC) LIKE UPPER('%" . $docm_ds_assunto_doc[$i] . "%') 
                     OR UPPER(DOCM_DS_ASSUNTO_DOC) LIKE UPPER('%" . $array_html_docm[$i] . "%'))";
            }
        }

        if ($params["SSOL_DS_OBSERVACAO"]) {
            $ssol_ds_observacao = explode(',', $params["SSOL_DS_OBSERVACAO"]);
            $num_complementos_ssol_observacao = count($ssol_ds_observacao);

            $i = 0;
            foreach ($ssol_ds_observacao as $complemento_observacao_ssol) {
                $array_html_ssol[$i] = $Zend_Filter_HtmlEntities->filter($complemento_observacao_ssol);
                $i++;
            }

            for ($i = 0; $i < $num_complementos_ssol_observacao; $i++) {
                $stmt .= " AND (UPPER(SSOL_DS_OBSERVACAO) LIKE UPPER('%" . $ssol_ds_observacao[$i] . "%') 
                    OR UPPER(SSOL_DS_OBSERVACAO) LIKE UPPER('%" . $array_html_ssol[$i] . "%'))";
            }
        }

        $stmt .= ($data_inicial && $data_final) ? (" AND DOCM_DH_CADASTRO between TO_DATE('$data_inicial', 'DD/MM/YYYY') AND TO_DATE('$data_final', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $stmt .= (($data_inicial == "") && ($data_final != "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_final . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_final . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $stmt .= (($data_inicial != "") && ($data_final == "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        if (!empty($docm_nr_documento)) {

            $lista_nr_doc = json_decode($this->json_nr_documento($docm_nr_documento, '160'));

            if (count($lista_nr_doc) > 1) {
                $stmt_aux = null;
                foreach ($lista_nr_doc as $value) {
                    $stmt_aux = ($stmt_aux <> null) ? ($stmt_aux . ' OR ') : (null);
                    if (strpos($value, "%") === false) {
                        $stmt_aux .= "DOCM_NR_DOCUMENTO = '$value'";
                    } else {
                        $stmt_aux .= "DOCM_NR_DOCUMENTO like '$value'";
                    }
                }
                $stmt .= "AND ($stmt_aux)";
            } else {
                if (strpos($lista_nr_doc[0], "%") === false) {
                    $stmt .= "AND DOCM_NR_DOCUMENTO = '$lista_nr_doc[0]'";
                } else {
                    $stmt .= "AND DOCM_NR_DOCUMENTO like '$lista_nr_doc[0]'";
                }
            }
        }

        $stmt .= ($mode_id_caixa_entrada) ? (" AND MODE_ID_CAIXA_ENTRADA = $mode_id_caixa_entrada") : ('');

        if ($params["MOFA_DS_COMPLEMENTO"] != '' || $params['MOFA_ID_FASE'] != '') {

            $dados_mofa_ds_complemento = explode(',', $params["MOFA_DS_COMPLEMENTO"]);
            $i = 0;
            foreach ($dados_mofa_ds_complemento as $complemento) {
                $array_html[$i] = $Zend_Filter_HtmlEntities->filter($complemento);
                $i++;
            }

            $stmt_historico .= "SELECT DISTINCT MODO_MOVI.MODO_ID_DOCUMENTO
                                    FROM   SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                    INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                    ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                    WHERE";

            $num_complementos = count($dados_mofa_ds_complemento);

            for ($i = 0; $i < $num_complementos; $i++) {
                if ($i != 0) {
                    $stmt_historico .=" AND ";
                }
                $stmt_historico .="
                        (UPPER(MOFA.MOFA_DS_COMPLEMENTO) LIKE UPPER('%" . $dados_mofa_ds_complemento[$i] . "%')
                        OR    
                        UPPER(MOFA.MOFA_DS_COMPLEMENTO) LIKE UPPER('%" . $array_html[$i] . "%'))";
            }
            $stmt_historico .= ($params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = " . $params['MOFA_ID_FASE']) : ('');
            $stmt .= " AND DOCM_ID_DOCUMENTO IN($stmt_historico)";
        }
        $stmt .= $usuarioExterno != "" ? " AND SSOL_NM_USUARIO_EXTERNO IS NOT NULL AND DOCM_ID_DOCUMENTO IN( SELECT SSOL_ID_DOCUMENTO  FROM SOS_TB_SSOL_SOLICITACAO  WHERE UPPER(SSOL_NM_USUARIO_EXTERNO) LIKE UPPER('%" . $usuarioExterno . "%'))  " : "";
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getTodasSolicitacoesCount ($params) {
        $mat = explode(' - ', $params["DOCM_CD_MATRICULA_CADASTRO"]);
        $lot = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
        $trf_secao = explode('|', $params['TRF1_SECAO']);
        $secao = explode('|', $params['SECAO_SUBSECAO']);
        $usuarioExterno = $params['SSOL_NM_USUARIO_EXTERNO'];
        
//        $i = 0;
//        foreach ($params["SSER_ID_SERVICO"] as $value) {
//            $aux = explode('|', $value);
//            $params["SSER_ID_SERVICO"][$i] = $aux[0];
//            $i++;
//        }

        $servicoID = explode('|', $params['SSER_ID_SERVICO']);
        $sServicoID = $servicoID[0];

        $grupo = Zend_Json::decode($params["SGRS_ID_GRUPO"]);
        $docm_cd_matricula_cadastro = $mat[0];
        $docm_cd_lotacao_geradora = $lot[2];
        /**
         * Verifica se foi escolhida uma Subsecao
         * Se não, fazer a busca pela Secao ou TRF
         */
        if ($secao[0] == "" && $trf_secao[0] != "") {
            $sigla_secao = $trf_secao[0];
        } else {
            if ($secao[0] != "") {
                $sigla_secao = $secao[0];
            }
        }
        $ssol_nr_telefone_externo = $params["SSOL_NR_TELEFONE_EXTERNO"];
        $ssol_ds_email_externo = $params["SSOL_DS_EMAIL_EXTERNO"];
        $ssol_ed_localizacao = $params["SSOL_ED_LOCALIZACAO"];
        $mofa_id_fase = $params["STATUS_SOLICITACAO"];
        //$sgrs_id_grupo = $grupo["SGRS_ID_GRUPO"];
        $sser_ds_servico = $params["SSER_DS_SERVICO"];
//        $sser_id_servico = $servico[0];
        $ssol_nr_tombo = $params["SSOL_NR_TOMBO"];
        $docm_ds_assunto_doc = $params["DOCM_DS_ASSUNTO_DOC"];
        $ssol_ds_observacao = $params["SSOL_DS_OBSERVACAO"];
        $data_inicial = $params["DATA_INICIAL"];
        $data_final = $params["DATA_FINAL"];
        $docm_nr_documento = $params["DOCM_NR_DOCUMENTO"];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCountIdSolicitacao();
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();

        $stmt .= ( $docm_cd_matricula_cadastro) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '$docm_cd_matricula_cadastro'") : ('');
        $stmt .= ( $docm_cd_lotacao_geradora) ? (" AND DOCM_CD_LOTACAO_GERADORA = '$docm_cd_lotacao_geradora'") : ('');
        if ($secao[0] != "" && $secao[2] == 2) {
            $lotacao = $secao[1];
            $tipolotacao = $secao[2];
            $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
            $Lotacao_array = $RhCentralLotacao->getLotacaobySecao($sigla_secao, $lotacao, $tipolotacao);
            foreach ($Lotacao_array as $key => $value) {
                $ids[$key] = $value["LOTA_COD_LOTACAO"];
            }
            $ids = implode(',', $ids);

            $stmt .= " AND DOCM_CD_LOTACAO_REDATORA IN($ids) 
                       AND DOCM_SG_SECAO_REDATORA = '$sigla_secao'";
        } else {
            if ($trf_secao[0] != "") {
                $stmt .= ( $sigla_secao) ? (" AND DOCM_SG_SECAO_REDATORA = '$sigla_secao'") : ("");
            }
        }
        $stmt .= ( $sigla_secao) ? (" AND DOCM_SG_SECAO_REDATORA = '$sigla_secao'") : ("");
        $stmt .= ( $ssol_nr_telefone_externo) ? (" AND SSOL_NR_TELEFONE_EXTERNO = '$ssol_nr_telefone_externo'") : ('');
        $stmt .= ( $ssol_ds_email_externo) ? (" AND SSOL_DS_EMAIL_EXTERNO = '$ssol_ds_email_externo'") : ('');
        $stmt .= ( $ssol_ed_localizacao) ? (" AND SSOL_ED_LOCALIZACAO = '$ssol_ed_localizacao'") : ('');
        $stmt .= ( ($mofa_id_fase == '1026') || ($mofa_id_fase == '1000') || ($mofa_id_fase == '1014')) ? (" AND MOFA_ID_FASE = $mofa_id_fase") : ('');
        $stmt .= ( $mofa_id_fase == '9999') ? (" AND MOFA_ID_FASE NOT IN ('1000', '1014','1026')") : ('');
        $stmt .= ( $sServicoID) ? (" AND SSER_ID_SERVICO IN( " . $sServicoID . ") ") : ('');

//      $stmt .= ($sgrs_id_grupo)?(" AND SGRS_ID_GRUPO = $sgrs_id_grupo"):('');


        /* Serviço */
//        if (is_array($params['SSER_ID_SERVICO'])) {
//            //Remove valores vazios da array
//            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
//                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
//            }
//            //Verifica se a array não é vazia
//            if (count($params['SSER_ID_SERVICO']) > 0) {
//                //Concatena os valores separados por vírgula
//                $value_query = implode(',', $params['SSER_ID_SERVICO']);
//                // Retira a utima virgula
//                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
//            }
//        } else {
//            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
//        }
//        $stmt .= ($params['SSER_DS_SERVICO'])?(" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%".$params['SSER_DS_SERVICO']."%')"):('');    

        $stmt .= ($ssol_nr_tombo) ? (" AND SSOL_NR_TOMBO = '$ssol_nr_tombo'") : ('');

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);

        if ($params["DOCM_DS_ASSUNTO_DOC"]) {
            $docm_ds_assunto_doc = explode(',', $params["DOCM_DS_ASSUNTO_DOC"]);
            $num_complementos_docm_assunto = count($docm_ds_assunto_doc);

            $i = 0;
            foreach ($docm_ds_assunto_doc as $complemento_assunto_doc) {
                $array_html_docm[$i] = $Zend_Filter_HtmlEntities->filter($complemento_assunto_doc);
                $i++;
            }
            for ($i = 0; $i < $num_complementos_docm_assunto; $i++) {
                $stmt .= " AND (UPPER(DOCM_DS_ASSUNTO_DOC) LIKE UPPER('%" . $docm_ds_assunto_doc[$i] . "%') 
                     OR UPPER(DOCM_DS_ASSUNTO_DOC) LIKE UPPER('%" . $array_html_docm[$i] . "%'))";
            }
        }

        if ($params["SSOL_DS_OBSERVACAO"]) {
            $ssol_ds_observacao = explode(',', $params["SSOL_DS_OBSERVACAO"]);
            $num_complementos_ssol_observacao = count($ssol_ds_observacao);

            $i = 0;
            foreach ($ssol_ds_observacao as $complemento_observacao_ssol) {
                $array_html_ssol[$i] = $Zend_Filter_HtmlEntities->filter($complemento_observacao_ssol);
                $i++;
            }

            for ($i = 0; $i < $num_complementos_ssol_observacao; $i++) {
                $stmt .= " AND (UPPER(SSOL_DS_OBSERVACAO) LIKE UPPER('%" . $ssol_ds_observacao[$i] . "%') 
                    OR UPPER(SSOL_DS_OBSERVACAO) LIKE UPPER('%" . $array_html_ssol[$i] . "%'))";
            }
        }

        $stmt .= ($data_inicial && $data_final) ? (" AND DOCM_DH_CADASTRO between TO_DATE('$data_inicial', 'DD/MM/YYYY') AND TO_DATE('$data_final', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $stmt .= (($data_inicial == "") && ($data_final != "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_final . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_final . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $stmt .= (($data_inicial != "") && ($data_final == "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        if (!empty($docm_nr_documento)) {

            $lista_nr_doc = json_decode($this->json_nr_documento($docm_nr_documento, '160'));

            if (count($lista_nr_doc) > 1) {
                $stmt_aux = null;
                foreach ($lista_nr_doc as $value) {
                    $stmt_aux = ($stmt_aux <> null) ? ($stmt_aux . ' OR ') : (null);
                    if (strpos($value, "%") === false) {
                        $stmt_aux .= "DOCM_NR_DOCUMENTO = '$value'";
                    } else {
                        $stmt_aux .= "DOCM_NR_DOCUMENTO like '$value'";
                    }
                }
                $stmt .= "AND ($stmt_aux)";
            } else {
                if (strpos($lista_nr_doc[0], "%") === false) {
                    $stmt .= "AND DOCM_NR_DOCUMENTO = '$lista_nr_doc[0]'";
                } else {
                    $stmt .= "AND DOCM_NR_DOCUMENTO like '$lista_nr_doc[0]'";
                }
            }
        }

        $stmt .= ($mode_id_caixa_entrada) ? (" AND MODE_ID_CAIXA_ENTRADA = $mode_id_caixa_entrada") : ('');

        if ($params["MOFA_DS_COMPLEMENTO"] != '' || $params['MOFA_ID_FASE'] != '') {

            $dados_mofa_ds_complemento = explode(',', $params["MOFA_DS_COMPLEMENTO"]);
            $i = 0;
            foreach ($dados_mofa_ds_complemento as $complemento) {
                $array_html[$i] = $Zend_Filter_HtmlEntities->filter($complemento);
                $i++;
            }

            $stmt_historico .= "SELECT DISTINCT MODO_MOVI.MODO_ID_DOCUMENTO
                                    FROM   SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                    INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                    ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                    WHERE";

            $num_complementos = count($dados_mofa_ds_complemento);

            for ($i = 0; $i < $num_complementos; $i++) {
                if ($i != 0) {
                    $stmt_historico .=" AND ";
                }
                $stmt_historico .="
                        (UPPER(MOFA.MOFA_DS_COMPLEMENTO) LIKE UPPER('%" . $dados_mofa_ds_complemento[$i] . "%')
                        OR    
                        UPPER(MOFA.MOFA_DS_COMPLEMENTO) LIKE UPPER('%" . $array_html[$i] . "%'))";
            }
            $stmt_historico .= ($params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = " . $params['MOFA_ID_FASE']) : ('');
            $stmt .= "AND DOCM_ID_DOCUMENTO IN($stmt_historico)";
        }
        $stmt .= $usuarioExterno != "" ? " AND SSOL_NM_USUARIO_EXTERNO IS NOT NULL AND DOCM_ID_DOCUMENTO IN( SELECT SSOL_ID_DOCUMENTO  FROM SOS_TB_SSOL_SOLICITACAO  WHERE UPPER(SSOL_NM_USUARIO_EXTERNO) LIKE UPPER('%" . $usuarioExterno . "%'))  " : "";
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);exit;
        $stmt = $db->query($stmt);
        return $stmt->fetch();
    }

    /*
     * Método que retorna as solicitações cadastradas por uma Unidade
     * @params $params - variavel de sessao do Zend com os parametros de pesquisa
     * 
     * @return array Collection of rows, each in a format by the fetch mode
     */

    public function getTodasSolicitacoesUnidade ($params) {

        if (!empty($params["TRF1_SECAO"])) {
            $secao = explode('|', $params['TRF1_SECAO']);
            $sigla_secao = $secao[0];
        }
        if (!empty($params["SECAO_SUBSECAO"]) && empty($params["DOCM_CD_LOTACAO_GERADORA"])) {
            $subsecao = explode('|', $params['SECAO_SUBSECAO']);
            if ($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA) {
                $docm_cd_lotacao_geradora = $subsecao[1];
            } else {
                $docm_cd_lotacao_geradora = "";
            }
        }
        if (!empty($params["DOCM_CD_LOTACAO_GERADORA"])) {
            $lot = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
            $docm_cd_lotacao_geradora = $lot[2];
        }
        //parametros do relatorio de sosti por periodo, a unidade é a lotação do usuário
        if (!empty($params["SIGLA_SECAO"])) {
            $sigla_secao = $params["SIGLA_SECAO"];
        }
        if (!empty($params["CODIGO_LOTACAO"])) {
            $docm_cd_lotacao_geradora = $params["CODIGO_LOTACAO"];
        }

        if (!empty($params["NR_TOMBO"])) {
            $ssol_nr_tombo = $params["NR_TOMBO"];
        }


        $data_inicial = $params["DATA_INICIAL"];
        $data_final = $params["DATA_FINAL"];
        $order = $params["ORDER"];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(14);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinLotacaoGeradora();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false, Trf1_Sosti_Definicoes::TIPO_SOLICITACAO_SERVICO);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();

        $stmt .= ( $sigla_secao) ? (" AND DOCM_SG_SECAO_REDATORA = '$sigla_secao'") : ("");
        $stmt .= ( $docm_cd_lotacao_geradora) ? (" AND DOCM_CD_LOTACAO_GERADORA = '$docm_cd_lotacao_geradora'") : ('');
        $stmt .= ($ssol_nr_tombo) ? (" AND SSOL_NR_TOMBO = '$ssol_nr_tombo'") : ('');
        $stmt .= ($data_inicial && $data_final) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('$data_inicial', 'DD/MM/YYYY') AND TO_DATE('$data_final', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $stmt .= (($data_inicial == "") && ($data_final != "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_final . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_final . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $stmt .= (($data_inicial != "") && ($data_final == "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        $ordenacao = " DOCM_DH_CADASTRO " . $order;
        $stmt .= $CaixasQuerys->ordem($ordenacao);

        //Zend_Debug::dump($stmt, 'query'); 
        //exit;

        $st = $db->query($stmt);
        return $st->fetchAll();
    }

    public function setAtendente ($idDocmDocumento, $matricula) {
        /* Retira do atendente */
        /* ---------------------------------------------------------------------------------------- */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = $matricula;
        $rowSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();
        $rowSolicitacao->setFromArray($dataSsolSolicitacao);
        $rowSolicitacao->save();
        /* ---------------------------------------------------------------------------------------- */
    }

    public function setSolicitarInformacaoSolicitacao (array $dataMofaMoviFase, $idSolicitacao, $nrDocsRed = null) {
        /**
         * Solicitar informação para solicitação de TI
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();

            //Ultima Fase lançada na Solicitação.//
            /* ---------------------------------------------------------------------------------------- */

            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idSolicitacao)->current();

            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();

            /* ---------------------------------------------------------------------------------------- */
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idSolicitacao;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            /**
             * Cadastra os documentos que ainda não existe no red.
             */
            if ($nrDocsRed['incluidos']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /**
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idSolicitacao AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                    if (!$SadTbAnexAnexofetchRow) {
                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                        $rowAnexAnexo->save();
                    }
                }
            }
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
        return $datahora;
    }

    public function getDadosSolicitante ($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SO.SSOL_ED_LOCALIZACAO, SO.SSOL_DS_EMAIL_EXTERNO, SO.SSOL_NR_TELEFONE_EXTERNO
                            FROM SOS_TB_SSOL_SOLICITACAO SO
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DC
                            ON SO.SSOL_ID_DOCUMENTO = DC.DOCM_ID_DOCUMENTO
                            WHERE SO.SSOL_ID_DOCUMENTO IN (SELECT MAX(SSOL_ID_DOCUMENTO) FROM SOS_TB_SSOL_SOLICITACAO SO
                                                           INNER JOIN SAD_TB_DOCM_DOCUMENTO DC
                                                           ON SO.SSOL_ID_DOCUMENTO = DC.DOCM_ID_DOCUMENTO
                                                           WHERE DC.DOCM_CD_MATRICULA_CADASTRO = '" . $matricula . "')");
        return $stmt->fetchAll();
    }

    public function getSolicitacoesPeriodoSla ($grupo, $nivel, $data_inicial, $data_final, $order, $avaliacao) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(7);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinServicoGrupoServico();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereUltimaAvaliacao();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= ($avaliacao != 9999) ? ( $CaixasQuerys->whereUltimaFaseHistorico(true, $avaliacao) ) : ($CaixasQuerys->whereUltimaMovimentacao() . $CaixasQuerys->whereUltimafaseBaixa());

        $stmt .= ( $grupo != '') ? (" AND SGRS_ID_GRUPO = $grupo ") : ('');
        $stmt .= ( $nivel != '') ? (" AND SNAT_CD_NIVEL = $nivel ") : ('');
        $stmt .= ( $data_inicial && $data_final) ? (" AND MOFA_DH_FASE between TO_DATE( '$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $stmt .= ( ($data_inicial == "") && ($data_final != "")) ? (" AND MOFA_DH_FASE between TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $stmt .= ( ($data_inicial != "") && ($data_final == "")) ? (" AND MOFA_DH_FASE between TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getQtdeSolicitacoesPeriodoSla ($grupo, $nivel, $data_inicial, $data_final, $order, $avaliacao) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCountIdSolicitacao('QTDE');
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinServicoGrupoServico();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereUltimaAvaliacao();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= ($avaliacao != 9999) ? ( $CaixasQuerys->whereUltimaFaseHistorico(true, $avaliacao) ) : ($CaixasQuerys->whereUltimaMovimentacao() . $CaixasQuerys->whereUltimafaseBaixa());

        $stmt .= ( $grupo != '') ? (" AND SGRS_ID_GRUPO = $grupo ") : ('');
        $stmt .= ( $nivel != '') ? (" AND SNAT_CD_NIVEL = $nivel ") : ('');
        $stmt .= ( $data_inicial && $data_final) ? (" AND MOFA_DH_FASE between TO_DATE( '$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $stmt .= ( ($data_inicial == "") && ($data_final != "")) ? (" AND MOFA_DH_FASE between TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $stmt .= ( ($data_inicial != "") && ($data_final == "")) ? (" AND MOFA_DH_FASE between TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getMinhasSolicitacoesPeriodoSla ($matricula, $params, $order) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(8);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinServicoGrupoServico();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereUltimaAvaliacao();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereUltimaFaseHistorico(true, 1000);

        $stmt .= ( $matricula) ? (" AND MOFA_CD_MATRICULA = '$matricula' ") : ("");

        $stmt .= ($params['DATA_INICIAL'] && $params['DATA_FINAL']) ? (" AND MOFA_DH_FASE between TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY hh24:mi:ss') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE <= TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND MOFA_DH_FASE >= TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        ($params['DATA_INICIAL_CADASTRO'] && $params['DATA_FINAL_CADASTRO']) ? ($stmt .= "AND DOCM_DH_CADASTRO between TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO <= TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO >= TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        ($params['DATA_INICIAL_ENCAMINHAMENTO'] && $params['DATA_FINAL_ENCAMINHAMENTO']) ? ($stmt .= "AND MOVI_DH_ENCAMINHAMENTO between TO_DATE('" . $params['DATA_INICIAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') AND TO_DATE('" . $params['DATA_FINAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_ENCAMINHAMENTO'] == "") && ($params['DATA_FINAL_ENCAMINHAMENTO'] != "")) ? ($stmt .= "AND MOVI_DH_ENCAMINHAMENTO <= TO_DATE('" . $params['DATA_FINAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_ENCAMINHAMENTO'] != "") && ($params['DATA_FINAL_ENCAMINHAMENTO'] == "")) ? ($stmt .= "AND MOVI_DH_ENCAMINHAMENTO >= TO_DATE('" . $params['DATA_INICIAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;

        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getQtdeMinhasSolicitacoesPeriodoSla ($matricula, $params, $order) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCountIdSolicitacao('QTDE');
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinServicoGrupoServico();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereUltimaAvaliacao();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereUltimaFaseHistorico(true, 1000);

        $stmt .= ( $matricula) ? (" AND MOFA_CD_MATRICULA = '$matricula' ") : ("");

        $stmt .= ($params['DATA_INICIAL'] && $params['DATA_FINAL']) ? (" AND MOFA_DH_FASE between TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY hh24:mi:ss') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND MOFA_DH_FASE <= TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND MOFA_DH_FASE >= TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        ($params['DATA_INICIAL_CADASTRO'] && $params['DATA_FINAL_CADASTRO']) ? ($stmt .= "AND DOCM_DH_CADASTRO between TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($stmt .= "AND DOCM_DH_CADASTRO <= TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($stmt .= "AND DOCM_DH_CADASTRO >= TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        ($params['DATA_INICIAL_ENCAMINHAMENTO'] && $params['DATA_FINAL_ENCAMINHAMENTO']) ? ($stmt .= "AND MOVI_DH_ENCAMINHAMENTO between TO_DATE('" . $params['DATA_INICIAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') AND TO_DATE('" . $params['DATA_FINAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_ENCAMINHAMENTO'] == "") && ($params['DATA_FINAL_ENCAMINHAMENTO'] != "")) ? ($stmt .= "AND MOVI_DH_ENCAMINHAMENTO <= TO_DATE('" . $params['DATA_FINAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL_ENCAMINHAMENTO'] != "") && ($params['DATA_FINAL_ENCAMINHAMENTO'] == "")) ? ($stmt .= "AND MOVI_DH_ENCAMINHAMENTO >= TO_DATE('" . $params['DATA_INICIAL_ENCAMINHAMENTO'] . "', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//       Zend_Debug::dump($stmt);
//        exit;

        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getTipoSatisfacao ($solicitacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TS.STSA_DS_TIPO_SAT
                            FROM SOS_TB_SAVS_AVALIACAO_SERVICO AV
                            INNER JOIN SOS_TB_STSA_TIPO_SATISFACAO TS
                            ON TS.STSA_ID_TIPO_SAT = AV.savs_id_tipo_sat
                            WHERE AV.SAVS_ID_DOCUMENTO = $solicitacao");
        $retorno = $stmt->fetchAll();
        return $retorno[0]['STSA_DS_TIPO_SAT'];
    }

    public function dataHoraAtual () {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux = $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATAHORA"];
        return $datahora;
    }

    /**
     * Cancela Solicitação
     * Tipo : 1 (Proprio usuario) 2 (Caixa)
     */
    public function cancelaSolicitacao (array $dataMofaMoviFase, $idSolicitacao, $tipo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();
            /* ---------------------------------------------------------------------------------------- */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $detalhe = $SosTbSsolSolicitacao->getDadosSolicitacao($idSolicitacao);
            $email['atendente'] = trim(strstr($detalhe['ATENDENTE'], '-', true));

            if (($detalhe['CXEN_ID_CAIXA_ENTRADA'] == 2) && ($tipo == 1)) { /* Desenvolvimento e Sustentaçao */
                $dataMofaMoviFase["MOFA_ID_FASE"] = 1056; /* PEDIDO DE CANCELAMENTO */
                $email['assunto'] = 'Pedido de Cancelamento de Solicitação';
                $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
                $email['tipo'] = 2;
            } else {
                $dataMofaMoviFase["MOFA_ID_FASE"] = 1026; /* CANCELAMENTO */
                $email['assunto'] = 'Cancelamento de Solicitação';
                $email['tipo'] = 1;
                $dataSsolSolicitacao['SSOL_ID_DOCUMENTO'] = $idSolicitacao;
                $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
                $rowSolicitacao = $SosTbSsolSolicitacao->find($dataSsolSolicitacao['SSOL_ID_DOCUMENTO'])->current();
                $rowSolicitacao->setFromArray($dataSsolSolicitacao);
                $rowSolicitacao->save();
            }
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();

            //Ultima Fase do lançada na Solicitação.//
            /* ---------------------------------------------------------------------------------------- */

            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idSolicitacao)->current();
            $rowUltima_fase->setFromArray($dataUltima_fase);
            //   Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();

            $db->commit();
            return $email;
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
    }

    /*
     * Desvinculando um atendente
     */

    public function desvincularAtendente (array $idDocmDocumento, $dataMofaMoviFase, $idSolicitacao, $nrDocsRed, $tipo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();
            $userNs = new Zend_Session_Namespace('userNs');
            /* ---------------------------------------------------------------------------------------- */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $detalhe = $SosTbSsolSolicitacao->getDadosSolicitacao($idSolicitacao);
            $email['atendente'] = trim(strstr($detalhe['ATENDENTE'], '-', true));

            if (($detalhe['CXEN_ID_CAIXA_ENTRADA'] == 2) && ($tipo == 1)) { /* Desenvolvimento e Sustentaçao */
                $dataMofaMoviFase["MOFA_ID_FASE"] = 1091; /* Desvincular Atendente */
                $email['assunto'] = 'Desvincular Atendnte';
                //$dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = '';
                // $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
                $email['tipo'] = 2;
            } else {
                $dataMofaMoviFase["MOFA_ID_FASE"] = 1091; /* Desvincular atendnte */
                $email['assunto'] = 'Desvincular Atendente';
                $email['tipo'] = 1;
                $dataSsolSolicitacao['SSOL_ID_DOCUMENTO'] = $idSolicitacao;
                //$dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = '';
                $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';

                $rowSolicitacao = $SosTbSsolSolicitacao->find($dataSsolSolicitacao['SSOL_ID_DOCUMENTO'])->current();
                $rowSolicitacao->setFromArray($dataSsolSolicitacao);
                $rowSolicitacao->save();
            }
            $dataMofaMoviFase['MOFA_CD_MATRICULA'] = $userNs->matricula;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();

            //Ultima Fase do lançada na Solicitação.//
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idSolicitacao)->current();
            $rowUltima_fase->setFromArray($dataUltima_fase);
            //   Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();
            /* ---------------------------------------------------------------------------------------- */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idSolicitacao;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            /**
             * Cadastra os documentos que ainda não existe no red.
             */
            if ($nrDocsRed['incluidos']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /**
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idSolicitacao AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                    if (!$SadTbAnexAnexofetchRow) {
                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                        $rowAnexAnexo->save();
                    }
                }
            }
            $db->commit();
            return $email;
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
    }

    public function setHomologarSos (array $dataMofaMoviFase, $idSolicitacao, $nrDocsRed = null) {
        /**
         * Solicitar informação para solicitação de TI
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();

            //Ultima Fase lançada na Solicitação.//
            /* ---------------------------------------------------------------------------------------- */
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $SadTbDocmDocumento->find($idSolicitacao)->current();

            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();

            /* ---------------------------------------------------------------------------------------- */
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idSolicitacao;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            /**
             * Cadastra os documentos que ainda não existe no red.
             */
            if ($nrDocsRed['incluidos']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /**
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idSolicitacao AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                    if (!$SadTbAnexAnexofetchRow) {
                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                        $rowAnexAnexo->save();
                    }
                }
            }
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
        }
        return $datahora;
    }

    public function getMinhasSolicitacoesHomologacao ($matricula, $order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        $stmt .= $CaixasQuerys->whereUltimaFaseHistorico(true, 1085);
        $stmt .= $CaixasQuerys->ordemCaixa($order);
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function setIncluirVariosAnexo ($anexAnexo, $nrDocsRed = array()) {
        if (!empty($nrDocsRed)) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed as $value) {
                $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $value;
                $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                $rowAnexAnexo->save();
            }
        }
    }

    public function setIncluirAnexo ($dataAnexAnexo) {
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        $rowAnexAnexo = $SadTbAnexAnexo->createRow($dataAnexAnexo);
        $rowAnexAnexo->save();
    }

    public function countAnexoIncluido ($idDocumento, $numeroRed) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) QTDE FROM Sad_Tb_Anex_Anexo
                            WHERE ANEX_ID_DOCUMENTO = $idDocumento
                            AND ANEX_NR_DOCUMENTO_INTERNO = '$numeroRed'");
        $retorno = $stmt->fetchAll();
        return $retorno[0]['QTDE'];
    }

    public function parecerSolicitacao (array $dataMofaMoviFase, $idSolicitacao, $nrDocsRed = null) {
        /**
         * Dar parecer na solicitação
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1028; /* Dar parecer na solicitação de TI */
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idSolicitacao)->current();

            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();
            /* ---------------------------------------------------------------------------------------- */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idSolicitacao;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            /**
             * Cadastra os documentos que ainda não existe no red.
             */
            if ($nrDocsRed['incluidos']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /**
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idSolicitacao AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                    if (!$SadTbAnexAnexofetchRow) {
                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                        $rowAnexAnexo->save();
                    }
                }
            }
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
        return $datahora;
    }

    public function getProcessosdaunidade ($unidade) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT *
							FROM SAD_TB_DOCM_DOCUMENTO 
							WHERE SUBSTR(DOCM_NR_DOCUMENTO,9,5) =" . $unidade);

        return $stmt->fetchAll();
    }

    public function getCheckList ($order) {

        if (!isset($order)) {
            $order = 'DOCM_NR_DOCUMENTO';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT 	B.NU_TOMBO, 
                                                                    C.DOCM_NR_DOCUMENTO,
                                                                    C.DOCM_DS_ASSUNTO_DOC,
                                                                    A.SSOL_DS_OBSERVACAO,
                                                                     substr(C.DOCM_DS_ASSUNTO_DOC,0,140) as RESUMO_ASSUNTO
                                                                    FROM SOS_TB_SSOL_SOLICITACAO A, TOMBO B, SAD_TB_DOCM_DOCUMENTO C
                                                                    WHERE A.SSOL_NR_TOMBO = NU_TOMBO
                                                                       AND C.DOCM_ID_DOCUMENTO = A.SSOL_ID_DOCUMENTO
                                                                       AND A.SSOL_NR_TOMBO IS NOT NULL
                                                                       AND B.TI_TOMBO = 'T'
                                                                       AND B.CO_MAT LIKE '5235%' ORDER BY $order");


        return $lstSolicitacoes = $stmt->fetchAll();
    }

    public function getSolicitacoesVencidasparaAvaliar ($matricula) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= "SELECT DISTINCT SSOL_ID_DOCUMENTO";
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereSolicitante(true, $matricula);
        $stmt .= $CaixasQuerys->whereUltimafaseBaixa();
        $stmt .= "AND  (  TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS') -  TO_DATE(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')  )   >=2";

        $stmt .= $CaixasQuerys->ordem('SSOL_ID_DOCUMENTO');
//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    public function getSolicitacoesPendenteAvaliacao ($order) {



        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();

        $stmt .= $CaixasQuerys->whereUltimafaseBaixa(true);
        $stmt .= $CaixasQuerys->ordemCaixa($order);


        $stmt = $db->query($stmt);

        return $stmt->fetchAll();
    }

    public function getIdVinculacao ($id) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $stmt = $db->query("SELECT VIDC_ID_VINCULACAO_DOCUMENTO
                            FROM SAD_TB_VIDC_VINCULACAO_DOC
                            WHERE VIDC_ID_DOC_VINCULADO = $id
                            AND VIDC_ID_TP_VINCULACAO = 4
                            --OR VIDC_ID_DOC_VINCULADO = $id");
        return $stmt->fetchAll();
    }

    public function getIdVinculacaoRecusada ($id) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $stmt = $db->query("SELECT VIDC_ID_VINCULACAO_DOCUMENTO
                            FROM SAD_TB_VIDC_VINCULACAO_DOC
                            WHERE VIDC_ID_DOC_VINCULADO = $id
                            AND VIDC_ID_TP_VINCULACAO = 4
                            OR VIDC_ID_DOC_PRINCIPAL = $id");
        return $stmt->fetchAll();
    }

    public function getPrincipalVinculacao ($id, $tpVinculacao = 4) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $stmt = $db->query("SELECT  DISTINCT (VIDC_ID_DOC_PRINCIPAL),
                                    VIDC_DH_VINCULACAO,
                                    DOCM_NR_DOCUMENTO
                            FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                            WHERE VIDC.VIDC_ID_DOC_VINCULADO = $id
                            OR VIDC.VIDC_ID_DOC_PRINCIPAL = $id
                            AND DOCM_ID_TIPO_DOC = 160
                            AND VIDC_ID_TP_VINCULACAO = '".$tpVinculacao."'");
        return $stmt->fetch();
    }

    public function getSolicitacaoMaisAntigaVinculacao ($solicitacoes) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM_ID_DOCUMENTO
                            FROM SAD_TB_DOCM_DOCUMENTO
                            WHERE DOCM_ID_DOCUMENTO IN ($solicitacoes)
                            AND DOCM_ID_TIPO_DOC = 160
                            ORDER BY DOCM_DH_CADASTRO ASC");
        return $stmt->fetchAll();
    }

    public function getSolicitacoesVinculadasActions ($principal) {
        $solicitacoes = $principal;

        $db = Zend_Db_Table::getDefaultAdapter();
        $i = 0;
        $qr = array();
        foreach ($solicitacoes as $solic) {
            $dados_solic = $solic;
            $stmt = $db->query("SELECT --DOCM_NR_DOCUMENTO,
                                       VIDC_ID_VINCULACAO_DOCUMENTO,
                                       VIDC_ID_DOC_PRINCIPAL,
                                       VIDC_ID_DOC_VINCULADO
                                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                                WHERE VIDC.VIDC_ID_DOC_PRINCIPAL =  (SELECT VIDC_ID_DOC_PRINCIPAL
                                                                    FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                                                    WHERE VIDC.VIDC_ID_DOC_VINCULADO = " . $dados_solic["SSOL_ID_DOCUMENTO"] . "
                                                                    AND VIDC.VIDC_ID_TP_VINCULACAO = " . Trf1_Sosti_Definicoes::ID_VINCULACAO_VINCULAR_SOSTI . ") 
                                 AND DOCM_ID_TIPO_DOC = 160
                                 AND VIDC_ID_TP_VINCULACAO = 4
                    

                                UNION

                                SELECT --DOCM_NR_DOCUMENTO,
                                       VIDC_ID_VINCULACAO_DOCUMENTO,
                                       VIDC_ID_DOC_PRINCIPAL,
                                       VIDC_ID_DOC_VINCULADO     
                                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                                WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = " . $dados_solic["SSOL_ID_DOCUMENTO"] . "
                                AND DOCM_ID_TIPO_DOC = 160
                                AND VIDC_ID_TP_VINCULACAO = 4

                                UNION

                                SELECT --DOCM_NR_DOCUMENTO,
                                       VIDC_ID_VINCULACAO_DOCUMENTO,
                                       VIDC_ID_DOC_PRINCIPAL,
                                       VIDC_ID_DOC_VINCULADO     
                                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                                WHERE VIDC.VIDC_ID_DOC_PRINCIPAL =  (SELECT VIDC_ID_DOC_PRINCIPAL
                                                                    FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                                                    WHERE VIDC.VIDC_ID_DOC_VINCULADO = " . $dados_solic["SSOL_ID_DOCUMENTO"] . "
                                                                    AND VIDC.VIDC_ID_TP_VINCULACAO = " . Trf1_Sosti_Definicoes::ID_VINCULACAO_VINCULAR_SOSTI . ")
                                AND DOCM_ID_TIPO_DOC = 160
                                AND VIDC_ID_TP_VINCULACAO = 4");
            $vinculados = NULL;
            $vinculados = $stmt->fetchAll();
            if (isset($vinculados[0])) {
                if (!is_null($vinculados[0])) {
                    $qr[$i] = $vinculados;
                    $qr[$i]["ID_CONSULTADA"] = $dados_solic["SSOL_ID_DOCUMENTO"];
                }
            }
            $i++;
        }
        return $qr;
    }

    public function getSolicitacoesVinculadas ($principal) {
        $solicspace = new Zend_Session_Namespace('solicspace');
        $solicitacoes = $solicspace->dados;

        $db = Zend_Db_Table::getDefaultAdapter();
        $i = 0;
        foreach ($solicitacoes as $solic) {
            $dados_solic = Zend_Json::decode($solic);
            $stmt = $db->query("SELECT --DOCM_NR_DOCUMENTO,
                                       VIDC_ID_VINCULACAO_DOCUMENTO,
                                       VIDC_ID_DOC_PRINCIPAL,
                                       VIDC_ID_DOC_VINCULADO
                                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                                WHERE VIDC.VIDC_ID_DOC_PRINCIPAL =  (SELECT VIDC_ID_DOC_PRINCIPAL
                                                                    FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                                                    WHERE VIDC.VIDC_ID_DOC_VINCULADO = " . $dados_solic["SSOL_ID_DOCUMENTO"] . "
                                                                    AND VIDC.VIDC_ID_TP_VINCULACAO = " . Trf1_Sosti_Definicoes::ID_VINCULACAO_VINCULAR_SOSTI . ")
                               AND DOCM_ID_TIPO_DOC = 160
                               AND VIDC_ID_TP_VINCULACAO = 4

                                UNION

                                SELECT --DOCM_NR_DOCUMENTO,
                                       VIDC_ID_VINCULACAO_DOCUMENTO,
                                       VIDC_ID_DOC_PRINCIPAL,
                                       VIDC_ID_DOC_VINCULADO     
                                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                                WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = " . $dados_solic["SSOL_ID_DOCUMENTO"] . "
                                AND DOCM_ID_TIPO_DOC = 160
                                AND VIDC_ID_TP_VINCULACAO = 4

                                UNION

                                SELECT --DOCM_NR_DOCUMENTO,
                                       VIDC_ID_VINCULACAO_DOCUMENTO,
                                       VIDC_ID_DOC_PRINCIPAL,
                                       VIDC_ID_DOC_VINCULADO     
                                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                                WHERE VIDC.VIDC_ID_DOC_PRINCIPAL =  (SELECT VIDC_ID_DOC_PRINCIPAL
                                                                    FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                                                    WHERE VIDC.VIDC_ID_DOC_VINCULADO = " . $dados_solic["SSOL_ID_DOCUMENTO"] . "
                                                                    AND VIDC.VIDC_ID_TP_VINCULACAO = " . Trf1_Sosti_Definicoes::ID_VINCULACAO_VINCULAR_SOSTI . ")
                                AND DOCM_ID_TIPO_DOC = 160
                                AND VIDC_ID_TP_VINCULACAO = 4");
            $vinculados = NULL;
            $vinculados = $stmt->fetchAll();
            if (!is_null($vinculados[0])) {
                $qr[$i] = $vinculados;
                $qr[$i]["ID_CONSULTADA"] = $dados_solic["SSOL_ID_DOCUMENTO"];
            }
            $i++;
        }
        return $qr;
    }

    public function getListaSolicitacoesVinculadas (
        $id, $id_caixa_entrada = null, $tpVinculacao = 4, $faseVincSolic = Trf1_Sosti_Definicoes::FASE_VINCULA_SOLICITACAO
    ) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $q = "SELECT DOCM_NR_DOCUMENTO,
                                    VIDC_ID_VINCULACAO_DOCUMENTO,
                                    VIDC_ID_DOC_PRINCIPAL,
                                    VIDC_ID_DOC_VINCULADO,
                                    TO_CHAR (VIDC_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIDC_DH_VINCULACAO,
                                    MOFA_ID_FASE,
                                    MOFA_ID_MOVIMENTACAO,
                                    PMAT_CD_MATRICULA,
                                    PNAT_NO_PESSOA
                             FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                             INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                             INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                 ON MOFA_DH_FASE = VIDC_DH_VINCULACAO
                                 AND MOFA_ID_FASE IN ( " . $faseVincSolic . ", " . Trf1_Sosti_Definicoes::FASE_VINCULA_SOLICITACAO_A_NOVA_PRINCIPAL . " )
                             INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO
                                 ON MOFA_ID_MOVIMENTACAO = MODO_ID_MOVIMENTACAO
                                 AND MODO_ID_DOCUMENTO = VIDC_ID_DOC_VINCULADO
                             INNER JOIN OCS_TB_PMAT_MATRICULA
                                 ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                             INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                 ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                             WHERE VIDC.VIDC_ID_DOC_PRINCIPAL =  (SELECT VIDC_ID_DOC_PRINCIPAL
                                                                 FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                                                 WHERE VIDC.VIDC_ID_DOC_VINCULADO = $id
                                                                 AND VIDC_ID_TP_VINCULACAO = " . $tpVinculacao . ")
                                 AND DOCM_ID_TIPO_DOC = " . Trf1_Sosti_Definicoes::TIPO_SOLICITACAO_SERVICO . "
                                 AND VIDC_ID_TP_VINCULACAO = " . $tpVinculacao . "
                                 AND VIDC_ID_DOC_VINCULADO != $id
                             UNION

                             SELECT DOCM_NR_DOCUMENTO,
                                    VIDC_ID_VINCULACAO_DOCUMENTO,
                                    VIDC_ID_DOC_PRINCIPAL,
                                    VIDC_ID_DOC_VINCULADO,
                                    TO_CHAR (VIDC_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIDC_DH_VINCULACAO,
                                    MOFA_ID_FASE,
                                    MOFA_ID_MOVIMENTACAO,
                                    PMAT_CD_MATRICULA,
                                    PNAT_NO_PESSOA
                             FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                             INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                             INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                 ON MOFA_DH_FASE = VIDC_DH_VINCULACAO
                                 AND MOFA_ID_FASE IN ( " . $faseVincSolic . ", " . Trf1_Sosti_Definicoes::FASE_VINCULA_SOLICITACAO_A_NOVA_PRINCIPAL . " )
                             INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO
                                 ON MOFA_ID_MOVIMENTACAO = MODO_ID_MOVIMENTACAO
                                 AND MODO_ID_DOCUMENTO = VIDC_ID_DOC_VINCULADO
                             INNER JOIN OCS_TB_PMAT_MATRICULA
                                 ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                             INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                 ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                             WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = $id
                                 AND DOCM_ID_TIPO_DOC = " . Trf1_Sosti_Definicoes::TIPO_SOLICITACAO_SERVICO . "
                                 AND VIDC_ID_TP_VINCULACAO = " . $tpVinculacao . "
                                 AND VIDC_ID_DOC_VINCULADO != $id
                             UNION

                             SELECT DOCM_NR_DOCUMENTO,
                                    VIDC_ID_VINCULACAO_DOCUMENTO,
                                    VIDC_ID_DOC_PRINCIPAL,
                                    VIDC_ID_DOC_VINCULADO,
                                    TO_CHAR (VIDC_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIDC_DH_VINCULACAO,
                                    MOFA_ID_FASE,
                                    MOFA_ID_MOVIMENTACAO,
                                    PMAT_CD_MATRICULA,
                                    PNAT_NO_PESSOA
                             FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                             INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                 ON VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                             INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                 ON MOFA_DH_FASE = VIDC_DH_VINCULACAO
                                 AND MOFA_ID_FASE IN ( " . $faseVincSolic . ", " . Trf1_Sosti_Definicoes::FASE_VINCULA_SOLICITACAO_A_NOVA_PRINCIPAL . " )
                             INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO
                                 ON MOFA_ID_MOVIMENTACAO = MODO_ID_MOVIMENTACAO
                                 AND MODO_ID_DOCUMENTO = VIDC_ID_DOC_VINCULADO
                             INNER JOIN OCS_TB_PMAT_MATRICULA
                                 ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                             INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                 ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                             WHERE VIDC.VIDC_ID_DOC_PRINCIPAL =  (SELECT VIDC_ID_DOC_PRINCIPAL
                                                                 FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC
                                                                 WHERE VIDC.VIDC_ID_DOC_VINCULADO = $id
                                                                 AND VIDC_ID_TP_VINCULACAO = " . $tpVinculacao . ")
                                 AND DOCM_ID_TIPO_DOC = " . Trf1_Sosti_Definicoes::TIPO_SOLICITACAO_SERVICO . "
                                 AND VIDC_ID_TP_VINCULACAO = " . $tpVinculacao . "
                                 AND VIDC_ID_DOC_VINCULADO != $id";
        $stmt = $db->query($q);
        $familia = $stmt->fetchAll();
        return $familia;
    }

    public function setVincularSolicitacoes ($solicitacoes, $idsSolicitacoespost, $todas_principais, $justificativa) {
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SadTbVidcAuditoria();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $Dual = new Application_Model_DbTable_Dual();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        /**
         * Verifica se existe familias
         */
        if (is_null($todas_principais)) {
            $somente_orfas = true;
        } else {
            $somente_orfas = false;
        }
        /**
         * Recolhe a mais antiga entre as orfãs
         */
        if ($somente_orfas) {
            $antigaSolicitacoesOrfas = $SosTbSsolSolicitacao->getSolicitacaoMaisAntigaVinculacao($idsSolicitacoespost);
            $antigaSolicitacoesOrfas = $antigaSolicitacoesOrfas[0]['DOCM_ID_DOCUMENTO'];
        } else {
            $antigaPrincipais = $SosTbSsolSolicitacao->getSolicitacaoMaisAntigaVinculacao(implode(',', $todas_principais));
            $antigaPrincipais = $antigaPrincipais[0]['DOCM_ID_DOCUMENTO'];
        }

//        Zend_Debug::dump($solicitacoes,'solicitacoes');
//        Zend_Debug::dump($idsSolicitacoespost,'idsSolicitacoespost');
//        Zend_Debug::dump($todas_principais,'todas_principais');
//        Zend_Debug::dump(implode(',',$todas_principais) ,'todas_principais');
//        Zend_Debug::dump($justificativa,'justificativa');
//        Zend_Debug::dump($somente_orfas,'$somente_orfas');
//        Zend_Debug::dump($antigaSolicitacoesOrfas,'$antigaSolicitacoesOrfas');
//        Zend_Debug::dump($antigaPrincipais,'$antigaPrincipais');

        $datahora = $Dual->sysdate();

        if ($somente_orfas) {/* Solicitações que não possuem nenhuma familia. */
            try {
                /*
                 * Para cada solicitação órfã.
                 */
                foreach ($solicitacoes as $dados_solic) {
                    if ($dados_solic["SSOL_ID_DOCUMENTO"] != $antigaSolicitacoesOrfas) {/* EVITA VINCULAÇÃO DO PRINCIPAL COM ELE MESMO */
                        /*
                         * Popula Array para incluir na tabela de vinculação
                         */
                        $data["VIDC_ID_DOC_PRINCIPAL"] = $antigaSolicitacoesOrfas;
                        $data["VIDC_ID_DOC_VINCULADO"] = $dados_solic["SSOL_ID_DOCUMENTO"];
                        $data["VIDC_DH_VINCULACAO"] = $datahora;
                        $data["VIDC_ID_TP_VINCULACAO"] = 4;
                        $data["VIDC_CD_MATR_VINCULACAO"] = $userNs->matricula;
                        $row = $SadTbVidcVinculacaoDoc->createRow($data);
                        $id_vinculacao = $row->save();
                        /*
                         * INSERÇÃO DE FASE NA VINCULAÇÃO DE SOMENTE ÓRFAS
                         * $qr recupera o id da movimentação da solicitação
                         */
                        $qr = $this->getDadosSolicitacao($dados_solic["SSOL_ID_DOCUMENTO"]);
                        $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                        /* Data e hora da vinculação */
                        $dataInfo["MOFA_DH_FASE"] = $datahora;
                        /* Matricula de quem fez a vinculação da solicitação */
                        $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        /* Justificativa do usuário para a vinculação */
                        $dataInfo["MOFA_DS_COMPLEMENTO"] = $justificativa;
                        /* Fase de Vinculação de solicitações */
                        $dataInfo['MOFA_ID_FASE'] = 1035;
                        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
                        $rowMofaMoviFase->save();

                        //Ultima Fase do lançada na Solicitação.//
                        /* ---------------------------------------------------------------------------------------- */

                        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfo["MOFA_ID_MOVIMENTACAO"];
                        $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($dados_solic["SSOL_ID_DOCUMENTO"])->current();
                        ;
                        $rowUltima_fase->setFromArray($dataUltima_fase);
                        $rowUltima_fase->save();
                        /* ---------------------------------------------------------------------------------------- */

                        $dataVidcAuditoria['VIDC_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dataVidcAuditoria['VIDC_IC_OPERACAO'] = 'I';
                        $dataVidcAuditoria['VIDC_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                        $dataVidcAuditoria['VIDC_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                        $dataVidcAuditoria['VIDC_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                        $dataVidcAuditoria['OLD_VIDC_ID_VINCULACAO_DOC'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_VINCULACAO_DOC'] = $id_vinculacao;

                        $dataVidcAuditoria['OLD_VIDC_ID_DOC_PRINCIPAL'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_DOC_PRINCIPAL'] = $antigaSolicitacoesOrfas;

                        $dataVidcAuditoria['OLD_VIDC_ID_DOC_VINCULADO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_DOC_VINCULADO'] = $dados_solic["SSOL_ID_DOCUMENTO"];

                        $dataVidcAuditoria['OLD_VIDC_ID_TP_VINCULACAO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_TP_VINCULACAO'] = 4;

                        $dataVidcAuditoria['OLD_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_DH_VINCULACAO'] = $datahora;

                        $dataVidcAuditoria['OLD_VIDC_CD_MATR_VINCULACAO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
                        $rowVidcAuditoria = $SadTbVidcAuditoria->createRow($dataVidcAuditoria);
                        $rowVidcAuditoria->save();
                    }
                }
                /* Lancamento de fase na orfa mais antiga */
                $qr = $this->getDadosSolicitacao($antigaSolicitacoesOrfas);
                $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                /* Data e hora da vinculação */
                $dataInfo["MOFA_DH_FASE"] = $datahora;
                /* Matricula de quem fez a vinculação da solicitação */
                $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula;
                /* Justificativa do usuário para a vinculação */
                $dataInfo["MOFA_DS_COMPLEMENTO"] = $justificativa;
                /* Fase de Vinculação de solicitações */
                $dataInfo['MOFA_ID_FASE'] = 1035;
                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
                $rowMofaMoviFase->save();

                //Ultima Fase do lançada na Solicitação.//
                /* ---------------------------------------------------------------------------------------- */

                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfo["MOFA_ID_MOVIMENTACAO"];
                $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                $rowUltima_fase = $tabelaSadTbDocmDocumento->find($antigaSolicitacoesOrfas)->current();
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();
                /* ---------------------------------------------------------------------------------------- */

                /*
                 * Salva em banco o disposto acima.
                 */
                $db->commit();
                return true;
            } catch (Zend_Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {/* Caso em que há pelo menos um membro de qualquer familia. */
            /**
             * Desfaz as outras familias
             */
            try {
                /*
                 * Para Cada Solicitação
                 */
                foreach ($solicitacoes as $dados_solic) {
//                    Zend_Debug::dump($dados_solic["SSOL_ID_DOCUMENTO"],'$dados_solic["SSOL_ID_DOCUMENTO"]');
//                    Zend_Debug::dump($antigaPrincipais,'$antigaPrincipais');
                    /*
                     * Se a mais antiga for diferente da principal de alguma solicitação. 
                     * Motivo: evita desfazer a familia da mais antiga.
                     */
                    if (!($dados_solic["VIDC_ID_DOC_PRINCIPAL"] == $antigaPrincipais)) {
                        /*
                         * Cada vinculação da principal é apagada.
                         */
                        foreach ($dados_solic['VIDC_ID_VINCULACAO_DOCUMENTO'] as $filho) {
                            /*
                             * Verifica se ID de Vinculação está no Array.
                             * Motivo: Evita valores NULL.
                             */
                            if (isset($dados_solic["VIDC_ID_VINCULACAO_DOCUMENTO"])) {
                                $row = $SadTbVidcVinculacaoDoc->find($filho)->current();
                                $sql = "SELECT VIDC_ID_VINCULACAO_DOCUMENTO,
                                             VIDC_ID_DOC_PRINCIPAL,
                                             VIDC_ID_DOC_VINCULADO,
                                             VIDC_ID_TP_VINCULACAO,
                                             TO_CHAR(VIDC_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIDC_DH_VINCULACAO,
                                             VIDC_CD_MATR_VINCULACAO 
                                      FROM SAD_TB_VIDC_VINCULACAO_DOC
                                      WHERE VIDC_ID_VINCULACAO_DOCUMENTO = $filho";
                                $dataAuditoriaOld = $db->query($sql)->fetch();
                                /*
                                 * Verifica se ID de Vinculação foi apagado.
                                 * Evita: Tentativade exclusão de linha que não existe.
                                 */
                                if ($row) {
//                                    $dataAuditoriaOld = $row->toArray();
                                    Zend_Debug::dump($dataAuditoriaOld, '$dataAuditoriaOld');
                                    $row->delete();
                                    $dataVidcAuditoria['VIDC_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataVidcAuditoria['VIDC_IC_OPERACAO'] = 'E';
                                    $dataVidcAuditoria['VIDC_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                    $dataVidcAuditoria['VIDC_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                    $dataVidcAuditoria['VIDC_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                                    $dataVidcAuditoria['OLD_VIDC_ID_VINCULACAO_DOC'] = $filho;
                                    $dataVidcAuditoria['NEW_VIDC_ID_VINCULACAO_DOC'] = new Zend_Db_Expr("NULL");

                                    $dataVidcAuditoria['OLD_VIDC_ID_DOC_PRINCIPAL'] = $dataAuditoriaOld['VIDC_ID_DOC_PRINCIPAL'];
                                    $dataVidcAuditoria['NEW_VIDC_ID_DOC_PRINCIPAL'] = new Zend_Db_Expr("NULL");

                                    $dataVidcAuditoria['OLD_VIDC_ID_DOC_VINCULADO'] = $dataAuditoriaOld["VIDC_ID_DOC_VINCULADO"];
                                    $dataVidcAuditoria['NEW_VIDC_ID_DOC_VINCULADO'] = new Zend_Db_Expr("NULL");

                                    $dataVidcAuditoria['OLD_VIDC_ID_TP_VINCULACAO'] = 4;
                                    $dataVidcAuditoria['NEW_VIDC_ID_TP_VINCULACAO'] = new Zend_Db_Expr("NULL");

                                    $dataVidcAuditoria['OLD_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("TO_DATE( '" . $dataAuditoriaOld["VIDC_DH_VINCULACAO"] . "' ,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataVidcAuditoria['NEW_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("NULL");

                                    $dataVidcAuditoria['OLD_VIDC_CD_MATR_VINCULACAO'] = $dataAuditoriaOld["VIDC_CD_MATR_VINCULACAO"];
                                    $dataVidcAuditoria['NEW_VIDC_CD_MATR_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                    $rowVidcAuditoria = $SadTbVidcAuditoria->createRow($dataVidcAuditoria);
//                                    Zend_Debug::dump($rowVidcAuditoria->toArray());exit;
                                    $rowVidcAuditoria->save();
                                }
                            }
                        }
                    }
                }
                /**
                 * Após desfazer familia das solicitaçoes principais diferente
                 * das antigas
                 * 
                 * 
                 * Vincula as antigas familias
                 * 
                 * Para Cada solicitação:
                 */
                foreach ($solicitacoes as $dados_solic) {
//                Zend_Debug::dump($dados_solic["SSOL_ID_DOCUMENTO"],'$dados_solic["SSOL_ID_DOCUMENTO"]');
//                Zend_Debug::dump($antigaPrincipais,'$antigaPrincipais');
                    /*
                     * Verifica se na solicitação há uma solicitação principal.
                     */
                    if (isset($dados_solic["VIDC_ID_DOC_PRINCIPAL"])) {
                        /*
                         * Se a mais antiga for diferente da principal de alguma solicitação. 
                         * Motivo: evita vincular novamente a familia da mais antiga.
                         */
                        if (!($dados_solic["VIDC_ID_DOC_PRINCIPAL"] == $antigaPrincipais)) {
//                        echo '<br>começo----------------------------';
                            /*
                             * Para cada membro de uma dada família, vincula os órfãos a uma nova principal.
                             */
                            foreach ($dados_solic['VIDC_ID_DOC_VINCULADO'] as $orfao) {
                                /*
                                 * Verificação se existe vinculação igual. 
                                 */
                                $existe = $SadTbVidcVinculacaoDoc->fetchRow("VIDC_ID_DOC_PRINCIPAL = $antigaPrincipais AND VIDC_ID_DOC_VINCULADO = $orfao");
                                /*
                                 * Caso não exista, vincula. 
                                 */
                                if ($existe == NULL) {
                                    /*
                                     * Insere fase de desvinculação do antigo pai de outra familia.
                                     */
//                                    $qr = $this->getDadosSolicitacao($orfao);
//                                    $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
//                                    $dataInfo["MOFA_DH_FASE"] = $datahora;
//                                    $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
//                                    $qr = $this->getDadosSolicitacao($antigaPrincipais);
//                                    $dataInfo["MOFA_DS_COMPLEMENTO"] = "Solicitação Desvinculada automaticamente e vinculada à: ".$qr["DOCM_NR_DOCUMENTO"];
//                                    $dataInfo['MOFA_ID_FASE'] = 1036; // Desvinculação de solicitações
//                                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
//                                    Zend_Debug::dump($rowMofaMoviFase->toArray(),'$rowMofaMoviFase');
//                                    $rowMofaMoviFase->save();
                                    /*
                                     * Vincula à nova família.
                                     */
                                    $data["VIDC_ID_DOC_PRINCIPAL"] = $antigaPrincipais;
                                    $data["VIDC_ID_DOC_VINCULADO"] = $orfao;
                                    $data["VIDC_DH_VINCULACAO"] = $datahora;
                                    $data["VIDC_ID_TP_VINCULACAO"] = 4;
                                    $data["VIDC_CD_MATR_VINCULACAO"] = $userNs->matricula;
                                    $row = $SadTbVidcVinculacaoDoc->createRow($data);
//                                    Zend_Debug::dump($row->toArray(),'$row');
                                    $id_vinculacao = $row->save();
                                    /*
                                     * Insere fase nova fase de vinculação na solicitação órfã
                                     */
                                    $qr = $this->getDadosSolicitacao($orfao);
                                    $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                                    $dataInfo["MOFA_DH_FASE"] = $datahora;
                                    $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                                    $dataInfo["MOFA_DS_COMPLEMENTO"] = $justificativa;
                                    $dataInfo['MOFA_ID_FASE'] = 1037; // Agregação a solicitações já vinculadas.
                                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
//                                    Zend_Debug::dump($rowMofaMoviFase->toArray(),'$rowMofaMoviFase');
                                    $rowMofaMoviFase->save();

                                    //Ultima Fase do lançada na Solicitação.//
                                    /* ---------------------------------------------------------------------------------------- */

                                    $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfo["MOFA_ID_MOVIMENTACAO"];
                                    $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                                    $rowUltima_fase = $tabelaSadTbDocmDocumento->find($dados_solic["SSOL_ID_DOCUMENTO"])->current();
                                    ;
                                    $rowUltima_fase->setFromArray($dataUltima_fase);
                                    $rowUltima_fase->save();
                                    /* ---------------------------------------------------------------------------------------- */
                                    /* Dados para Auditoria */
                                    $dataVidcAuditoria['VIDC_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataVidcAuditoria['VIDC_IC_OPERACAO'] = 'I';
                                    $dataVidcAuditoria['VIDC_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                    $dataVidcAuditoria['VIDC_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                    $dataVidcAuditoria['VIDC_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                                    $dataVidcAuditoria['OLD_VIDC_ID_VINCULACAO_DOC'] = new Zend_Db_Expr("NULL");
                                    $dataVidcAuditoria['NEW_VIDC_ID_VINCULACAO_DOC'] = $id_vinculacao;

                                    $dataVidcAuditoria['OLD_VIDC_ID_DOC_PRINCIPAL'] = new Zend_Db_Expr("NULL");
                                    $dataVidcAuditoria['NEW_VIDC_ID_DOC_PRINCIPAL'] = $antigaPrincipais;

                                    $dataVidcAuditoria['OLD_VIDC_ID_DOC_VINCULADO'] = new Zend_Db_Expr("NULL");
                                    $dataVidcAuditoria['NEW_VIDC_ID_DOC_VINCULADO'] = $orfao;

                                    $dataVidcAuditoria['OLD_VIDC_ID_TP_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                    $dataVidcAuditoria['NEW_VIDC_ID_TP_VINCULACAO'] = 4;

                                    $dataVidcAuditoria['OLD_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                    $dataVidcAuditoria['NEW_VIDC_DH_VINCULACAO'] = $datahora;

                                    $dataVidcAuditoria['OLD_VIDC_CD_MATR_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                    $dataVidcAuditoria['NEW_VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
                                    $rowVidcAuditoria = $SadTbVidcAuditoria->createRow($dataVidcAuditoria);
                                    $rowVidcAuditoria->save();
                                }
                            }
                            /*
                             * Neste caso vincula o antigo pai ao novo pai (principal). 
                             */
                            $existe = $SadTbVidcVinculacaoDoc->fetchRow("VIDC_ID_DOC_PRINCIPAL = $antigaPrincipais AND VIDC_ID_DOC_VINCULADO = $dados_solic[VIDC_ID_DOC_PRINCIPAL]");
                            /*
                             * Caso não exista, vincula. 
                             */
                            if ($existe == NULL) {
                                $data["VIDC_ID_DOC_PRINCIPAL"] = $antigaPrincipais;
                                $data["VIDC_ID_DOC_VINCULADO"] = $dados_solic["VIDC_ID_DOC_PRINCIPAL"];
                                $data["VIDC_DH_VINCULACAO"] = $datahora;
                                $data["VIDC_ID_TP_VINCULACAO"] = 4;
                                $data["VIDC_CD_MATR_VINCULACAO"] = $userNs->matricula;
                                $row = $SadTbVidcVinculacaoDoc->createRow($data);
//                            Zend_Debug::dump($row->toArray(),'orfao antigo pai');
                                $id_vinculacao = $row->save();

                                /*
                                 * Insere fase no antigo pai de outra familia.
                                 */
                                $dataInfo["MOFA_ID_MOVIMENTACAO"] = $dados_solic["MOFA_ID_MOVIMENTACAO"];
                                $dataInfo["MOFA_DH_FASE"] = $datahora;
                                $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                                $dataInfo["MOFA_DS_COMPLEMENTO"] = $justificativa;
                                $dataInfo['MOFA_ID_FASE'] = 1037; // Agregação de solicitações já vinculadas
                                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
//                            Zend_Debug::dump($rowMofaMoviFase->toArray());
                                $rowMofaMoviFase->save();

                                //                        //Ultima Fase do lançada na Solicitação.//
                                /* ---------------------------------------------------------------------------------------- */

                                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfo["MOFA_ID_MOVIMENTACAO"];
                                $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                                $rowUltima_fase = $tabelaSadTbDocmDocumento->find($dados_solic["VIDC_ID_DOC_PRINCIPAL"])->current();
                                ;
                                $rowUltima_fase->setFromArray($dataUltima_fase);
                                $rowUltima_fase->save();
                                /* ---------------------------------------------------------------------------------------- */

                                /* Dados para Auditoria */
                                $dataVidcAuditoria['VIDC_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                $dataVidcAuditoria['VIDC_IC_OPERACAO'] = 'I';
                                $dataVidcAuditoria['VIDC_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                $dataVidcAuditoria['VIDC_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                $dataVidcAuditoria['VIDC_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                                $dataVidcAuditoria['OLD_VIDC_ID_VINCULACAO_DOC'] = new Zend_Db_Expr("NULL");
                                $dataVidcAuditoria['NEW_VIDC_ID_VINCULACAO_DOC'] = $id_vinculacao;

                                $dataVidcAuditoria['OLD_VIDC_ID_DOC_PRINCIPAL'] = new Zend_Db_Expr("NULL");
                                $dataVidcAuditoria['NEW_VIDC_ID_DOC_PRINCIPAL'] = $antigaPrincipais;

                                $dataVidcAuditoria['OLD_VIDC_ID_DOC_VINCULADO'] = new Zend_Db_Expr("NULL");
                                $dataVidcAuditoria['NEW_VIDC_ID_DOC_VINCULADO'] = $dados_solic["VIDC_ID_DOC_PRINCIPAL"];

                                $dataVidcAuditoria['OLD_VIDC_ID_TP_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                $dataVidcAuditoria['NEW_VIDC_ID_TP_VINCULACAO'] = 4;

                                $dataVidcAuditoria['OLD_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                $dataVidcAuditoria['NEW_VIDC_DH_VINCULACAO'] = $datahora;

                                $dataVidcAuditoria['OLD_VIDC_CD_MATR_VINCULACAO'] = new Zend_Db_Expr("NULL");
                                $dataVidcAuditoria['NEW_VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
                                $rowVidcAuditoria = $SadTbVidcAuditoria->createRow($dataVidcAuditoria);
                                $rowVidcAuditoria->save();
                            }
//                        echo '<br>fim----------------------------';
                        }
                    } else {
                        /*
                         * Caso não haja um ID_DOC_PRINCIPAL significa que a solicitação é órfã.
                         * 
                         * $antigaPrincipais guarda o ID da mais antiga entre os pais das familias.
                         */
                        $data["VIDC_ID_DOC_PRINCIPAL"] = $antigaPrincipais;
                        $data["VIDC_ID_DOC_VINCULADO"] = $dados_solic["SSOL_ID_DOCUMENTO"];
                        $data["VIDC_DH_VINCULACAO"] = $datahora;
                        $data["VIDC_ID_TP_VINCULACAO"] = 4;
                        $data["VIDC_CD_MATR_VINCULACAO"] = $userNs->matricula;
                        $row = $SadTbVidcVinculacaoDoc->createRow($data);
//                    Zend_Debug::dump($row->toArray(),'orfas originais');
                        $id_vinculacao = $row->save();
//                    Zend_Debug::dump($id_vinculacao);exit;
                        /*
                         * Insere fase na solicitação filha.
                         */
                        $qr = $this->getDadosSolicitacao($dados_solic["SSOL_ID_DOCUMENTO"]);
                        $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                        $dataInfo["MOFA_DH_FASE"] = $datahora;
                        $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                        $dataInfo["MOFA_DS_COMPLEMENTO"] = $justificativa;
                        $dataInfo['MOFA_ID_FASE'] = 1035; // Vinculação de solicitações
                        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
//                    Zend_Debug::dump($rowMofaMoviFase,'$rowMofaMoviFase');
                        $rowMofaMoviFase->save();

                        //Ultima Fase do lançada na Solicitação Filha.//
                        /* ---------------------------------------------------------------------------------------- */

                        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                        $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($dados_solic["SSOL_ID_DOCUMENTO"])->current();
                        ;
                        $rowUltima_fase->setFromArray($dataUltima_fase);
//                    Zend_Debug::dump($rowUltima_fase->toArray(),'$rowUltima_fase');
//                                    exit;
                        $rowUltima_fase->save();
                        /* ---------------------------------------------------------------------------------------- */

                        /*
                         * Insere fase na solicitação principal.
                         */
                        sleep(1);
                        $datahora3s = $Dual->sysdate();
                        $qr = $this->getDadosSolicitacao($antigaPrincipais);
                        $dataInfoPrincipal["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                        $dataInfoPrincipal["MOFA_DH_FASE"] = $datahora3s;
                        $dataInfoPrincipal["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                        $dataInfoPrincipal["MOFA_DS_COMPLEMENTO"] = $justificativa;
                        $dataInfoPrincipal['MOFA_ID_FASE'] = 1035; // Vinculação de solicitações
                        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfoPrincipal);
//                    Zend_Debug::dump($rowMofaMoviFase->toArray(),'$rowMofaMoviFase');
                        $rowMofaMoviFase->save();

//                        //Ultima Fase do lançada na Solicitação Pai.//
                        /* ---------------------------------------------------------------------------------------- */

                        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfoPrincipal["MOFA_ID_MOVIMENTACAO"];
                        $dataUltima_fase["DOCM_DH_FASE"] = $datahora3s;
                        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($antigaPrincipais)->current();
                        ;
                        $rowUltima_fase->setFromArray($dataUltima_fase);
                        $rowUltima_fase->save();

//                 

                        /* Dados para Auditoria */
                        $dataVidcAuditoria['VIDC_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dataVidcAuditoria['VIDC_IC_OPERACAO'] = 'I';
                        $dataVidcAuditoria['VIDC_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                        $dataVidcAuditoria['VIDC_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                        $dataVidcAuditoria['VIDC_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                        $dataVidcAuditoria['OLD_VIDC_ID_VINCULACAO_DOC'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_VINCULACAO_DOC'] = $id_vinculacao;

                        $dataVidcAuditoria['OLD_VIDC_ID_DOC_PRINCIPAL'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_DOC_PRINCIPAL'] = $antigaPrincipais;

                        $dataVidcAuditoria['OLD_VIDC_ID_DOC_VINCULADO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_DOC_VINCULADO'] = $dados_solic["SSOL_ID_DOCUMENTO"];

                        $dataVidcAuditoria['OLD_VIDC_ID_TP_VINCULACAO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_ID_TP_VINCULACAO'] = 4;

                        $dataVidcAuditoria['OLD_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_DH_VINCULACAO'] = $datahora;

                        $dataVidcAuditoria['OLD_VIDC_CD_MATR_VINCULACAO'] = new Zend_Db_Expr("NULL");
                        $dataVidcAuditoria['NEW_VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
                        $rowVidcAuditoria = $SadTbVidcAuditoria->createRow($dataVidcAuditoria);
                        $rowVidcAuditoria->save();
                    }
                }
//            $db->rollBack();
//            exit('viculou novamente');
                $db->commit();
                return true;
            } catch (Zend_Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
//        exit ("nada");
    }

    public function setDesvincularSolicitacoes ($solicitacoes, $principal, $justificativa) {
        $userNs = new Zend_Session_Namespace('userNs');
        $Dual = new Application_Model_DbTable_Dual();
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SadTbVidcAuditoria();
        $vidc_id_vinculacao_documento = $SadTbVidcVinculacaoDoc->fetchRow("VIDC_ID_DOC_PRINCIPAL = $principal AND VIDC_ID_DOC_VINCULADO = $solicitacoes");
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        $datahora = $Dual->sysdate();
        try {
            $row = $SadTbVidcVinculacaoDoc->find($vidc_id_vinculacao_documento["VIDC_ID_VINCULACAO_DOCUMENTO"])->current();
            $dataAuditoriaOld = $row->toArray();
            if ($row) {
                $row->delete();
                /**
                 * Fase de desvinculação de solicitações
                 */
                $qr = $this->getDadosSolicitacao($solicitacoes);
                $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                $dataInfo["MOFA_DH_FASE"] = $datahora;
                $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                /* $qr = $this->getDadosSolicitacao($principal); */
                $dataInfo["MOFA_DS_COMPLEMENTO"] = $justificativa;
                $dataInfo['MOFA_ID_FASE'] = 1036; // Desvinculação de solicitações
                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
                $rowMofaMoviFase->save();
                //Ultima Fase do lançada na Solicitação.//
                /* ---------------------------------------------------------------------------------------- */
                $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfo["MOFA_ID_MOVIMENTACAO"];
                $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                $rowUltima_fase = $tabelaSadTbDocmDocumento->find($solicitacoes)->current();
                ;
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();
                /* ---------------------------------------------------------------------------------------- */

                $qr = $this->getDadosSolicitacao($principal);
                $dataInfoDesvPrinc["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                $dataInfoDesvPrinc["MOFA_DH_FASE"] = $datahora;
                $dataInfoDesvPrinc["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                $dataInfoDesvPrinc["MOFA_DS_COMPLEMENTO"] = $justificativa;
                $dataInfoDesvPrinc['MOFA_ID_FASE'] = 1041; // Desvinculação de solicitações FILHAS
                $rowMofaMoviFaseDesvPrinc = $SadTbMofaMoviFase->createRow($dataInfoDesvPrinc);
//                    Zend_Debug::dump($rowMofaMoviFase,'$rowMofaMoviFase');
                $rowMofaMoviFaseDesvPrinc->save();

                //Ultima Fase do lançada na Solicitação Pai.//
                /* ---------------------------------------------------------------------------------------- */

                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                $rowUltima_fase = $tabelaSadTbDocmDocumento->find($principal)->current();
                ;
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();

                $dataVidcAuditoria['VIDC_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                $dataVidcAuditoria['VIDC_IC_OPERACAO'] = 'E';
                $dataVidcAuditoria['VIDC_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                $dataVidcAuditoria['VIDC_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $dataVidcAuditoria['VIDC_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                $dataVidcAuditoria['OLD_VIDC_ID_VINCULACAO_DOC'] = $vidc_id_vinculacao_documento["VIDC_ID_VINCULACAO_DOCUMENTO"];
                $dataVidcAuditoria['NEW_VIDC_ID_VINCULACAO_DOC'] = new Zend_Db_Expr("NULL");

                $dataVidcAuditoria['OLD_VIDC_ID_DOC_PRINCIPAL'] = $dataAuditoriaOld['VIDC_ID_DOC_PRINCIPAL'];
                $dataVidcAuditoria['NEW_VIDC_ID_DOC_PRINCIPAL'] = new Zend_Db_Expr("NULL");

                $dataVidcAuditoria['OLD_VIDC_ID_DOC_VINCULADO'] = $dataAuditoriaOld["VIDC_ID_DOC_VINCULADO"];
                $dataVidcAuditoria['NEW_VIDC_ID_DOC_VINCULADO'] = new Zend_Db_Expr("NULL");

                $dataVidcAuditoria['OLD_VIDC_ID_TP_VINCULACAO'] = 4;
                $dataVidcAuditoria['NEW_VIDC_ID_TP_VINCULACAO'] = new Zend_Db_Expr("NULL");

                $dataVidcAuditoria['OLD_VIDC_DH_VINCULACAO'] = $dataAuditoriaOld["VIDC_DH_VINCULACAO"];
                $dataVidcAuditoria['NEW_VIDC_DH_VINCULACAO'] = new Zend_Db_Expr("NULL");

                $dataVidcAuditoria['OLD_VIDC_CD_MATR_VINCULACAO'] = $dataAuditoriaOld["VIDC_CD_MATR_VINCULACAO"];
                $dataVidcAuditoria['NEW_VIDC_CD_MATR_VINCULACAO'] = new Zend_Db_Expr("NULL");
                $rowVidcAuditoria = $SadTbVidcAuditoria->createRow($dataVidcAuditoria);
                $rowVidcAuditoria->save();
            }
            $db->commit();
        } catch (Zend_Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function setDesvincularSolicitacoesRecusada ($id, $solicitacoes) {
        $userNs = new Zend_Session_Namespace('userNs');
        $Dual = new Application_Model_DbTable_Dual();
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
//        $vidc_id_vinculacao_documento = $SadTbVidcVinculacaoDoc->fetchRow("VIDC_ID_DOC_PRINCIPAL = $principal AND VIDC_ID_DOC_VINCULADO = $solicitacoes");
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        $datahora = $Dual->sysdate();
//        Zend_Debug::dump($id);
//                        EXIT;
        foreach ($id as $value) {
            foreach ($value as $id_vinculacao_documento) {
                try {
                    $row = $SadTbVidcVinculacaoDoc->find($id_vinculacao_documento)->current();
                    if ($row) {
                        $row->delete();
                        /**
                         * Fase de desvinculação de solicitações
                         */
                        $qr = $this->getDadosSolicitacao($solicitacoes);
                        $dataInfo["MOFA_ID_MOVIMENTACAO"] = $qr["MOFA_ID_MOVIMENTACAO"];
                        $dataInfo["MOFA_DH_FASE"] = $datahora;
                        $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez a vinculação da solicitação 
                        $dataInfo["MOFA_DS_COMPLEMENTO"] = 'Desvinculação automática por motivo de recusa';
                        $dataInfo['MOFA_ID_FASE'] = 1036; // Desvinculação de solicitações
                        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataInfo);
                        $rowMofaMoviFase->save();

                        //Ultima Fase do lançada na Solicitação.//
                        /* ---------------------------------------------------------------------------------------- */
                        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataInfo["MOFA_ID_MOVIMENTACAO"];
                        $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($solicitacoes)->current();
                        ;
                        $rowUltima_fase->setFromArray($dataUltima_fase);
                        $rowUltima_fase->save();
                        /* ---------------------------------------------------------------------------------------- */
                    }
                    $db->commit();
                } catch (Zend_Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }
        }
    }

    public function getNaoConformidades ($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UPPER(SOTC_DS_CONFORMIDADE) SOTC_DS_CONFORMIDADE,
                                 MVCO_DS_JUSTIF_N_CONFORMIDADE,
                                 SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MVCO_CD_MATRICULA_INCLUSAO) MVCO_CD_MATRICULA_INCLUSAO,
                                   TO_CHAR(MVCO_DH_INCLUSAO ,'dd/mm/yyyy HH24:MI:SS') MVCO_DH_INCLUSAO
                            FROM SOS_TB_MVCO_MOVIM_N_CONFORM
                            INNER JOIN SOS_TB_SOTC_TP_N_CONFORMIDADE
                            ON MVCO_ID_NAO_CONFORMIDADE = SOTC_ID_NAO_CONFORMIDADE
                            WHERE MVCO_ID_MOVIMENTACAO IN (SELECT MODO_ID_MOVIMENTACAO
                                                        FROM SAD_TB_DOCM_DOCUMENTO
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO
                                                        ON DOCM_ID_DOCUMENTO = MODO_ID_DOCUMENTO
                                                        WHERE DOCM_ID_DOCUMENTO = $idDocumento)
                            AND MVCO_IC_ATIVO_INATIVO = 'S'
                            ORDER BY MVCO_DH_INCLUSAO DESC");
        return $stmt->fetchAll();
    }

    /**
     * Verifica se existem mais de uma caixa pessoal dentro das solicitações
     * em posse de algum atendente
     * 
     * @param	$matricula
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function hasVariasCaixasPessoaisSolicitacoes ($matricula) {
        //REFAZER A QUERY COM MAIS TEMPO
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT COUNT(DISTINCT MODE_ID_CAIXA_ENTRADA) QTD_REGISTROS FROM (
                            SELECT DISTINCT
                                SSOL.SSOL_ID_DOCUMENTO,
                                MODE_ID_CAIXA_ENTRADA
                            FROM

                            -- solicitacao    
                            SOS_TB_SSOL_SOLICITACAO SSOL

                            -- documento
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                            -- documento movimentacao
                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                            ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

                            -- movimentacao origem
                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                            ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

                            -- movimentacao destino
                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                            ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

                            --fase
                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                            ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO

                                        WHERE

                            --Ãºltima movimentaÃ§Ã£o

                            (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                            WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                            ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                            ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                            AND DOCM_1.DOCM_ID_TIPO_DOC = 160)
                            )

                                AND 

                            -- em atendimento
                            -- dispensa fases
                            -- 1000 baixa
                            -- 1014 avaliada
                            -- 1026 cancelada
                            MOFA.MOFA_ID_FASE NOT IN (1000, 1014, 1026)                                                                                  

                                AND 

                                -- tipo documento solicitacao
                                DOCM.DOCM_ID_TIPO_DOC = 160                                                                               

                                AND 

                                -- Com o atendente
                                SSOL.SSOL_CD_MATRICULA_ATENDENTE = '$matricula'
            )";
        $array = $db->fetchRow($sql);
        return ($array['QTD_REGISTROS'] > 1) ? true : false;
    }

    /**
     * Retorna as caixas pessoais dentro das solicitações
     * em posse de algum atendente
     * 
     * @param	$matricula
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getCaixasPessoaisSolicitacoes ($matricula) {
        //REFAZER A QUERY COM MAIS TEMPO
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT DISTINCT CXEN_ID_CAIXA_ENTRADA, CXEN_DS_CAIXA_ENTRADA FROM (
                            SELECT DISTINCT
                                SSOL.SSOL_ID_DOCUMENTO,
                                MODE_ID_CAIXA_ENTRADA
                            FROM

                            -- solicitacao    
                            SOS_TB_SSOL_SOLICITACAO SSOL

                            -- documento
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                            -- documento movimentacao
                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                            ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

                            -- movimentacao origem
                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                            ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

                            -- movimentacao destino
                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                            ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

                            --fase
                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                            ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO

                                        WHERE

                            --Ãºltima movimentaÃ§Ã£o

                            (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                            WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                            ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                            ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                            AND DOCM_1.DOCM_ID_TIPO_DOC = 160)
                            )

                                AND 

                            -- em atendimento
                            -- dispensa fases
                            -- 1000 baixa
                            -- 1014 avaliada
                            -- 1026 cancelada
                            MOFA.MOFA_ID_FASE NOT IN (1000, 1014, 1026)                                                                                  

                                AND 

                                -- tipo documento solicitacao
                                DOCM.DOCM_ID_TIPO_DOC = 160                                                                               

                                AND 

                                -- Com o atendente
                                SSOL.SSOL_CD_MATRICULA_ATENDENTE = '$matricula'
            )
            INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA
                ON MODE_ID_CAIXA_ENTRADA = CXEN_ID_CAIXA_ENTRADA
        
        ";
        return $db->fetchAll($sql);
    }

    /**
     * Verifica se tem a caixa pessoal especificada dentro das solicitações do atendente de caixa
     * em posse de algum atendente
     * 
     * @param	$matricula
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function hasCaixaPessoalSolicitacao ($idCaixaPessoal, $matricula) {
        //REFAZER A QUERY COM MAIS TEMPO
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT COUNT(DISTINCT MODE_ID_CAIXA_ENTRADA) QTD_REGISTROS FROM (
                            SELECT DISTINCT
                                SSOL.SSOL_ID_DOCUMENTO,
                                MODE_ID_CAIXA_ENTRADA
                            FROM

                            -- solicitacao    
                            SOS_TB_SSOL_SOLICITACAO SSOL

                            -- documento
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                            -- documento movimentacao
                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                            ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

                            -- movimentacao origem
                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                            ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

                            -- movimentacao destino
                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                            ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

                            --fase
                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                            ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO

                                        WHERE

                            --Ãºltima movimentaÃ§Ã£o

                            (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                            WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                            ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                            ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                            AND DOCM_1.DOCM_ID_TIPO_DOC = 160)
                            )

                                AND 

                            -- em atendimento
                            -- dispensa fases
                            -- 1000 baixa
                            -- 1014 avaliada
                            -- 1026 cancelada
                            MOFA.MOFA_ID_FASE NOT IN (1000, 1014, 1026)                                                                                  

                                AND 

                                -- tipo documento solicitacao
                                DOCM.DOCM_ID_TIPO_DOC = 160                                                                               

                                AND 

                                -- Com o atendente
                                SSOL.SSOL_CD_MATRICULA_ATENDENTE = '$matricula'
                                    
                                AND
        
                                -- Com a caixa
                                MODE_ID_CAIXA_ENTRADA = '$idCaixaPessoal'
            )
        ";
        $array = $db->fetchRow($sql);
        return ($array['QTD_REGISTROS'] == 1) ? true : false;
    }

    /**
     * Lista as solicitações com primeiro atendimento
     * @param type $grupo
     * @param type $nivel
     * @param type $data_inicial
     * @param type $data_final
     * @param type $order
     * @param type $avaliacao
     * @return type (array contendo as solicitações do primeiro atendimento)
     */
    public function getSolicitacoesPrimeiroAtendimentoSla ($caixa, $data_inicial, $data_final, $order) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $sql = "SELECT
                SSOL_ID_DOCUMENTO,
                DOCM_NR_DOCUMENTO, 
                TO_CHAR(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY hh24:mi:ss') MOVI_DH_ENCAMINHAMENTO, 
                TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY hh24:mi:ss') AS DATA_PRIMEIRO_ATENDIMENTO,
                SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA_CD_MATRICULA) NOME_ATENDENTE,
                (SELECT SSER_DS_SERVICO 
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                       INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                       ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                       AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                       INNER JOIN SOS_TB_SSER_SERVICO SSER_1
                       ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO 
                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                               FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                               INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                               ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                               AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                               WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = SUB_5.MOVI_ID_MOVIMENTACAO)) SSER_DS_SERVICO
                FROM
                (
                    SELECT * FROM
                    (
                        SELECT * FROM
                            (
                                SELECT 
                                SUB_2.*,
                                MOFA.*,
                                (SELECT MIN(MOFA_DH_FASE)
                                    FROM SAD_TB_MOFA_MOVI_FASE MOFA_1
                                    WHERE MOFA_1.MOFA_DH_FASE > SUB_2.MOVI_DH_ENCAMINHAMENTO
                                    AND   MOFA_1.MOFA_ID_MOVIMENTACAO = SUB_2.MOVI_ID_MOVIMENTACAO
                                ) PRIMEIRO_ATENDIMENTO
                                 FROM
                                (
                                    SELECT * FROM
                                    (
                                        SELECT * FROM SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                        WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $caixa
                                    ) SUB_1
                                    INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI
                                    ON  SUB_1.MODE_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                )SUB_2
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  SUB_2.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                            )SUB_3
                        -- documento movimentacao
                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                        ON MODO_MOVI.MODO_ID_MOVIMENTACAO = SUB_3.MOVI_ID_MOVIMENTACAO
                        WHERE SUB_3.MOFA_DH_FASE = SUB_3.PRIMEIRO_ATENDIMENTO 
                    ) SUB_4
                    -- documento
                    INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                    ON  DOCM.DOCM_ID_DOCUMENTO     = SUB_4.MODO_ID_DOCUMENTO
                    AND DOCM.DOCM_ID_TIPO_DOC = 160
                ) SUB_5
                -- solicitacao    
                INNER JOIN SOS_TB_SSOL_SOLICITACAO SSOL
                ON SSOL.SSOL_ID_DOCUMENTO = SUB_5.DOCM_ID_DOCUMENTO ";

        $sql .= ( $data_inicial && $data_final) ? (" AND PRIMEIRO_ATENDIMENTO between TO_DATE( '$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $sql .= ( ($data_inicial == "") && ($data_final != "")) ? (" AND PRIMEIRO_ATENDIMENTO between TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $sql .= ( ($data_inicial != "") && ($data_final == "")) ? (" AND PRIMEIRO_ATENDIMENTO between TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $sql .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public function getQtdeSolicitacoesPrimeiroAtendimentoSla ($caixa, $data_inicial, $data_final) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $sql = "SELECT 
                COUNT(*) QTDE
                FROM
                (
                    SELECT * FROM
                    (
                        SELECT * FROM
                            (
                                SELECT 
                                SUB_2.*,
                                MOFA.*,
                                (SELECT MIN(MOFA_DH_FASE)
                                    FROM SAD_TB_MOFA_MOVI_FASE MOFA_1
                                    WHERE MOFA_1.MOFA_DH_FASE > SUB_2.MOVI_DH_ENCAMINHAMENTO
                                    AND   MOFA_1.MOFA_ID_MOVIMENTACAO = SUB_2.MOVI_ID_MOVIMENTACAO
                                ) PRIMEIRO_ATENDIMENTO
                                 FROM
                                (
                                    SELECT * FROM
                                    (
                                        SELECT * FROM SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                        WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $caixa
                                    ) SUB_1
                                    INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI
                                    ON  SUB_1.MODE_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                )SUB_2
                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                ON  SUB_2.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                            )SUB_3
                        -- documento movimentacao
                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                        ON MODO_MOVI.MODO_ID_MOVIMENTACAO = SUB_3.MOVI_ID_MOVIMENTACAO
                        WHERE SUB_3.MOFA_DH_FASE = SUB_3.PRIMEIRO_ATENDIMENTO 
                    ) SUB_4
                    -- documento
                    INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                    ON  DOCM.DOCM_ID_DOCUMENTO     = SUB_4.MODO_ID_DOCUMENTO
                    AND DOCM.DOCM_ID_TIPO_DOC = 160
                ) SUB_5
                -- solicitacao    
                INNER JOIN SOS_TB_SSOL_SOLICITACAO SSOL
                ON SSOL.SSOL_ID_DOCUMENTO = SUB_5.DOCM_ID_DOCUMENTO
            WHERE PRIMEIRO_ATENDIMENTO BETWEEN TO_DATE('01/01/2012 00:00:00','DD/MM/YYYY hh24:mi:ss') AND TO_DATE('30/01/2013 00:00:00','DD/MM/YYYY hh24:mi:ss')";
        $sql .= ( $data_inicial && $data_final) ? (" AND PRIMEIRO_ATENDIMENTO between TO_DATE( '$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $sql .= ( ($data_inicial == "") && ($data_final != "")) ? (" AND PRIMEIRO_ATENDIMENTO between TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE( '$data_final', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");
        $sql .= ( ($data_inicial != "") && ($data_final == "")) ? (" AND PRIMEIRO_ATENDIMENTO between TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) AND TO_DATE('$data_inicial', 'DD/MM/YYYY hh24:mi:ss' ) ") : ("");

//        Zend_Debug::dump($stmt);
//        exit;
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public function getSolicitacaoInfo ($solicitacaoNR) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "
    	SELECT C.*,A.*,B.*, D.STCA_DS_TIPO_CAD
                
                
        FROM 
        SOS_TB_SSOL_SOLICITACAO A, 
        TOMBO B,
        SAD_TB_DOCM_DOCUMENTO C,
        SOS_TB_STCA_TIPO_CADASTRO D
        WHERE
        C.DOCM_NR_DOCUMENTO =$solicitacaoNR 
         AND C.DOCM_ID_DOCUMENTO = A.SSOL_ID_DOCUMENTO
         AND A.SSOL_NR_TOMBO = B.NU_TOMBO
         AND A.SSOL_ID_TIPO_CAD = D.STCA_ID_TIPO_CAD
         AND B.TI_TOMBO = 'T'
         AND B.CO_MAT LIKE '5235%'";


        return $db->query($stmt)->fetchAll();
    }

    public function getdataEntradaNivel ($documentoID, $nivel, $fase) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT  
                    TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE
                             FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                    INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                    ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                    INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                    ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                    INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                    ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                    INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                    ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                    INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                    ON MODE_MOVI.MODE_ID_CAIXA_ENTRADA = CXEN.CXEN_ID_CAIXA_ENTRADA
                    LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS
                    ON  MOFA.MOFA_ID_MOVIMENTACAO  = SNAS.SNAS_ID_MOVIMENTACAO
                    AND MOFA.MOFA_DH_FASE = SNAS.SNAS_DH_FASE
                    LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                    ON  SNAT.SNAT_ID_NIVEL         =  SNAS.SNAS_ID_NIVEL
                    LEFT JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS
                    ON MOFA.MOFA_ID_MOVIMENTACAO = SAVS.SAVS_ID_MOVIMENTACAO 
                    AND  MOFA.MOFA_DH_FASE = SAVS.SAVS_DH_FASE
                    LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA
                    ON   SAVS.SAVS_ID_TIPO_SAT = STSA.STSA_ID_TIPO_SAT
                    INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                    ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                    LEFT JOIN SAD_TB_ANEX_ANEXO ANEX
                    ON ANEX.ANEX_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO 
                    AND    ANEX.ANEX_DH_FASE = MOFA.MOFA_DH_FASE
                    AND    ANEX.ANEX_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                    INNER JOIN SAD_TB_FADM_FASE_ADM FADM
                    ON FADM.FADM_ID_FASE = MOFA.MOFA_ID_FASE
                     WHERE  DOCM.DOCM_ID_DOCUMENTO = $documentoID
                      and   FADM_ID_FASE  = $fase
                      and   SNAT_CD_NIVEL = $nivel

                     ORDER BY MOFA.MOFA_DH_FASE DESC";

        $row = $db->query($stmt)->fetchAll();
        return $row;
    }

    public function solicitacaohasTombo ($solicitacaoNR) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT C.*,A.*,B.CO_MAT, B.TI_TOMBO
                            FROM 
                            SOS_TB_SSOL_SOLICITACAO A, 
                            TOMBO B,
                            SAD_TB_DOCM_DOCUMENTO C
                            WHERE
                            C.DOCM_NR_DOCUMENTO = $solicitacaoNR 
                            AND C.docm_id_documento = A.ssol_id_documento
                            AND A.SSOL_NR_TOMBO = B.NU_TOMBO
                            AND B.TI_TOMBO = 'T'
                            AND B.CO_MAT LIKE '5235%'");
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function vincularSolicitacoes ($rows, $principal, $justificativa) {
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SadTbVidcAuditoria();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $Dual = new Application_Model_DbTable_Dual();

        if (!empty($rows)) {
            foreach ($rows as $row) {

                //Verifica vinculo
                $res = $SadTbVidcVinculacaoDoc->select()
                        ->where('vidc_id_doc_vinculado = ?', $row["SSOL_ID_DOCUMENTO"])
                        ->where('VIDC_ID_TP_VINCULACAO <> ?', 7)
                        ->query();
                $res = $res->fetchAll();
                $exists = (!empty($res)) ? true : false;
                sleep(1);
                $datahora = $Dual->sysdate();
//                $dadosSolPrinc = $this->getDadosSolicitacao($row["SSOL_ID_DOCUMENTO"]);
                $userNs = new Zend_Session_Namespace('userNs');
                if (!$exists) {
                    $id_vinculacao = new Zend_Db_Expr("NULL");
                    if ($row["SSOL_ID_DOCUMENTO"] != $principal) {
                        $rowVinc = $SadTbVidcVinculacaoDoc->createRow(array(
                            "VIDC_ID_DOC_PRINCIPAL" => $principal,
                            "VIDC_ID_DOC_VINCULADO" => $row["SSOL_ID_DOCUMENTO"],
                            "VIDC_DH_VINCULACAO" => $datahora,
                            "VIDC_ID_TP_VINCULACAO" => 4,
                            "VIDC_CD_MATR_VINCULACAO" => $userNs->matricula
                        ));
                        $id_vinculacao = $rowVinc->save();
                    }

                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow(array(
                        "MOFA_ID_MOVIMENTACAO" => $row["MOFA_ID_MOVIMENTACAO"],
                        "MOFA_DH_FASE" => $datahora,
                        "MOFA_CD_MATRICULA" => $userNs->matricula, //Matricula de quem fez a vinculação da solicitação
                        "MOFA_DS_COMPLEMENTO" => $justificativa,
                        'MOFA_ID_FASE' => 1035 //Desvinculação de solicitações
                    ));
                    $rowMofaMoviFase->save();


                    $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $row["MOFA_ID_MOVIMENTACAO"];
                    $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                    $rowUltima_fase = $tabelaSadTbDocmDocumento->find($row["SSOL_ID_DOCUMENTO"])->current();
                    $rowUltima_fase->setFromArray(array(
                        "DOCM_ID_MOVIMENTACAO" => $row["MOFA_ID_MOVIMENTACAO"],
                        "DOCM_DH_FASE" => $datahora
                    ));
                    $rowUltima_fase->save();
                    $rowVidcAuditoria = $SadTbVidcAuditoria->createRow(
                        array(
                            'VIDC_TS_OPERACAO' => new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')"),
                            'VIDC_IC_OPERACAO' => 'I',
                            'VIDC_CD_MATRICULA_OPERACAO' => $userNs->matricula,
                            'VIDC_CD_MAQUINA_OPERACAO' => substr($_SERVER['REMOTE_ADDR'], 0, 50),
                            'VIDC_CD_USUARIO_SO' => substr($_SERVER['HTTP_USER_AGENT'], 0, 50),
                            'OLD_VIDC_ID_VINCULACAO_DOC' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_VINCULACAO_DOC' => $id_vinculacao,
                            'OLD_VIDC_ID_DOC_PRINCIPAL' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_DOC_PRINCIPAL' => $principal,
                            'OLD_VIDC_ID_DOC_VINCULADO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_DOC_VINCULADO' => $row["SSOL_ID_DOCUMENTO"],
                            'OLD_VIDC_ID_TP_VINCULACAO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_TP_VINCULACAO' => 4,
                            'OLD_VIDC_DH_VINCULACAO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_DH_VINCULACAO' => $datahora,
                            'OLD_VIDC_CD_MATR_VINCULACAO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_CD_MATR_VINCULACAO' => $userNs->matricula
                        )
                    );
                    $rowVidcAuditoria->save();
                }
            }
        }
    }

    public function desvincularSolicitacoes ($rows, $principal, $principais = true, $justificativa = null, $tipoVinculacao = 4) {
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SadTbVidcAuditoria();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $Dual = new Application_Model_DbTable_Dual();

        if (empty($justificativa))
            $justificativa = "Desvinculação automática para vinculação de novas solicitações ou escolha de uma nova solicitação principal";

        if (!empty($rows)) {
            foreach ($rows as $row) {
                //Verifica vinculo
                if ($principais) {
                    $res = $SadTbVidcVinculacaoDoc->select()->where('vidc_id_doc_principal = ?', $row)->query();
                    $principal = $row;
                } else {
                    $res = $SadTbVidcVinculacaoDoc->select()->where('vidc_id_doc_vinculado = ?', $row)->query();
                }
                $res = $res->fetchAll();
                $datahora = $Dual->sysdate();
                $dadosSolPrinc = $this->getDadosSolicitacao($principal);
                foreach ($res as $rs) {
                    $linha = $SadTbVidcVinculacaoDoc->find($rs["VIDC_ID_VINCULACAO_DOCUMENTO"])->current();
                    $dadosSol = $this->getDadosSolicitacao($rs["VIDC_ID_DOC_VINCULADO"]);
                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow(array(
                        "MOFA_ID_MOVIMENTACAO" => $dadosSol["MOFA_ID_MOVIMENTACAO"],
                        "MOFA_DH_FASE" => $datahora,
                        "MOFA_CD_MATRICULA" => $userNs->matricula, //Matricula de quem fez a vinculação da solicitação
                        "MOFA_DS_COMPLEMENTO" => $justificativa,
                        'MOFA_ID_FASE' => 1036 //Desvinculação de solicitações
                    ));
                    $rowMofaMoviFase->save();
                    $rowUltima_fase = $tabelaSadTbDocmDocumento->find($rs["VIDC_ID_DOC_VINCULADO"])->current();
                    $rowUltima_fase->setFromArray(array(
                        "DOCM_ID_MOVIMENTACAO" => $dadosSol["MOFA_ID_MOVIMENTACAO"],
                        "DOCM_DH_FASE" => $datahora
                    ));
                    $rowUltima_fase->save();
                    sleep(1);
                    $datahora = $Dual->sysdate();
                    $rowMofaMoviFaseDesvPrinc = $SadTbMofaMoviFase->createRow(array(
                        "MOFA_ID_MOVIMENTACAO" => $dadosSolPrinc["MOFA_ID_MOVIMENTACAO"],
                        "MOFA_DH_FASE" => $datahora,
                        "MOFA_CD_MATRICULA" => $userNs->matricula,
                        "MOFA_DS_COMPLEMENTO" => $justificativa,
                        'MOFA_ID_FASE' => 1041
                    ));
                    $rowMofaMoviFaseDesvPrinc->save();

                    $rowUltima_fase = $tabelaSadTbDocmDocumento->find($rs["VIDC_ID_DOC_PRINCIPAL"])->current();
                    $rowUltima_fase->setFromArray(array(
                        "DOCM_ID_MOVIMENTACAO" => $dadosSolPrinc["MOFA_ID_MOVIMENTACAO"],
                        "DOCM_DH_FASE" => $datahora
                    ));
                    $rowUltima_fase->save();

                    $rowVidcAuditoria = $SadTbVidcAuditoria->createRow(array(
                        'VIDC_TS_OPERACAO' => new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')"),
                        'VIDC_IC_OPERACAO' => 'E',
                        'VIDC_CD_MATRICULA_OPERACAO' => $userNs->matricula,
                        'VIDC_CD_MAQUINA_OPERACAO' => substr($_SERVER['REMOTE_ADDR'], 0, 50),
                        'VIDC_CD_USUARIO_SO' => substr($_SERVER['HTTP_USER_AGENT'], 0, 50),
                        'OLD_VIDC_ID_VINCULACAO_DOC' => $rs["VIDC_ID_VINCULACAO_DOCUMENTO"],
                        'NEW_VIDC_ID_VINCULACAO_DOC' => new Zend_Db_Expr("NULL"),
                        'OLD_VIDC_ID_DOC_PRINCIPAL' => $linha->VIDC_ID_DOC_PRINCIPAL,
                        'NEW_VIDC_ID_DOC_PRINCIPAL' => new Zend_Db_Expr("NULL"),
                        'OLD_VIDC_ID_DOC_VINCULADO' => $linha->VIDC_ID_DOC_VINCULADO,
                        'NEW_VIDC_ID_DOC_VINCULADO' => new Zend_Db_Expr("NULL"),
                        'OLD_VIDC_ID_TP_VINCULACAO' => $tipoVinculacao,
                        'NEW_VIDC_ID_TP_VINCULACAO' => new Zend_Db_Expr("NULL"),
                        'OLD_VIDC_DH_VINCULACAO' => $linha->VIDC_DH_VINCULACAO,
                        'NEW_VIDC_DH_VINCULACAO' => new Zend_Db_Expr("NULL"),
                        'OLD_VIDC_CD_MATR_VINCULACAO' => $linha->VIDC_CD_MATR_VINCULACAO,
                        'NEW_VIDC_CD_MATR_VINCULACAO' => new Zend_Db_Expr("NULL")
                    ));
                    $rowVidcAuditoria->save();

                    $linha->delete();
                }
            }
        }
    }

    private function json_nr_documento ($nr_marcarado, $tipo_documento = null) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SAD_PKG_NR_DOCUMENTO.STRING_BUSCA('" . strtoupper($nr_marcarado) . "','$tipo_documento') JSON_NR_DOC FROM DUAL");
        $json_nr_doc_aux = $stmt->fetchAll();
        $json_nr_doc = $json_nr_doc_aux[0]["JSON_NR_DOC"];
        return $json_nr_doc;
    }

    public function getQtdeDesenvComSolInfo ($idCaixa, $idFase) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "  SELECT DISTINCT COUNT(SSOL_ID_DOCUMENTO) QTDE
                FROM SOS_TB_SSOL_SOLICITACAO SSOL
                -- documento
                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                -- documento movimentacao
                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                -- movimentacao origem
                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                -- movimentacao destino
                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                --fase
                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                --descricao fase
                INNER JOIN SAD_TB_FADM_FASE_ADM FADM
                ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE
                WHERE
                --última movimentação
                (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                      FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                      ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                      ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                      WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                      AND DOCM_1.DOCM_ID_TIPO_DOC = 160)
                                                     )
                AND MOFA.MOFA_ID_FASE NOT IN (1000, 1014, 1026, 1081)                                                                                  
                AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa                                                                           
                AND DOCM_ID_TIPO_DOC = 160                                                                               
                ";

        if (!empty($idFase))
            $q .= "AND MOFA_ID_FASE = '$idFase'";

        $stmt = $db->query($q);
        $qtde = $stmt->fetchAll();
        return $qtde[0]['QTDE'];
    }

    public function getSolCaixa ($idCaixa) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "  SELECT DISTINCT *
                FROM SOS_TB_SSOL_SOLICITACAO SSOL
                -- documento
                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                -- documento movimentacao
                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                -- movimentacao origem
                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                -- movimentacao destino
                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                --fase
                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                --descricao fase
                INNER JOIN SAD_TB_FADM_FASE_ADM FADM
                ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE
                WHERE
                --última movimentação
                (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                      FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                      ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                      INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                      ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                      WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                      AND DOCM_1.DOCM_ID_TIPO_DOC = 160)
                                                     )
                AND MOFA.MOFA_ID_FASE NOT IN (1000, 1014, 1026, 1081)
                AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa
                AND DOCM_ID_TIPO_DOC = 160
                ";

        $stmt = $db->query($q);
        $qtde = $stmt->fetchAll();
        return $qtde;
    }
    
    public function setVinculaEntreCaixas(
        $idDocmDocumento, array $dataMoviMovimentacao, array $dataModeMoviDestinatario, 
        array $dataMofaMoviFase, array $dataSsesServicoSolic, array $dataSnasNivelAtendSolic, 
        $principal, $nrDocsRed = null, $acompanhar = null
    )

    {
        $datahora = $this->sysdate();
        Zend_Debug::dump($datahora, 'data e hora');

        /**
         * Verifica se a solicitação é de videoconferência e está sendo enviada a um serviço de videoconferência. 
         */
        $DadosSolicitacao = $this->getDadosSolicitacao($idDocmDocumento);
        if (!array_key_exists("SSES_DT_INICIO_VIDEO", $DadosSolicitacao)) {
            throw new Zend_Exception('O valor da variável obrigatória SSES_DT_INICIO_VIDEO está ausente');
        }
        if (!is_null($DadosSolicitacao["SSES_DT_INICIO_VIDEO"])) {
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServicoRow = $SosTbSserServico->fetchRow("SSER_ID_SERVICO = " . $dataSsesServicoSolic["SSES_ID_SERVICO"]);
            if (!is_null($SosTbSserServicoRow)) {
                $SosTbSserServicoRowArray = $SosTbSserServicoRow->toArray();
                if ($SosTbSserServicoRowArray["SSER_IC_VIDEOCONFERENCIA"] == "S") {
                    $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = new Zend_Db_Expr("TO_DATE('" . $DadosSolicitacao["SSES_DT_INICIO_VIDEO"] . "','dd/mm/yyyy HH24:MI:SS')");
                    $dataSsesServicoSolic["SSES_IC_VIDEO_REALIZADA"] = "N";
                } else {
                    throw new Zend_Exception('ATENÇÃO: Solicitações com o serviço de Videoconferência somente podem ser enviadas a serviços de Videoconferência correspondentes do grupo ao qual elas serão enviadas. Selecione um serviço de Videoconferência correspondete do grupo de envio.', 2);
                }
            }
        } else {
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServicoRow = $SosTbSserServico->fetchRow("SSER_ID_SERVICO = " . $dataSsesServicoSolic["SSES_ID_SERVICO"]);
            if (!is_null($SosTbSserServicoRow)) {
                $SosTbSserServicoRowArray = $SosTbSserServicoRow->toArray();
                if ($SosTbSserServicoRowArray["SSER_IC_VIDEOCONFERENCIA"] == "S") {
                    throw new Zend_Exception('ATENÇÃO: As solicitações escolhidas são de videoconferência se sim, uma ou mais solicitações de videoconferência dentre as escolhidas não possui(em) a data de inicio. Resolva este problema trocando o serviço para Videoconferencia e informando a data de início da mesma. Se não para enviar como videoconfência é preciso trocar o serviço para videoconferência primeiro. ', 2);
                }
            }
        }

        /* ---------------------------------------------------------------------------------------- */
        /* primeira tabela */
        $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        //$dataMoviMovimentacao =  $SadTbMoviMovimentacao->fetchNew()->toArray();
        // $dataMoviMovimentacao = array();

        Zend_Debug::dump($dataMoviMovimentacao);
        unset($dataMoviMovimentacao["MODO_ID_MOVIMENTACAO"]);
        $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $datahora;
//            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = INFORMAR;
//            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = INFORMAR;
//            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = INFORMAR;
//            $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = INFORMAR;
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
        //$dataModoMoviDocumento = array();

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
        //$dataModeMoviDestinatario = array();

        Zend_Debug::dump($dataModeMoviDestinatario);
        $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
//            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = INFORMAR;
//            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = INFORMAR;
//            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = INFORMAR;
//            $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = INFORMAR;
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
        //$dataMofaMoviFase = array();

        Zend_Debug::dump($dataMofaMoviFase);
        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
//            $dataMofaMoviFase["MOFA_ID_FASE"] = INFORMAR;
        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
        Zend_Debug::dump($dataMofaMoviFase);

        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
        $rowMofaMoviFase->save();
        /* ---------------------------------------------------------------------------------------- */

        //Ultima Fase do lançada na Solicitação.//
        /* ---------------------------------------------------------------------------------------- */

        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();

        $rowUltima_fase->setFromArray($dataUltima_fase);
        Zend_Debug::dump($rowUltima_fase->toArray());
        $rowUltima_fase->save();
        /* ---------------------------------------------------------------------------------------- */
        /* setima tabela */
        //if( $dataSsesServicoSolic["SSES_ID_SERVICO"] && isset($dataSsesServicoSolic["SSES_ID_SERVICO"]) ) {
        $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
        //$dataSsesServicoSolic=  $SosTbSsesServicoSolic->fetchNew()->toArray();
        // $dataSsesServicoSolic = array();

        Zend_Debug::dump($dataSsesServicoSolic);
        $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataSsesServicoSolic["SSES_DH_FASE"] = $datahora;
//                $dataSsesServicoSolic["SSES_ID_SERVICO"] = INFORMAR;
        $dataSsesServicoSolic["SSES_ID_DOCUMENTO"] = $idDocmDocumento;
        Zend_Debug::dump($dataSsesServicoSolic);

        $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
        $rowSsesServicoSolic->save();
        //}
        /* ---------------------------------------------------------------------------------------- */
        /* quinta tabela */
        if ($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] && isset($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"])) {
            $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
            //$dataSnasNivelAtendSolic=  $SosTbSnasNivelAtendSolic->fetchNew()->toArray();
            // $dataSnasNivelAtendSolic = array();

            Zend_Debug::dump($dataSnasNivelAtendSolic);
            $dataSnasNivelAtendSolic["SNAS_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataSnasNivelAtendSolic["SNAS_DH_FASE"] = $datahora;
//                $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = INFORMAR;
            $dataSnasNivelAtendSolic["SNAS_ID_DOCUMENTO"] = $idDocmDocumento;
            Zend_Debug::dump($dataSnasNivelAtendSolic);

            $rowSnasNivelAtendSolic = $SosTbSnasNivelAtendSolic->createRow($dataSnasNivelAtendSolic);
            $rowSnasNivelAtendSolic->save();
        }
        
        
        /**
         * Realiza a vinculação entre as solicitações 
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SadTbVidcAuditoria();
//                            $id_vinculacao = new Zend_Db_Expr("NULL");
        if ($idDocmDocumento != $principal) {
            $rowVinc = $SadTbVidcVinculacaoDoc->createRow(array(
                "VIDC_ID_DOC_PRINCIPAL" => $principal,
                "VIDC_ID_DOC_VINCULADO" => $idDocmDocumento,
                "VIDC_DH_VINCULACAO" => $datahora,
                "VIDC_ID_TP_VINCULACAO" => 8,
                "VIDC_CD_MATR_VINCULACAO" => $userNs->matricula
            ));
            $id_vinculacao = $rowVinc->save();
        }

        $rowVidcAuditoria = $SadTbVidcAuditoria->createRow(
            array(
                'VIDC_TS_OPERACAO' => new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')"),
                'VIDC_IC_OPERACAO' => 'I',
                'VIDC_CD_MATRICULA_OPERACAO' => $userNs->matricula,
                'VIDC_CD_MAQUINA_OPERACAO' => substr($_SERVER['REMOTE_ADDR'], 0, 50),
                'VIDC_CD_USUARIO_SO' => substr($_SERVER['HTTP_USER_AGENT'], 0, 50),
                'OLD_VIDC_ID_VINCULACAO_DOC' => new Zend_Db_Expr("NULL"),
                'NEW_VIDC_ID_VINCULACAO_DOC' => $id_vinculacao,
                'OLD_VIDC_ID_DOC_PRINCIPAL' => new Zend_Db_Expr("NULL"),
                'NEW_VIDC_ID_DOC_PRINCIPAL' => $principal,
                'OLD_VIDC_ID_DOC_VINCULADO' => new Zend_Db_Expr("NULL"),
                'NEW_VIDC_ID_DOC_VINCULADO' => $idDocmDocumento,
                'OLD_VIDC_ID_TP_VINCULACAO' => new Zend_Db_Expr("NULL"),
                'NEW_VIDC_ID_TP_VINCULACAO' => 8,
                'OLD_VIDC_DH_VINCULACAO' => new Zend_Db_Expr("NULL"),
                'NEW_VIDC_DH_VINCULACAO' => $datahora,
                'OLD_VIDC_CD_MATR_VINCULACAO' => new Zend_Db_Expr("NULL"),
                'NEW_VIDC_CD_MATR_VINCULACAO' => $userNs->matricula
            )
        );
        $rowVidcAuditoria->save();
        
        
        
        
        
        
        /* ---------------------------------------------------------------------------------------- */

        /* Retira do atendente */
        /* ---------------------------------------------------------------------------------------- */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
        $rowSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();
        $rowSolicitacao->setFromArray($dataSsolSolicitacao);
        $rowSolicitacao->save();
        /* ---------------------------------------------------------------------------------------- */

        // Insere o anexo
        /* ---------------------------------------------------------------------------------------- */

        $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
        $anexAnexo['ANEX_DH_FASE'] = $datahora;
        $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        /**
         * Cadastra os documentos que ainda não existe no red.
         */
        if ($nrDocsRed['incluidos']) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                $rowAnexAnexo->save();
            }
        }
        /**
         *  Verifica se o documento que já existe no red já pertence a esta solicitação
         * caso negativo, cadastra o nr do documento para a solicitação.
         */
        if ($nrDocsRed['existentes']) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idDocmDocumento AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                if (!$SadTbAnexAnexofetchRow) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
        }
        /* ----------------------ACOMPANHAMENTO DE BAIXA DA SOLICITAÇÃO NO ENCAMINHAMENTO  --------- */
        if ($acompanhar == "S") {
            $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
            $tabelaPapd->addAcompanhanteSostiCaixaAtendimento($idDocmDocumento);
        }
        /* ---------------------------------------------------------------------------------------- */

        $retorno['ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $retorno['DATA_HORA'] = $datahora;
        
        return $retorno;
        
        
        
        
        
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $db->beginTransaction();
//        $dual = new Application_Model_DbTable_Dual();
//        $datahora = $dual->sysdate();
//        
//        /**
//         * Lançar a nova fase
//         */
//        
//        /**
//         * Altera o serviço das solicitações que estão sendo vinculadas
//         */
//        
//        /**
//         * Envia as solicitações para a mesma caixa da solicitação principal
//         */
//        
//        /**
//         * Realiza a vinculação entre as solicitações
//         */
//        try {
//            $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
//            $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
//            $dataSsesServicoSolic["SSES_DH_FASE"] = $datahora;
//            $dataSsesServicoSolic['SSES_ID_DOCUMENTO'] = $idDocmDocumento;
////            Zend_Debug::dump($dataSsesServicoSolic);EXIT;
//            if(isset($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]) && !is_null($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"])){
//                $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = new Zend_Db_Expr("TO_DATE('".$dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]."','dd/mm/yyyy HH24:MI:SS')"); 
//                $dataSsesServicoSolic["SSES_IC_VIDEO_REALIZADA"] = "N";
//            }
//            $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
//            $rowSsesServicoSolic->save();
//
//            /**
//             * Atualiza o número do tombo
//             */
//            if ($dataSsolSolicitacao["SSOL_NR_TOMBO"] != '') {
//                $dataSsolSolicitacao["SSOL_NR_TOMBO"];
//                $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
//                $rowSsolSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();;
//                $rowSsolSolicitacao->setFromArray($dataSsolSolicitacao);
//                $rowSsolSolicitacao->save();
//            }
//
//            //Ultima Fase do lançada na Solicitação.//
//            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
//            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
//            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
//            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();;
//            $rowUltima_fase->setFromArray($dataUltima_fase);
//            $rowUltima_fase->save();
//            /* ---------------------------------------------------------------------------------------- */
//
//            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
//            $anexAnexo['ANEX_DH_FASE'] = $datahora;
//            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
//            /**
//             * Cadastra os documentos que ainda não existe no red.
//             */
//            if ($nrDocsRed['incluidos']) {
//                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
//                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
//                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
//                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
//                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
//                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
//                    $rowAnexAnexo->save();
//                }
//            }
//            /**
//             *  Verifica se o documento que já existe no red já pertence a esta solicitação
//             * caso negativo, cadastra o nr do documento para a solicitação.
//             */
//            if ($nrDocsRed['existentes']) {
//                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
//                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
//                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idDocmDocumento AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
//                    if (!$SadTbAnexAnexofetchRow) {
//                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
//                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
//                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
//                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
//                        $rowAnexAnexo->save();
//                    }
//                }
//            }
//            $db->commit();
//            return true;
//        } catch (Exception $ex) {
//            $db->rollBack();
//            return $ex->getMessage();
//        }
    }
    
    public function setLancarFase($idDocmDocumento, array $dataMofaMoviFase)
    {
        $solicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $datahora = $solicitacao->sysdate();
        /**
         * Insere na tabela de movimentação
         */
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
        $rowMofaMoviFase->save();
        /**
         * Atualiza a última fase lançada na tabela de documento
         */
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
        $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();

        $rowUltima_fase->setFromArray($dataUltima_fase);
        $rowUltima_fase->toArray();
        $ultimaFaseLancada = $rowUltima_fase->save();
 
        return $ultimaFaseLancada;
    }
    
    public function solicitacaohasTomboCentral($solicitacaoNR)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT C.*,A.*,B.CO_MAT, B.TI_TOMBO
                            FROM 
                            SOS_TB_SSOL_SOLICITACAO A, 
                            TOMBO_TI_CENTRAL B,
                            SAD_TB_DOCM_DOCUMENTO C
                            WHERE
                            C.DOCM_NR_DOCUMENTO = $solicitacaoNR 
                            AND C.docm_id_documento = A.ssol_id_documento
                            AND A.SSOL_NR_TOMBO = B.NU_TOMBO
                            AND B.TI_TOMBO = 'T'
                            AND B.CO_MAT LIKE '5235%'");
        $rows = $stmt->fetch();
        return $rows;
    }
    
   

}
