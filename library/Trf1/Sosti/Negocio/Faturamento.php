<?php

/**
 * @category	TRF1
 * @package	Trf1_Sosti_Negocio_Faturamento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author	Pedro Henrique
 * @license	FREE, keep original copyrights
 * @version	controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre o SOSTI - Respostas Padrões do Sistema
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 */
class Trf1_Sosti_Negocio_Faturamento {

    protected $db;
    protected $tb_status_faturamento;
    protected $tb_classificacao_faturamento;
    protected $SosTbPfdsApfDesenvolvedora;
    protected $SosTbPfafApfAferidora;
    protected $SosTbPftrApfTribunal;

    function __construct() {
        //ADAPTADOR DO BANCO DE DADOS
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'")->execute();

        $this->tb_status_faturamento = new Application_Model_DbTable_Sosti_SosTbSctaStatusContratada();
        $this->tb_classificacao_faturamento = new Application_Model_DbTable_Sosti_SosTbClcoClassificacaoCont();

        $this->SosTbPfdsApfDesenvolvedora = new Application_Model_DbTable_Sosti_SosTbPfdsApfDesenvolvedora();
        $this->SosTbPfafApfAferidora = new Application_Model_DbTable_Sosti_SosTbPfafApfAferidora();
        $this->SosTbPftrApfTribunal = new Application_Model_DbTable_Sosti_SosTbPftrApfTribunal();
    }

    /*     * *******************************************************************
     * ********************** FUNÇÕES DE STATUS ********************************
     * ********************************************************************** */

    /**
     * Função que busca os status de faturamento de acordo com a função
     * @param String $tipo Tipo de status
     * @author Pedro Henrique
     */
    public function retornaStatus($tipo) {
        $status = $this->tb_status_faturamento->fetchAll("SCTA_IC_ATOR_STATUS = '$tipo'");
        return $status->toArray();
    }

    public function retornaDadosTotalizadosByIds($ids) {
        try {
            $status_dsv = "SELECT SCTA_DS_STATUS, 
                       COUNT(*) QTD,
                       CAST(SUM(PFDS_QT_PF_BRUTO) AS NUMBER(9,2)) PF_BRUTO,
                       CAST(SUM(PFDS_QT_PF_LIQUIDO) AS NUMBER(9,2)) PF_LIQUIDO
                  FROM SOS_TB_PFDS_APF_DESENVOLVEDORA 
            INNER JOIN SOS_TB_SCTA_STATUS_CONTRATADA
                    ON PFDS_ID_STATUS = SCTA_ID_STATUS
                 WHERE PFDS_ID_SOLICITACAO IN ($ids)
              GROUP BY SCTA_DS_STATUS";
            $retornoStatusDsv = $this->db->query($status_dsv);
            $total["STATUS_DSV"] = $retornoStatusDsv->fetchAll();

            $status_afe = "SELECT SCTA_DS_STATUS, 
                       COUNT(*) QTD,
                       CAST(SUM(PFAF_QT_PF_BRUTO) AS NUMBER(9,2)) PF_BRUTO,
                       CAST(SUM(PFAF_QT_PF_LIQUIDO) AS NUMBER(9,2)) PF_LIQUIDO
                  FROM SOS_TB_PFAF_APF_AFERIDORA
            INNER JOIN SOS_TB_SCTA_STATUS_CONTRATADA
                    ON PFAF_ID_STATUS = SCTA_ID_STATUS
                 WHERE PFAF_ID_SOLICITACAO IN ($ids)
              GROUP BY SCTA_DS_STATUS";
            $retornoStatusAfe = $this->db->query($status_afe);
            $total["STATUS_AFE"] = $retornoStatusAfe->fetchAll();

            $status_trf = "SELECT SCTA_DS_STATUS, 
                       COUNT(*) QTD
                  FROM SOS_TB_PFTR_APF_TRIBUNAL
            INNER JOIN SOS_TB_SCTA_STATUS_CONTRATADA
                    ON PFTR_ID_STATUS = SCTA_ID_STATUS
                 WHERE PFTR_ID_SOLICITACAO IN ($ids)
              GROUP BY SCTA_DS_STATUS";
            $retornoStatusTRF = $this->db->query($status_trf);
            $total["STATUS_TRF"] = $retornoStatusTRF->fetchAll();

            return $total;
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit;
        }
    }

    /*     * *******************************************************************
     * ********************** FUNÇÕES DE CLASSIFICACAO *************************
     * ********************************************************************** */

