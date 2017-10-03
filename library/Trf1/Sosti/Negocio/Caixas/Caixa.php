<?php

/**
 * @category    TRF1
 * @package        Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento
 * @copyright    Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author        Leonan Alves dos Anjos
 * @license        FREE, keep original copyrights
 * @version        controlada pelo SVN
 * @tutorial    Tutorial abaixo
 *
 * TRF1, Classe negocial sobre o SOSTI - Garantia dos serviços do desenvolvimento
 *
 * ====================================================================================================
 * LICENSA
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
class Trf1_Sosti_Negocio_Caixas_Caixa
{
    /*     * ***********************************************************
     * Definições iniciais
     * ********************************************************** */

    public $_query_caixa;
    protected $_flag_filtro_ativo = false;
    public $_CaixasQuerys;
    public $_Clausula_Select_topo;
    public $_Clausula_Select_topo_count;
    public $_Clausula_Order_topo;
    public $_Clausula_from_e_where_nucleo;
    public $_Clausula_From_nucleo;
    public $_Clausula_Where_nucleo;
    public $_ConsultaCaixaCount;
    public $_ConsultaNucleo;
    public $_chaves_pesquisa = array(
        "SSOL_CD_MATRICULA_ATENDENTE" => "",
        "DOCM_CD_MATRICULA_CADASTRO" => "",
        "MOFA_ID_FASE" => "",
        "DOCM_SG_SECAO_GERADORA" => "",
        "DOCM_CD_LOTACAO_GERADORA" => "",
        "CATE_ID_CATEGORIA" => "",
        "SSER_ID_SERVICO" => "",
        "SSER_DS_SERVICO" => "",
        "DATA_INICIAL_CADASTRO" => "",
        "DATA_FINAL_CADASTRO" => "",
        "DATA_INICIAL" => "",
        "DATA_FINAL" => "",
        "DOCM_NR_DOCUMENTO" => "",
        "SOMENTE_PRINCIPAL" => "N"
    );

    public function __construct()
    {
        $this->_CaixasQuerys = new App_Sosti_CaixasQuerys();
        /**
         * Inicializa as variáves para não misturar as querys de caixas distintas
         */
        $this->initVariaveis();
    }

    /**
     * @abstract Indica de o filtro está ativo
     * @param boolean $flag
     */
    public function setFlagFiltroAtivo($flag)
    {
        $this->_flag_filtro_ativo = (bool)$flag;
    }

    /**
     * @abstract Indica de o filtro está ativo
     * @param boolean $flag
     */
    public function getFlagFiltroAtivo()
    {
        return $this->_flag_filtro_ativo;
    }

    /**
     * Inicializa as variáves para não misturar as querys de caixas distintas
     */
    public function initVariaveis()
    {
        $this->_query_caixa = null;
        $this->_CaixasQuerys = null;
        $this->_Clausula_Select_topo = null;
        $this->_Clausula_Select_topo_count = null;
        $this->_Clausula_Order_topo = null;
        $this->_Clausula_from_e_where_nucleo = null;
        $this->_Clausula_From_nucleo = null;
        $this->_Clausula_Where_nucleo = null;
        $this->_ConsultaCaixaCount = null;
        $this->_ConsultaNucleo = null;
    }

    public function setFiltro($params)
    {

        /* Atendente */
        if (isset($params['SSOL_CD_MATRICULA_ATENDENTE'])) {
            $this->_Clausula_Where_nucleo .= ($params['SSOL_CD_MATRICULA_ATENDENTE']) ? (" AND SSOL_CD_MATRICULA_ATENDENTE = '" . $params['SSOL_CD_MATRICULA_ATENDENTE'] . "' ") : ('');
        }
        /* Unidade fase */
        $this->_Clausula_Where_nucleo .= ($params['MOFA_ID_FASE']) ? (" AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' ") : ('');

        /* Unidade solicitante */
        $this->_Clausula_Where_nucleo .= ($params['DOCM_SG_SECAO_GERADORA']) ? (" AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' ") : ('');
        $this->_Clausula_Where_nucleo .= ($params['DOCM_CD_LOTACAO_GERADORA']) ? (" AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " ") : ('');

        /* Solicitante */
        $this->_Clausula_Where_nucleo .= ($params['DOCM_CD_MATRICULA_CADASTRO']) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' ") : ('');

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
                $this->_Clausula_Where_nucleo .= ($params['CATE_ID_CATEGORIA']) ? ("
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
                $this->_Clausula_Where_nucleo .= ($params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $this->_Clausula_Where_nucleo .= ($params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $this->_Clausula_Where_nucleo .= ($params['SSER_DS_SERVICO']) ? (" AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');

        /* Data de cadastro */
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($this->_Clausula_Where_nucleo .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($this->_Clausula_Where_nucleo .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($this->_Clausula_Where_nucleo .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Data da Ultima fase */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($this->_Clausula_Where_nucleo .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60  ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($this->_Clausula_Where_nucleo .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60  ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($this->_Clausula_Where_nucleo .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60  ") : ("");

        /* Número da solicitação */
        $docm_nr_documento = $params['DOCM_NR_DOCUMENTO'];
        if (!empty($docm_nr_documento)) {
            $this->_Clausula_Where_nucleo .= (strlen(trim($docm_nr_documento)) == 28) ? ("AND DOCM_NR_DOCUMENTO = $docm_nr_documento") :
                ("AND TO_NUMBER(SUBSTR(DOCM_NR_DOCUMENTO,-6,6)) = TO_NUMBER(SUBSTR($docm_nr_documento,5))
                                                              AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)");
        }
        return $this->_Clausula_Where_nucleo;
    }

    public function switchQuery($params)
    {
        $array_bons_filtros = array(
            "SSOL_CD_MATRICULA_ATENDENTE" => "",
            "DOCM_CD_MATRICULA_CADASTRO" => "",
            "MOFA_ID_FASE" => "",
            "DOCM_SG_SECAO_GERADORA" => "",
            "DOCM_CD_LOTACAO_GERADORA" => "",
            "CATE_ID_CATEGORIA" => "",
            "SSER_ID_SERVICO" => "",
            "SSER_DS_SERVICO" => "",
            "DATA_INICIAL_CADASTRO" => "",
            "DATA_FINAL_CADASTRO" => "",
            "DATA_INICIAL" => "",
            "DATA_FINAL" => "",
            "DOCM_NR_DOCUMENTO" => "",
            "POSSUI_ORDEM_SERVICO" => "",
        );
        foreach ($params as $key => $value) {
            if (in_array((string)$key, array_keys($array_bons_filtros))) {

                /**
                 * Limpa arrays passadas como parametro com valores vazios
                 */
                if (is_array($params[$key])) {
                    if (array_search("", $params[$key], true) !== false) {
                        unset($params[$key][array_search("", $params[$key])]);
                    }
                }

                if (!empty($params[$key])) {
                    $this->setFlagFiltroAtivo(true);
                }
            }
        }
    }

    public function getCaixaComNivelPesq($idCaixa, $nivel, $params, $order)
    {
        /**
         * Inicializa as variáves para não misturar as querys de caixas distintas
         */
        $nivel = $nivel ?: 1;
        $this->initVariaveis();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        $usuarioExterno = $params['SSOL_NM_USUARIO_EXTERNO'];
        /**
         * Faz o merge com as chaves padrões de pequisa para não disparar warnings nem notices
         */
        $params = array_merge($this->_chaves_pesquisa, $params);

        /**
         * Define qual query será utilizada
         */
        $this->switchQuery($params);
        /**
         * A clausula select e a Order deve ser comum a ambas as querys
         */
        /*         * *************************************
         * *************SELECT******************
         * ************************************* */
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(1);
        $stmt .= "," . $CaixasQuerys->whereStatusExtensao();
        $stmt .= "," . $CaixasQuerys->whereStatusVideoconferencia();
        $this->_Clausula_Select_topo = $stmt;
        unset($stmt);
        /*         * *************************************
         * *************ORDER******************
         * ************************************* */
        $this->_Clausula_Order_topo = $CaixasQuerys->ordemCaixa($order);


        /**
         * Montagem das querys
         */
        if ($this->getFlagFiltroAtivo()) {

            $stmt = "";
            $stmt .= "SELECT * FROM(";
            $stmt .= $this->_Clausula_Select_topo;
            $stmt .= $CaixasQuerys->from();
            $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
            $stmt .= $CaixasQuerys->leftJoinFaseServico();
            $stmt .= $CaixasQuerys->leftJoinFaseNivel();
            $stmt .= $CaixasQuerys->leftJoinFaseEspera();
            $stmt .= "
            --Solicitação de equipamento
            LEFT JOIN SOS_TB_MAEQ_MANUTENCAO_EQPTO MAEQ
            ON MAEQ.MAEQ_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
            ";
            $stmt .= $CaixasQuerys->where();
            $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
            $stmt .= $CaixasQuerys->whereUltimoServico();
            $stmt .= $CaixasQuerys->whereUltimoNivel();
            $stmt .= $CaixasQuerys->whereUltimaEspera();
            $stmt .= $CaixasQuerys->whereEmAtendimento();
            $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
            $stmt .= $CaixasQuerys->whereNivel(true, $nivel);
            $stmt .= $CaixasQuerys->whereTipoSolicitacao();
            /* Filtro */
            $this->setFiltro($params);
            $stmt .= $this->_Clausula_Where_nucleo;
            /* Ordem */
            $stmt .= $this->_Clausula_Order_topo;
            $stmt .= ") SUB_Q";
            if ($params['SOMENTE_PRINCIPAL'] == 'N') {
                $stmt .= $CaixasQuerys->where();
                //esconde as solicitações filhas vinculadas
                $stmt .= " SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL_ID_DOCUMENTO) = 1 ";
            }
            $stmt .= $usuarioExterno != "" ? " AND UPPER(SSOL_NM_USUARIO_EXTERNO) LIKE UPPER('%" . $usuarioExterno . "%') " : "";
            return $stmt;
        } else {

            $OpcoesConsulta = new Trf1_Sosti_Negocio_Caixas_OpcoesConsulta();
            $OpcoesConsulta->setOpIdCaixa($idCaixa);
            $OpcoesConsulta->setOpServico(TRUE);
            $OpcoesConsulta->setOpEspera(TRUE);
            $OpcoesConsulta->setOpNivel(TRUE);
            $OpcoesConsulta->setOpPrazo(FALSE);
            /**
             * Select
             */
            $this->_Clausula_Select_topo;
            /**
             * From
             */
            $stmt = "";
            $stmt .= $CaixasQuerys->fromSubQueryCaixaAtendimento($OpcoesConsulta);
            $stmt .= "
            --Solicitação de equipamento
            LEFT JOIN SOS_TB_MAEQ_MANUTENCAO_EQPTO MAEQ
            ON MAEQ.MAEQ_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
            ";
            $this->_Clausula_From_nucleo = $stmt;

            /**
             * Where
             */
            $this->_Clausula_Where_nucleo .= $CaixasQuerys->where() . $this->_Clausula_Where_nucleo;
            $this->_Clausula_Where_nucleo .= $CaixasQuerys->whereNivel(false, $nivel);
            $this->_Clausula_Where_nucleo .= $CaixasQuerys->whereTipoSolicitacao();
            $this->setFiltro($params);
            if ($params['SOMENTE_PRINCIPAL'] == 'N') {
                //esconde as solicitações filhas vinculadas
                $this->_Clausula_Where_nucleo .= "AND SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL.SSOL_ID_DOCUMENTO) = 1 ";
            }
$this->_Clausula_Where_nucleo .= $usuarioExterno != "" ? " AND UPPER(SSOL_NM_USUARIO_EXTERNO) LIKE UPPER('%" . $usuarioExterno . "%') " : "";
            /**
             * From + Where
             */
            $this->_Clausula_from_e_where_nucleo = $this->_Clausula_From_nucleo . " " . $this->_Clausula_Where_nucleo;

            /**
             * Query Montada
             */
            $this->_query_caixa = $this->_Clausula_Select_topo . " " . $this->_Clausula_from_e_where_nucleo;
//            $this->_query_caixa .= $usuarioExterno != "" ? " AND UPPER(SSOL_NM_USUARIO_EXTERNO) LIKE UPPER('%" . $usuarioExterno . "%') " : "";
            $this->_query_caixa .= $this->_Clausula_Order_topo;
//            Zend_Debug::dump($this->_query_caixa);exit;
            return $this;
        }
    }

    public function mountConsultaCaixaPreCount()
    {
        $this->_ConsultaCaixaCount = "";
        $this->_ConsultaCaixaCount .= " SELECT * ";
        $this->_ConsultaCaixaCount .= $this->_Clausula_from_e_where_nucleo;
        return $this->_ConsultaCaixaCount;
    }

    public function mountNucleoConsulta()
    {
        $this->_ConsultaNucleo .= "SELECT SUB_ORDER.*,
                                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO, 
                                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE
                                    FROM(
                                    ";
        $this->_ConsultaNucleo .= " SELECT * ";
        $this->_ConsultaNucleo .= $this->_Clausula_from_e_where_nucleo;
        $this->_ConsultaNucleo .= ")SUB_ORDER";
        $this->_ConsultaNucleo .= $this->_Clausula_Order_topo;
        return $this->_ConsultaNucleo;
    }

    public function getCaixaSemNivelPesq($idCaixa, $params, $order, $id_doc = null)
    {

        /**
         * Inicializa as variáves para não misturar as querys de caixas distintas
         */
        $this->initVariaveis();

        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        /**
         * Faz o merge com as chaves padrões de pequisa para não disparar warnings nem notices
         */
        $params = array_merge($this->_chaves_pesquisa, $params);

        /**
         * Define qual query será utilizada
         */
        $this->switchQuery($params);
        /**
         * A clausula select e a Order deve ser comum a ambas as querys
         */
        /*         * *************************************
         * *************SELECT******************
         * ************************************* */
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(2);
        if ($idCaixa == 2) {
            $stmt .= $CaixasQuerys->colunasServicosSistemas();
        }
        if ($idCaixa == 4) {
            $stmt .= "," . $CaixasQuerys->whereStatusVideoconferencia();
        }
        $this->_Clausula_Select_topo = $stmt;
        unset($stmt);
        /*         * *************************************
         * *************ORDER******************
         * ************************************* */
        $this->_Clausula_Order_topo = $CaixasQuerys->ordemCaixa($order);
        /**
         * Montagem das querys
         */
        if ($this->getFlagFiltroAtivo()) {

            $stmt = "";
            $stmt .= "SELECT * FROM(";
            $stmt .= $this->_Clausula_Select_topo;
            $stmt .= $CaixasQuerys->from();
            $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
            $stmt .= $CaixasQuerys->leftJoinFaseServico();
            $stmt .= $CaixasQuerys->leftJoinPriorizaDemanda();
            $stmt .= $CaixasQuerys->leftJoinFaseEspera();
            if ($idCaixa == 2) {
                $stmt .= $CaixasQuerys->leftJoinServicosSistemas();
            }
            $stmt .= $CaixasQuerys->where();
            $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
            $stmt .= $CaixasQuerys->whereUltimoServico();
            $stmt .= $CaixasQuerys->whereUltimaEspera();
            $stmt .= $CaixasQuerys->whereEmAtendimento();
            if (($params['POSSUI_ORDEM_SERVICO'] != '') && ($params['SOMENTE_PRINCIPAL'] == 'N' && empty($id_doc))) {
                $stmt .= $CaixasQuerys->whereAssociacaoOs($params['POSSUI_ORDEM_SERVICO']);
            }
            $stmt .= $CaixasQuerys->whereCaixa(true, $idCaixa);
            $stmt .= $CaixasQuerys->whereTipoSolicitacao();
            if (!empty($id_doc))
                $stmt .= " AND SSOL.SSOL_ID_DOCUMENTO = $id_doc ";

            /* Filtro */
            $this->setFiltro($params);
            $stmt .= $this->_Clausula_Where_nucleo;
            /* Ordem */
            $stmt .= $this->_Clausula_Order_topo;
            $stmt .= ") SUB_Q";

//            if (!empty($id_doc))
//                $stmt .= " AND SSOL.SSOL_ID_DOCUMENTO = $id_doc ";

            if ($params['SOMENTE_PRINCIPAL'] == 'N' && empty($id_doc)) {
                $stmt .= $CaixasQuerys->where();
                //esconde as solicitações filhas vinculadas
                $stmt .= " SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL_ID_DOCUMENTO) = 1 ";
            }
            $this->_query_caixa = $stmt;
            return $stmt;
        } else {

            $OpcoesConsulta = new Trf1_Sosti_Negocio_Caixas_OpcoesConsulta();
            $OpcoesConsulta->setOpIdCaixa($idCaixa);
            $OpcoesConsulta->setOpServico(TRUE);
            $OpcoesConsulta->setOpEspera(TRUE);
            $OpcoesConsulta->setOpNivel(FALSE);
            $OpcoesConsulta->setOpPrazo(FALSE);
            /**
             * Select
             */
            $this->_Clausula_Select_topo;
            /**
             * From
             */
            $stmt = "";
            $stmt .= $CaixasQuerys->fromSubQueryCaixaAtendimento($OpcoesConsulta);
            $stmt .= $CaixasQuerys->leftJoinPriorizaDemanda();
            if ($idCaixa == 2) {
                $stmt .= $CaixasQuerys->leftJoinServicosSistemas();
            }
            $this->_Clausula_From_nucleo = $stmt;
            /**
             * Zend_Debug::dump($var);
             * Where
             */
            $this->_Clausula_Where_nucleo .= $CaixasQuerys->where() . $this->_Clausula_Where_nucleo;
            $this->_Clausula_Where_nucleo .= $CaixasQuerys->whereTipoSolicitacao(false);
            $this->_Clausula_Where_nucleo .= ($params['INATIVO'] != '') ? (" AND SSER_IC_ATIVO = 'N' OR SSER_IC_VISIVEL = 'N'") : ('');
            $this->setFiltro($params);
            if ($params['SOMENTE_PRINCIPAL'] == 'N' && empty($id_doc)) {
                //esconde as solicitações filhas vinculadas
                $this->_Clausula_Where_nucleo .= "AND SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL.SSOL_ID_DOCUMENTO) = 1 ";
            }
            /**
             * From + Where
             */
            $this->_Clausula_from_e_where_nucleo = $this->_Clausula_From_nucleo . " " . $this->_Clausula_Where_nucleo;

            /**
             * Query Montada
             */
            if (!empty($id_doc))
                $this->_Clausula_from_e_where_nucleo .= " AND SSOL.SSOL_ID_DOCUMENTO = $id_doc ";
            $this->_query_caixa = $this->_Clausula_Select_topo . " " . $this->_Clausula_from_e_where_nucleo . " " . $this->_Clausula_Order_topo;
//            echo '<pre>';
//            var_dump($this->_query_caixa);
//            echo '</pre>';
//            die;
            return $this;
        }
    }

    public function getData($query = null)
    {
//        Zend_Debug::dump($this->_query_caixa);die;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if (empty($query))
            return $db->fetchRow($this->_query_caixa);
        else {
            return $db->fetchRow($this->_query_caixa);
        }
    }

    public function getVinculos($id_doc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT" . PHP_EOL;
        $stmt .= "*" . PHP_EOL;
        $stmt .= "FROM SAD.SAD_TB_VIDC_VINCULACAO_DOC A" . PHP_EOL;
        $stmt .= "WHERE A.VIDC_ID_DOC_PRINCIPAL = $id_doc" . PHP_EOL;
        return $db->fetchAll($stmt);
    }


}
