<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSserServico extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SSER_SERVICO';
    protected $_primary = 'SSER_ID_SERVICO';
    protected $_sequence = 'SOS_SQ_SSER';

    public function getServico($order)
    {
        if ( !isset($order) ) {
            $order = 'SGRS_DS_GRUPO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT S.SSER_ID_SERVICO, S.SSER_DS_SERVICO, S.SSER_ID_GRUPO, G.SGRS_DS_GRUPO,
                                   /*DECODE(S.SSER_IC_ATIVO, 'S', 'Sim', 'N', 'Não ')*/ SSER_IC_ATIVO,
                                   /*DECODE(S.SSER_IC_VISIVEL, 'S', 'Sim', 'N', 'Não ')*/ SSER_IC_VISIVEL,
                                   /*DECODE(S.SSER_IC_TOMBO, 'S', 'Sim', 'N', 'Não ')*/ SSER_IC_TOMBO,
                                   /*DECODE(S.SSER_IC_ANEXO, 'S', 'Sim', 'N', 'Não ')*/ SSER_IC_ANEXO,
                                   /*DECODE(S.SSER_IC_VIDEOCONFERENCIA, 'S', 'Sim', 'N', 'Não ')*/ SSER_IC_VIDEOCONFERENCIA
                            FROM   SOS_TB_SSER_SERVICO S
                            INNER  JOIN SOS_TB_SGRS_GRUPO_SERVICO G
                            ON     S.SSER_ID_GRUPO = G.SGRS_ID_GRUPO
                            WHERE  S.SSER_IC_ATIVO = 'S'
                            AND    S.SSER_IC_VISIVEL = 'S'
                            ORDER  BY $order");
        return $stmt->fetchAll();
    }
    
    public function getServicoPorGrupo($idgrupo,$order)
    {
        if ( !isset($order) ) {
            $order = 'SGRS_DS_GRUPO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT S.SSER_ID_SERVICO, 
                        S.SSER_DS_SERVICO, 
                        S.SSER_ID_GRUPO, 
                        S.SSER_IC_ATIVO,
                        S.SSER_IC_VISIVEL,
                        S.SSER_IC_TOMBO,
                        S.SSER_IC_ANEXO,
                        S.SSER_IC_VIDEOCONFERENCIA,
                        G.SGRS_DS_GRUPO
                FROM   SOS_TB_SSER_SERVICO S
                INNER  JOIN SOS_TB_SGRS_GRUPO_SERVICO G
                ON     S.SSER_ID_GRUPO = G.SGRS_ID_GRUPO
                WHERE  SSER_IC_ATIVO = 'S'AND SSER_IC_VISIVEL = 'S'";
          $stmt .= ($idgrupo)?(" AND G.SGRS_ID_GRUPO = ".$idgrupo." "):('');
          $stmt .=" ORDER BY $order";
	  $rows = $db->query($stmt); 
          return $rows->fetchAll();
    }
    
    // Selecionando os serviços que estao inativos
    public function getServicoInativoPorGrupo($idgrupo,$order)
    {
        if ( !isset($order) ) {
            $order = 'SGRS_DS_GRUPO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT S.SSER_ID_SERVICO, 
                        S.SSER_DS_SERVICO, 
                        S.SSER_ID_GRUPO, 
                        S.SSER_IC_ATIVO,
                        S.SSER_IC_VISIVEL,
                        S.SSER_IC_TOMBO,
                        S.SSER_IC_ANEXO,
                        S.SSER_IC_VIDEOCONFERENCIA,
                        G.SGRS_DS_GRUPO
                FROM   SOS_TB_SSER_SERVICO S
                INNER  JOIN SOS_TB_SGRS_GRUPO_SERVICO G
                ON     S.SSER_ID_GRUPO = G.SGRS_ID_GRUPO
                WHERE  SSER_IC_ATIVO = 'N' OR SSER_IC_VISIVEL = 'N'";
          $stmt .= ($idgrupo)?(" AND G.SGRS_ID_GRUPO = ".$idgrupo." "):('');
          $stmt .=" ORDER BY $order";
	  $rows = $db->query($stmt); 
          return $rows->fetchAll();
    }
    
    public function getServicoPorIdCaixaAtendimento($idCaixa,$order)
    {
        if ( !isset($order) ) {
            $order = 'SGRS_DS_GRUPO ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SSER.SSER_ID_SERVICO, SSER.SSER_DS_SERVICO, SSER.SSER_ID_GRUPO, SGRS.SGRS_DS_GRUPO,
                            /*DECODE(S.SSER_IC_ATIVO, 'S', 'Sim', 'N', 'Não ')*/ SSER.SSER_IC_ATIVO,
                            /*DECODE(S.SSER_IC_VISIVEL, 'S', 'Sim', 'N', 'Não ')*/ SSER.SSER_IC_VISIVEL,
                            /*DECODE(S.SSER_IC_TOMBO, 'S', 'Sim', 'N', 'Não ')*/ SSER.SSER_IC_TOMBO,
                            /*DECODE(S.SSER_IC_ANEXO, 'S', 'Sim', 'N', 'Não ')*/ SSER.SSER_IC_ANEXO
                            FROM   SOS_TB_SSER_SERVICO SSER
                            INNER  JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            ON     SSER.SSER_ID_GRUPO = SGRS.SGRS_ID_GRUPO
                            INNER  JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                            ON     CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                            INNER  JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                            ON     CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                            WHERE  CXEN_ID_CAIXA_ENTRADA = $idCaixa
                            AND SSER_IC_ATIVO = 'S'
                            AND SSER_IC_VISIVEL = 'S'
                            ORDER  BY $order");
        return $stmt->fetchAll();
    }
    
    
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
    public function setTrocaServicoSolicitacao($idDocmDocumento,$dataSsolSolicitacao, array $dataMofaMoviFase, array $dataSsesServicoSolic, $nrDocsRed = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $dual = new Application_Model_DbTable_Dual();
            $datahora = $dual->sysdate();

            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1008; /*TROCA DE SERVIÇO SOLICITAÇÃO TI*/
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr( " CAST( '". $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] ."' AS VARCHAR(4000)) " );

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();


            $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
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
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
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
             $db->commit();
        
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
        }
        return $datahora;
    }
    
    public function getGrupoPorId($idGrupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SSER_ID_GRUPO FROM SOS_TB_SSER_SERVICO
                            WHERE SSER_ID_SERVICO = $idGrupo");
        $retorno = $stmt->fetchAll();
        return $retorno[0]["SSER_ID_GRUPO"];
    }
    
    /*
     * Retorna os serviços de acordo com a sigla da Seçao Judiciaria ou TRF
     * Recebe como parametros de entrada
     * @param $siglasecao string
     * @param $order string
     * 
     * @return array com os servicos
     */
    public function getServicoPorLotacao($siglasecao, $order)
    {
        if ( !isset($order) ) {
            $order = 'SGRS_DS_GRUPO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT S.SSER_ID_SERVICO, S.SSER_DS_SERVICO, S.SSER_ID_GRUPO, G.SGRS_DS_GRUPO,
                                  SSER_IC_ATIVO, SSER_IC_VISIVEL, SSER_IC_TOMBO, SSER_IC_ANEXO
                          FROM   SOS_TB_SSER_SERVICO S
                          INNER  JOIN SOS_TB_SGRS_GRUPO_SERVICO G
                          ON     S.SSER_ID_GRUPO = G.SGRS_ID_GRUPO
                          INNER JOIN RH_CENTRAL_LOTACAO L
                          ON     L.LOTA_SIGLA_SECAO = G.SGRS_SG_SECAO_LOTACAO
                          AND    L.LOTA_COD_LOTACAO = G.SGRS_CD_LOTACAO  
                          AND    L.LOTA_SIGLA_SECAO = '$siglasecao'
                          AND    S.SSER_IC_VISIVEL = 'S'
                          AND    S.SSER_IC_ATIVO = 'S'
                          ORDER  BY $order");
        return $stmt->fetchAll();
    }

}