    /**
     * Função que busca os tipos de classificação do faturamento
     * @author Pedro Henrique
     */
    public function retornaClassificacao() {
        $classificacao = $this->tb_classificacao_faturamento->fetchAll();
        return $classificacao->toArray();
    }

    /*     * *******************************************************************
     * ********************** FUNÇÕES DO CONTRATADO ****************************
     * ********************************************************************** */

    public function dadosFaturamentoContratada($idSolicitacao) {
        $contratada = $this->SosTbPfdsApfDesenvolvedora->fetchAll("PFDS_ID_SOLICITACAO = $idSolicitacao");
        $dados = $contratada->toArray();
        $valor = new Trf1_Orcamento_Valor ();
        
        if (!empty($dados))
        {
            $pfBruto = $contratada [ 0 ] [ 'PFDS_QT_PF_BRUTO' ];
            $numBruto = $valor->retornaNumeroConvertido ( $pfBruto,4 );

            $pfLiquido = $contratada [ 0 ] [ 'PFDS_QT_PF_LIQUIDO' ];
            $numLiquido = $valor->retornaNumeroConvertido ( $pfLiquido,4 );

            $contratada [ 0 ] [ 'PFDS_QT_PF_BRUTO' ] = $numBruto;
            $contratada [ 0 ] [ 'PFDS_QT_PF_LIQUIDO' ] = $numLiquido;
        }
        return $contratada->toArray ();
    }

