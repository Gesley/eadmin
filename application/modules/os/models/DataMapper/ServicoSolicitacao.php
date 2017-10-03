<?php
/**
 * Troca o Servico de uma Solicitação
 * Recebe como parametros de entrada
 * @param $idDocmDocumento
 * @param $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = ;
 * @param $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
 * @param $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = ;
 * @param $dataSsesServicoSolic["SSES_ID_SERVICO"] = ;
 * 
 * @return void
 */
class Os_Model_DataMapper_ServicoSolicitacao extends Zend_Db_Table_Abstract
{
    public function setTrocaServicoOs($idDocmDocumento,$dataSsolSolicitacao, array $dataMofaMoviFase, array $dataSsesServicoSolic, $nrDocsRed = null, $allParams)
    {
//Zend_Debug::dump($idDocmDocumento);//ok
//Zend_Debug::dump($dataSsolSolicitacao);
//Zend_Debug::dump($dataMofaMoviFase);
//Zend_Debug::dump($dataSsesServicoSolic);
//Zend_Debug::dump($nrDocsRed);
//Zend_Debug::dump($allParams);
//exit;
////$dataMofaMoviFase["MOFA_ID_FASE"] = 2006; 
////$SosSsol = new Application_Model_DbTable_SosTbSsolSolicitacao();
////$r = $SosSsol->setLancarFase($idDocmDocumento, $dataMofaMoviFase);
//Zend_Debug::dump($r);
//exit;                                                        
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $dual = new Application_Model_DbTable_Dual();
            $datahora = $dual->sysdate();

            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 2006; /** TROCA DE CATEGORIA DE SERVIÇO **/
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr( " CAST( '". $allParams["DOCM_DS_ASSUNTO_DOC"] ."' AS VARCHAR(4000)) " );

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMovimentacao = $rowMofaMoviFase->save();
            $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();


            $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMovimentacao;
//            $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataSsesServicoSolic["SSES_DH_FASE"] = $datahora;
            $dataSsesServicoSolic['SSES_ID_DOCUMENTO'] = $idDocmDocumento;
            if(isset($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]) && !is_null($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"])){
                $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = new Zend_Db_Expr("TO_DATE('".$dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]."','dd/mm/yyyy HH24:MI:SS')"); 
                $dataSsesServicoSolic["SSES_IC_VIDEO_REALIZADA"] = "N";
            }
            $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
            $rowSsesServicoSolic->save();
                
            /**
             * Atualiza o número do tombo
             */
            $dataSsolSolicitacao["SSOL_NR_TOMBO"];
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $rowSsolSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();;
            $rowSsolSolicitacao->setFromArray($dataSsolSolicitacao);
            $rowSsolSolicitacao->save();
            
            //Ultima Fase do lançada na Solicitação.//
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();;
            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();
            /* ---------------------------------------------------------------------------------------- */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
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
             * Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idDocmDocumento AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
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
             * Atualiza as categorias do serviço.
             */
            $tabelaAsis = new Application_Model_DbTable_SosTbAsisAtendSistema();

            $id_categoria_servico = $allParams['CTSS_NM_CATEGORIA_SERVICO'];
            $ic_nivel_criticidade = $allParams['ASIS_IC_NIVEL_CRITICIDADE'];
            
//            Zend_Debug::dump($allParams['EMERGENCIAL']);
//            Zend_Debug::dump($id_categoria_servico);
//            Zend_Debug::dump($ic_nivel_criticidade);
//            Zend_Debug::dump($atendSistemaAtual['ASSO_ID_ATEND_SISTEMA_SOLIC']);
//            Zend_Debug::dump($allParams['EMERGENCIAL']);
//            Zend_Debug::dump($dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"]);
//            Zend_Debug::dump($dataAsso);
//            Zend_Debug::dump($allParams);
//            exit;

            if ($id_categoria_servico == '2') {
                $data['EMERGENCIAL'] = 'S';
            }
            $idAsis = $tabelaAsis->getIdAtendimentoSistema($allParams['EMERGENCIAL'], $id_categoria_servico, $ic_nivel_criticidade);