    public function salvarDadosDesenvolvedora($dadosDsv) {
        try {
                
            if (($dadosDsv["PFDS_ID_STATUS"] == 7) && ($dadosDsv["PFDS_ID_CLASSIFICACAO"] != 17))
            {
                $dadosAfe["PFAF_ID_SOLICITACAO"]    = $dadosDsv["PFDS_ID_SOLICITACAO"];
                $dadosAfe["PFAF_ID_CLASSIFICACAO"]  = $dadosDsv["PFDS_ID_CLASSIFICACAO"];
                
                $dadosTrf["PFTR_ID_SOLICITACAO"]    = $dadosDsv["PFDS_ID_SOLICITACAO"];
                $dadosTrf["PFTR_ID_CLASSIFICACAO"]  = $dadosDsv["PFDS_ID_CLASSIFICACAO"];
            }
            
            if (($dadosDsv["PFDS_ID_STATUS"] == 7) && ($dadosDsv["PFDS_ID_CLASSIFICACAO"] == 17))
            {
                $dadosAfe["PFAF_ID_SOLICITACAO"]    = $dadosDsv["PFDS_ID_SOLICITACAO"];
                $dadosAfe["PFAF_ID_STATUS"]  = 23;

                $dadosTrf["PFTR_ID_SOLICITACAO"]    = $dadosDsv["PFDS_ID_SOLICITACAO"];
                $dadosTrf["PFTR_ID_STATUS"]  = 17;
            }
            
            // RIA Original
            if ($dadosDsv["PFDS_NR_DCMTO_RIA_ORIGINAL"]) 
            {
                if ($dadosDsv["PFDS_ID_CLASSIFICACAO"] != 17)
                {
                    echo $dadosDsv["STSA_ID_TIPO_SAT"];
                    if ($dadosDsv["STSA_ID_TIPO_SAT"] == null || $dadosDsv["STSA_ID_TIPO_SAT"] == 6) 
                    {
                        $dadosDsv["PFDS_ID_STATUS"] = 2; //Aguardando Avaliação
                    } 
                        else 
                        {
                            $dadosDsv["PFDS_ID_STATUS"] = 3; // Liberado para contagem
                        }
                    unset($dadosDsv["FADM_ID_FASE"]);
                    $dadosDsv["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
            }

            //RIA a Esclarecer
            if ($dadosDsv["PFDS_NR_DCMTO_RIA_ESCLARECER"]) 
            {
                $dadosDsv["PFDS_ID_STATUS"] = 4; // Pendente de esclarecimento
                $dadosDsv["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
            }

            //RIA esclarecido
            if ($dadosDsv["PFDS_NR_DCMTO_RIA_ESCLARECIDO"]) 
            {
                $dadosDsv["PFDS_ID_STATUS"] = 5; // Esclarecimento realizado
                $dadosDsv["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
            }

            //Planilha de contagem PF
            if ($dadosDsv["PFDS_NR_DCMTO_CONTAGEM"]) 
            {
                
               
               /**************
                Baixada - NULL
                Avaliada != 6
                Recusada == 6
                *************/
                 
                if ($dadosDsv["STSA_ID_TIPO_SAT"] == null || $dadosDsv["STSA_ID_TIPO_SAT"] == 6) 
                {
                    $dadosDsv["PFDS_ID_STATUS"] = 2; //Aguardando Avaliação
                } 
                    else 
                    {
                        $dadosDsv["PFDS_ID_STATUS"] = 7; // Publicado
                        
                        $dadosAfe["PFAF_ID_SOLICITACAO"] = $dadosDsv["PFDS_ID_SOLICITACAO"];
                        $dadosAfe["PFAF_ID_STATUS"] = 8; // Aguardando Aferição
                        $dadosAfe["PFAF_ID_CLASSIFICACAO"]  = $dadosDsv["PFDS_ID_CLASSIFICACAO"];
                        $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");

                        $dadosTrf["PFTR_ID_SOLICITACAO"] = $dadosDsv["PFDS_ID_SOLICITACAO"];
                        $dadosTrf["PFTR_ID_STATUS"] = 16; // Aguardando Aferição
                        $dadosTrf["PFTR_ID_CLASSIFICACAO"]  = $dadosDsv["PFDS_ID_CLASSIFICACAO"];
                        $dadosTrf["PFTR_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                    }
                
                $dadosDsv["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                
                
                
             }
            
            $verificaRegistroDsv = $this->SosTbPfdsApfDesenvolvedora->fetchRow('PFDS_ID_SOLICITACAO = ' . $dadosDsv["PFDS_ID_SOLICITACAO"]);
            if ($verificaRegistroDsv) {
                /**
                 * Veirifica se foi alterado o status
                 */
                if ($dadosDsv["PFDS_ID_STATUS"] != $verificaRegistroDsv["PFDS_ID_STATUS"] || $dadosDsv["PFDS_DH_STATUS"] == null) {
                    $dadosDsv["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
                $dadosDesenvolvedora = $this->SosTbPfdsApfDesenvolvedora->find($verificaRegistroDsv["PFDS_ID_APF_DESENVOLVEDORA"])->current();
                $dadosDesenvolvedora->setFromArray($dadosDsv);
            } else {
                if ($dadosDsv["PFDS_ID_STATUS"]) {
                    $dadosDsv["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
                $dadosDesenvolvedora = $this->SosTbPfdsApfDesenvolvedora->createRow($dadosDsv);
                Zend_Debug::dump($dadosDesenvolvedora,'dd');
                
            }
            #EXIT;
            if ($dadosAfe) 
            {
                $this->salvarDadosAfericao($dadosAfe);
                
            }
            
            if ($dadosTrf) 
            {
                $this->salvarDadosTRF($dadosTrf);
                
            }
            
            
            
            $dadosDesenvolvedora->save();
            $msg = "
                    SUC_CADE_01 - Dados da desenvolvedora cadastrados com sucesso.
                    ";
        } catch (Exception $exc) {
            $error = $exc->getMessage();
            $msg = "
                    ERR_CADE_01 - Erro ao cadastrar dados. - Erro: $error
                    ";
        }
        return $msg;
    }

    /*     * *******************************************************************
     * ********************** FUNÇÕES DA AFERIÇÃO ******************************
     * ********************************************************************** */

    public function dadosFaturamentoAfericao($idSolicitacao) {
        $afericao = $this->SosTbPfafApfAferidora->fetchAll("PFAF_ID_SOLICITACAO = $idSolicitacao");
        return $afericao->toArray();
    }

    public function salvarDadosAfericao($dadosAfe) {
        try {
            
            
            $registroAfericao = $this->SosTbPfafApfAferidora->fetchRow('PFAF_ID_SOLICITACAO = ' . $dadosAfe["PFAF_ID_SOLICITACAO"]);
            if ($dadosAfe["PFAF_ID_STATUS"] != $registroAfericao["PFAF_ID_STATUS"]) 
            {
                $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
            }
     
            if (($dadosAfe["PFAF_ID_STATUS"] == 10) && $dadosAfe["PFAF_ID_STATUS"] != $registroAfericao["PFAF_ID_STATUS"]) 
            {
                $dadosTrf["PFTR_ID_SOLICITACAO"] = $dadosAfe["PFAF_ID_SOLICITACAO"];
                $dadosTrf["PFTR_ID_STATUS"] = 17;
                $dadosTrf["PFTR_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
            }

            //RIA com Parecer a Efetuar
            if ($dadosAfe["PFAF_NR_DCMTO_RIA_PARECER"]) 
            {
                $dadosAfe["PFAF_ID_STATUS"] = 9;
                $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
            }
            
            //RIA com Parecer Efetuado
            if ($dadosAfe["PFAF_NR_DCMTO_RIA_ESCLARECIDO"]) 
            {
                $dadosAfe["PFAF_ID_STATUS"] = 11;
                $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
            }
            
            //Planilha de Contagem PF
            if ($dadosAfe["PFAF_NR_DCMTO_CONTAGEM"]) 
            {
                $verificaRegistroAfe = $this->SosTbPfdsApfDesenvolvedora->fetchRow('PFDS_ID_SOLICITACAO = ' . $dadosAfe["PFAF_ID_SOLICITACAO"]);
                if ($verificaRegistroAfe["PFDS_QT_PF_BRUTO"] != $dadosAfe["PFAF_QT_PF_BRUTO"]) 
                {
                    $dadosAfe["PFAF_ID_STATUS"] = 13;
                    $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
                
                if ($verificaRegistroAfe["PFDS_QT_PF_LIQUIDO"] != $dadosAfe["PFAF_QT_PF_LIQUIDO"]) 
                {
                    $dadosAfe["PFAF_ID_STATUS"] = 14;
                    $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
                
                if (($verificaRegistroAfe["PFDS_QT_PF_BRUTO"] == $dadosAfe["PFAF_QT_PF_BRUTO"]) &&
                    ($verificaRegistroAfe["PFDS_QT_PF_LIQUIDO"] == $dadosAfe["PFAF_QT_PF_LIQUIDO"])) 
                {
                    $dadosAfe["PFAF_ID_STATUS"] = 15;
                    $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");

                    $dadosTrf["PFTR_ID_SOLICITACAO"] = $dadosAfe["PFAF_ID_SOLICITACAO"];
                    $dadosTrf["PFTR_ID_STATUS"] = 18;
                    $dadosTrf["PFTR_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
            }
            
            
            
            if ($registroAfericao) 
            {
                $dadosAfericao = $this->SosTbPfafApfAferidora->find($registroAfericao["PFAF_ID_APF_AFERICAO"])->current();
                $dadosAfericao->setFromArray($dadosAfe);
            } 
                else 
                {
                    $dadosAfericao = $this->SosTbPfafApfAferidora->createRow($dadosAfe);
                }   

            if ($dadosTrf) 
            {
                $this->salvarDadosTRF($dadosTrf);
            }

            $dadosAfericao->save();
            $msg = "
                    SUC_CAFE_01 - Dados de Aferiçao cadastrados com sucesso.
                    ";
        } catch (Exception $exc) {
            $error = $exc->getMessage();
            $msg = "
                    ERR_CAFE_01 - Problemas ao cadastrar dados da Aferição. Error: $error
                    ";
        }
        return $msg;
    }

    /*     * *******************************************************************
     * ********************* FUNÇÕES DO CONTRATANTE ****************************
     * ********************************************************************** */

    public function dadosFaturamentoContratante($idSolicitacao) {
        $contratante = $this->SosTbPftrApfTribunal->fetchAll("PFTR_ID_SOLICITACAO = $idSolicitacao");
        return $contratante->toArray();
    }

    public function salvarDadosTRF($dadosTrf) 
    {
        try {
                $cadastroTrf = $this->SosTbPftrApfTribunal->fetchRow('PFTR_ID_SOLICITACAO = ' . $dadosTrf["PFTR_ID_SOLICITACAO"]);
                if ($dadosTrf["PFTR_ID_STATUS"] != $cadastroTrf["PFTR_ID_STATUS"]) 
                {
                    $dadosTrf["PFTR_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }
                
                if (($dadosTrf["PFTR_ID_STATUS"] == 18) && ($cadastroTrf["PFTR_ID_STATUS"] != $dadosTrf["PFTR_ID_STATUS"])) 
                {
                    $dadosAfe["PFAF_ID_SOLICITACAO"] = $dadosTrf["PFTR_ID_SOLICITACAO"];
                    $dadosAfe["PFAF_ID_STATUS"] = 12;
                    $dadosAfe["PFAF_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                }

                if ($cadastroTrf) 
                {
                    $dadosCadTrf = $this->SosTbPftrApfTribunal->find($cadastroTrf["PFTR_ID_PF_CONTRATANTE"])->current();
                    $dadosCadTrf->setFromArray($dadosTrf);
                } 
                    else 
                    {
                        $dadosCadTrf = $this->SosTbPftrApfTribunal->createRow($dadosTrf);
                    }

                if ($dadosAfe) 
                {
                    $this->salvarDadosAfericao($dadosAfe);
                }
                $dadosCadTrf->save();
                $msg = "
                    SUC_CATR_01 - Dados do Trf Cadastrados com Sucesso
                    ";
            } 
                catch (Exception $exc) 
                {
                    $error = $exc->getMessage();
                    $msg = "
                            ERR_CATR_01 - Problemas ao cadastrar dados do Trf. Erro: $error
                        ";
        }
        return $msg;
    }

    public function getRelatorioRias($params, $vinc = false) {

        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "SELECT DISTINCT SSOL_ID_DOCUMENTO, 
                        DOCM_NR_DOCUMENTO,
                        MOVI_DH_ENCAMINHAMENTO, 
                        MOFA_ID_FASE,
                        MOFA_ID_MOVIMENTACAO,
                        TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') DATA_HORA_BAIXA,
                        TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') ENTRADA_CAIXA,
                        MOFA_CD_MATRICULA||' - '||PNAT_NO_PESSOA ATENDENTE,
                        MOFA_CD_MATRICULA,
                        SCTA_DSV.SCTA_ID_STATUS SCTA_ID_STATUS_DSV,
                        SCTA_DSV.SCTA_DS_STATUS SCTA_DS_STATUS_DSV,
                        CLCO_DSV.CLCO_DS_OBSERVACAO CLCO_DS_OBSERVACAO_DSV,
                        SCTA_AFE.SCTA_ID_STATUS SCTA_ID_STATUS_AFE,
                        SCTA_AFE.SCTA_DS_STATUS SCTA_DS_STATUS_AFE,
                        CLCO_AFE.CLCO_DS_OBSERVACAO CLCO_DS_OBSERVACAO_AFE,
                        SCTA_TRF.SCTA_ID_STATUS SCTA_ID_STATUS_TRF,
                        SCTA_TRF.SCTA_DS_STATUS SCTA_DS_STATUS_TRF,
                        CLCO_TRF.CLCO_DS_OBSERVACAO CLCO_DS_OBSERVACAO_TRF,
                        PFDS_QT_PF_BRUTO,
                        PFDS_QT_PF_LIQUIDO,
                        STSA_DS_TIPO_SAT,
                        STSA_ID_TIPO_SAT,
                        PFAF_QT_PF_BRUTO,
                        PFAF_QT_PF_LIQUIDO
                ";
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->leftJoinServicoGrupoServico();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= "
                LEFT JOIN SOS_TB_PFDS_APF_DESENVOLVEDORA PFDS
                      ON SSOL.SSOL_ID_DOCUMENTO = PFDS_ID_SOLICITACAO
                LEFT JOIN SOS_TB_SCTA_STATUS_CONTRATADA SCTA_DSV
                       ON PFDS.PFDS_ID_STATUS = SCTA_DSV.SCTA_ID_STATUS      
                LEFT JOIN SOS_TB_CLCO_CLASSIFICACAO_CONT CLCO_DSV
                       ON PFDS.PFDS_ID_CLASSIFICACAO = CLCO_DSV.CLCO_ID_CLASSIFICACAO
                LEFT JOIN SOS_TB_PFAF_APF_AFERIDORA PFAF
                      ON SSOL.SSOL_ID_DOCUMENTO = PFAF_ID_SOLICITACAO
                LEFT JOIN SOS_TB_SCTA_STATUS_CONTRATADA SCTA_AFE
                       ON PFAF.PFAF_ID_STATUS = SCTA_AFE.SCTA_ID_STATUS      
                LEFT JOIN SOS_TB_CLCO_CLASSIFICACAO_CONT CLCO_AFE
                       ON PFAF.PFAF_ID_CLASSIFICACAO = CLCO_AFE.CLCO_ID_CLASSIFICACAO                  
                LEFT JOIN SOS_TB_PFTR_APF_TRIBUNAL PFTR
                      ON SSOL.SSOL_ID_DOCUMENTO = PFTR_ID_SOLICITACAO
                LEFT JOIN SOS_TB_SCTA_STATUS_CONTRATADA SCTA_TRF
                       ON PFTR.PFTR_ID_STATUS = SCTA_TRF.SCTA_ID_STATUS      
                LEFT JOIN SOS_TB_CLCO_CLASSIFICACAO_CONT CLCO_TRF
                       ON PFTR.PFTR_ID_CLASSIFICACAO = CLCO_TRF.CLCO_ID_CLASSIFICACAO
                LEFT JOIN OCS_TB_PMAT_MATRICULA
                       ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA
                LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL
                       ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
        ";
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereUltimaAvaliacao();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereCaixa(true, 2);
        $stmt .= $CaixasQuerys->whereUltimaFaseHistorico(true, 1000);
        if (!$vinc)
        {    
            $stmt .= "  
                    AND SSOL_ID_DOCUMENTO NOT IN (SELECT VIDC_ID_DOC_VINCULADO FROM SAD_TB_VIDC_VINCULACAO_DOC)
                 ";
        }
        ($params['DATA_INICIAL'] && $params['DATA_FINAL']) ? ($stmt .= " AND MOFA_DH_FASE between TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . " 23:59:59', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= " AND MOFA_DH_FASE <= TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= " AND MOFA_DH_FASE >= TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') ") : ("");

        ($params['DATA_ENTRADA_CAIXA_INICIAL'] && $params['DATA_ENTRADA_CAIXA_FINAL']) ? ($stmt .= " AND TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY') between TO_DATE('" . $params['DATA_ENTRADA_CAIXA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_ENTRADA_CAIXA_FINAL']  . " 23:59:59', 'DD/MM/YYYY hh24:mi:ss') ") : ("");
        (($params['DATA_ENTRADA_CAIXA_INICIAL'] == "") && ($params['DATA_ENTRADA_CAIXA_FINAL'] != "")) ? ($stmt .= " TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY') <= TO_DATE('" . $params['DATA_ENTRADA_CAIXA_FINAL'] . "', 'DD/MM/YYYY') ") : ("");
        (($params['DATA_ENTRADA_CAIXA_INICIAL'] != "") && ($params['DATA_ENTRADA_CAIXA_FINAL'] == "")) ? ($stmt .= " AND TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY') >= TO_DATE('" . $params['DATA_ENTRADA_CAIXA_INICIAL'] . "', 'DD/MM/YYYY') ") : ("");

        ($params['MOFA_CD_MATRICULA']) ? ($stmt .= " AND MOFA_CD_MATRICULA||' - '||PNAT_NO_PESSOA = '" . $params['MOFA_CD_MATRICULA'] . "' ") : ("");
        ($params['DOCM_NR_DOCUMENTO']) ? ($stmt .= " AND DOCM_NR_DOCUMENTO = " . $params['DOCM_NR_DOCUMENTO'] . " ") : ("");
        ($params['PFDS_ID_STATUS']) ? ($stmt .= " AND PFDS_ID_STATUS = " . $params['PFDS_ID_STATUS'] . "") : (" ");
        ($params['PFDS_ID_CLASSIFICACAO']) ? ($stmt .= " AND PFDS_ID_CLASSIFICACAO =" . $params['PFDS_ID_CLASSIFICACAO'] . " ") : ("");
        
        ($params['PFAF_ID_STATUS']) ? ($stmt .= " AND PFAF_ID_STATUS = " . $params['PFAF_ID_STATUS'] . "") : ("");
        ($params['PFAF_ID_CLASSIFICACAO']) ? ($stmt .= " AND PFAF_ID_CLASSIFICACAO = " . $params['PFAF_ID_CLASSIFICACAO'] . " ") : ("");
        ($params['PFAF_NR_LOTE']) ? ($stmt .= " AND PFAF_NR_LOTE = " . $params['PFAF_NR_LOTE'] . " ") : ("");
        ($params['PFAF_DH_PREVISAO_RETORNO_LOTE']) ? ($stmt .= " AND PFAF_DH_PREVISAO_RETORNO_LOTE = TO_DATE('" . $params['PFAF_DH_PREVISAO_RETORNO_LOTE'] . "', 'DD/MM/YYYY') ") : ("");
        ($params['PFAF_DH_RETORNO_LOTE']) ? ($stmt .= " AND PFAF_DH_RETORNO_LOTE = TO_DATE('" . $params['PFAF_DH_RETORNO_LOTE'] . "', 'DD/MM/YYYY') ") : ("");
        
        ($params['PFTR_ID_STATUS']) ? ($stmt .= " AND PFTR_ID_STATUS = " . $params['PFTR_ID_STATUS'] . "") : ("");
        ($params['PFTR_ID_CLASSIFICACAO']) ? ($stmt .= " AND PFTR_ID_CLASSIFICACAO = " . $params['PFTR_ID_CLASSIFICACAO'] . " ") : ("");
        ($params['PFTR_NR_ID_RELAT_FATURAMENTO']) ? ($stmt .= " AND PFTR_NR_ID_RELAT_FATURAMENTO = " . $params['PFTR_NR_ID_RELAT_FATURAMENTO'] . " ") : ("");
        ($params['PFTR_DH_FATURAMENTO']) ? ($stmt .= " AND TO_DATE(PFTR_DH_FATURAMENTO) = TO_DATE('" . $params['PFTR_DH_FATURAMENTO'] . "', 'DD/MM/YYYY') ") : ("");

        ($params['STATUS_SOLICITACAO'] == 1000) ? ($stmt .= " AND STSA_ID_TIPO_SAT IS NULL ") : ("");
        ($params['STATUS_SOLICITACAO'] == 1014) ? ($stmt .= " AND STSA_ID_TIPO_SAT IN(1,2,3,4,5,7) ") : ("");
        ($params['STATUS_SOLICITACAO'] == 1019) ? ($stmt .= " AND STSA_ID_TIPO_SAT = 6 ") : ("");

        $stmt .= "ORDER BY DATA_HORA_BAIXA ASC";
        $stmt = $this->db->query($stmt);
        
//        Zend_Debug::dump($stmt);
//        exit;
        return $stmt->fetchAll();
    }
    
    public function getAvalRias($params, $vinc = false) {
        
        Zend_Debug::dump($params,'params');
        
        
        $CaixasQuerys = new App_Sosti_CaixasQuerys();

        $stmt = "SELECT DISTINCT SSOL_ID_DOCUMENTO, 
                        DOCM_NR_DOCUMENTO,
                        STSA_DS_TIPO_SAT,
                        STSA_ID_TIPO_SAT
                ";
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseAvaliacao();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimoServico(false);
        $stmt .= $CaixasQuerys->whereUltimaFaseHistorico(true, 1000);
        
        ($params['DOCM_NR_DOCUMENTO']) ? ($stmt .= " AND DOCM_NR_DOCUMENTO = " . $params['DOCM_NR_DOCUMENTO'] . " ") : ("");
      
        $stmt = $this->db->query($stmt);
        return $stmt->fetchAll();
    }

    public function calculoStatus($dadosRelatorio) {
        foreach ($dadosRelatorio as $value) {

            switch ($value["SCTA_ID_STATUS_DSV"]) {
//                  1 RIA pendente CTA
                case 1:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += $value["PFDS_QT_PF_BRUTO"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += $value["PFDS_QT_PF_LIQUIDO"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;

                    break;
//                  2 Aguardando avaliação CTA
                case 2:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_BRUTO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_LIQUIDO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;
                    break;
//                  3 Liberado para contagem CTA
                case 3:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_BRUTO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_LIQUIDO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;
                    break;
//                  4 Pendente de esclarecimento CTA
                case 4:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_BRUTO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_LIQUIDO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;
                    break;
//                  5 Esclarecimento realizado CTA
                case 5:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_BRUTO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_LIQUIDO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;
                    break;
//                  6 Liberado para publicação CTA
                case 6:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_BRUTO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_LIQUIDO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;
                    break;
//                  7 Publicado CTA                
                case 7:
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["NOME"] = $value["SCTA_DS_STATUS_DSV"];
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_BRUTO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFDS_QT_PF_LIQUIDO"]));
                    $dadosTotal["DSV"][$value["SCTA_ID_STATUS_DSV"]]["QTD"]++;
                    break;

                default:
                    break;
            }
            switch ($value["SCTA_ID_STATUS_AFE"]) {
//                  8 Aguardando aferição AFE
                case 8:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  9 Pendente de parecer/ajuste AFE
                case 9:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  10 Pendente de análise TRF AFE
                case 10:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  11 Parecer/ajuste realizadoo AFE
                case 11:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  12 Análise TRF realizada AFE
                case 12:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  13 Divergência de PF Bruto AFE
                case 13:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  14 Divergência de PF Líquido AFE
                case 14:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
//                  15 Aferido AFE                
                case 15:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;
                case 22:
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_BRUTO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_BRUTO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["PF_LIQUIDO"] += floatval(str_replace(',', '.', $value["PFAF_QT_PF_LIQUIDO"]));
                    $dadosTotal["AFE"][$value["SCTA_ID_STATUS_AFE"]]["QTD"]++;
                    break;

                default:
                    break;
            }
            switch ($value["SCTA_ID_STATUS_TRF"]) {
//                  16 Aguardando aferição CTE
                case 16:
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["NOME"] = $value["SCTA_DS_STATUS_TRF"];
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["QTD"]++;
                    ;
                    break;
//                  17 Pendente de análise TRF CTE
                case 17:
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["NOME"] = $value["SCTA_DS_STATUS_TRF"];
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["QTD"]++;
                    ;
                    break;
//                  18 Liberado para faturamento CTE
                case 18:
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["NOME"] = $value["SCTA_DS_STATUS_TRF"];
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["QTD"]++;
                    ;
                    break;
//                  19 Faturamento negado CTE
                case 19:
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["NOME"] = $value["SCTA_DS_STATUS_TRF"];
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["QTD"]++;
                    ;
                    break;
//                  20 Faturamento realizado CTE
                case 20:
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["NOME"] = $value["SCTA_DS_STATUS_TRF"];
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["QTD"]++;
                    ;
                    break;
//                  21 Pagamento realizado CTE
                case 21:
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["NOME"] = $value["SCTA_DS_STATUS_AFE"];
                    $dadosTotal["TRF"][$value["SCTA_ID_STATUS_TRF"]]["QTD"]++;
                    ;
                    break;

                default:
                    break;
            }
        }
        return $dadosTotal;
    }

    public function recuperarDoc($idDocumento, $numeroDocumento, $nrDocRed, $tipoDoc, $faturamento) {
        /**
         * Ajusta o memory limit para 256M para permitir a recuperação de arquivos de até 50Megas sem estourar os 128Megas padrão
         */
        ini_set("memory_limit", "256M");

        /**
         * Variáveis de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;
        mkdir(APPLICATION_PATH . '/../temp/download/', 0777);

        /**
         * Chamada da função de controller de reuperação de documentos
         */
        try {
            $arquivo = $this->recuperar($idDocumento, $nrDocRed, $matricula, $faturamento);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            return;
        }
        /**
         * Recuperação do Banco de dados o tipo de extensão do arquivo
         * Valor padrão é 1 tipo PDF
         */
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($tipoDoc);
        $extencaoDocumento = '.' . $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];

        /**
         * Endereço temporário do arquivo recuperado
         */
        $tmpfname_aux = APPLICATION_PATH . '/../temp/download/';
        $tmpfname_aux = substr($tmpfname_aux, 0, strpos($tmpfname_aux, 'application')) . "temp/download" . DIRECTORY_SEPARATOR;

        /**
         * Construção do nome do arquivo com extensão.
         */
        $tmpfname = $numeroDocumento . $extencaoDocumento;
        $tmpfname = $tmpfname_aux . $tmpfname;

        /**
         * Verifica se o arquivo não é vazio
         */
        if (!$arquivo) {
            echo 'Documento não encontrado.';
            return;
        }

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $arquivo);
        fclose($handle);
        unset($arquivo);

        $retorno["NOME"] = '' . $numeroDocumento . $extencaoDocumento;
        $retorno["ENDERECO"] = $tmpfname;
        return $retorno;
    }

    public function recuperar($idDocumento, $numeroDocumento, $matricula, $faturamento = null) {
        $envProducao = false;
        if (APPLICATION_ENV == 'development') {
            $matricula = 'TR227PS';
            $envProducao = true;
        }

        $parametros = new Services_Red_Parametros_Recuperar();
        $parametros->ip = substr($_SERVER['REMOTE_ADDR'], 0, 15);
        $parametros->login = $matricula;
        $parametros->sistema = 'EADMIN';
        $parametros->nomeMaquina = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
        $parametros->numeroDocumento = $numeroDocumento;

        try {

            $red = new Services_Red_Recuperar($envProducao);
            $red->debug = false;
            $retorno = $red->recuperar($parametros);

            $arquivo = $red->openHttpsUrl($retorno['url']);
            return $arquivo;
        } catch (Exception $exc) {
            throw $exc;
        }
    }

}