            /* Inclusão na tabela Atendimento Sistema */
            $dataAsso['ASSO_ID_ATENDIMENTO_SISTEMAS'] = $idAsis['ASIS_ID_ATENDIMENTO_SISTEMA'];
            $dataAsso['ASSO_ID_MOVIMENTACAO'] = $DocmDocumento["MOVI_ID_MOVIMENTACAO"];

            if ($allParams['EMERGENCIAL'] == 'S') {
                $dataAsso['ASSO_IC_ATENDIMENTO_EMERGENCIA'] = 'S';
            } else if ($allParams['EMERGENCIAL']) {
                $dataAsso['ASSO_IC_ATENDIMENTO_EMERGENCIA'] = 'N';
            }

            if ($allParams['CAUSA_PROBLEMA'] == 1) {
                /* SITUAÇÃO CAUSA */
                $dataAsso['ASSO_IC_SOLUCAO_CAUSA_PROBLEMA'] = 'S';
                $dataAsso['ASSO_IC_SOLUCAO_PROBLEMA'] = 'N';
            } else {
                /* SITUAÇÃO PROBLEMA */
                $dataAsso['ASSO_IC_SOLUCAO_CAUSA_PROBLEMA'] = 'N';
                $dataAsso['ASSO_IC_SOLUCAO_PROBLEMA'] = 'S';
            }
            $dataAsso['ASSO_ID_MOVIMENTACAO'] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $tabelaAsso = new Application_Model_DbTable_SosTbAssoAtendSistemSolic();
            $atendSistemaAtual = $tabelaAsso->fetchRow(array('ASSO_ID_MOVIMENTACAO = ?' => $dataAsso['ASSO_ID_MOVIMENTACAO']));
            $rowUltimaAsso = $tabelaAsso->find($atendSistemaAtual['ASSO_ID_ATEND_SISTEMA_SOLIC'])->current();
            $rowUltimaAsso->setFromArray($dataAsso);
            $rowUltimaAsso->save();

            /* Vinculacao do tipo Causa Problema */
            $arr_solic_prob = explode(',', $allParams['SOLIC_PROBLEMAS']);

//            foreach ($arr_solic_prob as $nr_solic) {
//                if ($nr_solic != "") {
//                    $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
//                    $tabelaVidc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
//                    $dual = new Application_Model_DbTable_Dual();
//                    $dadosSolic = $tabelaSadTbDocmDocumento->fetchRow(array('DOCM_NR_DOCUMENTO = ?' => $nr_solic));
//                    $dataVidc['VIDC_ID_DOC_PRINCIPAL'] = $dados_input['SSOL_ID_DOCUMENTO'];
//                    $dataVidc['VIDC_ID_DOC_VINCULADO'] = $dadosSolic['DOCM_ID_DOCUMENTO'];
//                    $dataVidc['VIDC_ID_TP_VINCULACAO'] = 6; /* VINCULACAO CAUSA PROBLEMA */
//                    $dataVidc['VIDC_DH_VINCULACAO'] = $dual->sysdate();
//                    $dataVidc['VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
//                    $rowVidc = $tabelaVidc->createRow($dataVidc);
//                    $rowVidc->save();
//                }
//            }
            if ($allParams["NEGA_IC_SOLICITA"] == "S") {
                $dadosArraySolicitaGarantia["NEGA_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
                $dadosArraySolicitaGarantia["NEGA_DH_SOLIC_GARANTIA"] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                $dadosArraySolicitaGarantia["NEGA_DS_JUSTIFICATIVA_PEDIDO"] = $allParams["NEGA_DS_JUSTIFICATIVA_PEDIDO"];
                $NegociaGarantiaDesenvolvimento->setSolicitaGarantia($dadosArraySolicitaGarantia);
            }
            $db->commit();
        
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
        }
        return $datahora;
    }
    
    public static function getServicosCaixa($idGrupo)
    {
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $servicos = $SosTbSserServico->getServicoPorGrupo($idGrupo, 'SSER_DS_SERVICO ASC');
        foreach ($servicos as $s) {
            $fetchPairsServicos[$s["SSER_ID_SERVICO"]] = $s["SSER_DS_SERVICO"];
        }
//        Zend_Debug::dump($fetchPairsServicos);
        return $fetchPairsServicos;
    }
}