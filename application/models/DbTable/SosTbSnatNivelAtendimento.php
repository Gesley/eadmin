<?php
class Application_Model_DbTable_SosTbSnatNivelAtendimento extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SNAT_NIVEL_ATENDIMENTO';
    protected $_primary = 'SNAT_ID_NIVEL';
    protected $_sequence = 'SOS_SQ_SNAT';
    
    
    /**
     * Troca o nível de atendimento de uma Solicitação
     * Recebe como parametros de entrada
     * @param $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
     * @param $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * @param $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = ;
     * @param $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = ;
     * 
     * @return void
     */
    public function trocanivelSolicitacao($idDocmDocumento , array $dataMofaMoviFase, array $dataSnasNivelAtendSolic, $nrDocsRed = null, $acompanhar = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $Dual = new Application_Model_DbTable_Dual(); 
            $datahora = $Dual->sysdate();
            Zend_Debug::dump($data);
            
            /*----------------------------------------------------------------------------------------*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();
            
            Zend_Debug::dump($dataMofaMoviFase);
//            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = ;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            
            
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1005; /*TROCA DE NÍVEL DE ATENDIMENTO SOLICITACAO TI*/
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
//            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = '';
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();
            Zend_Debug::dump($idMoviMovimentacao);
        
            /*----------------------------------------------------------------------------------------*/
            /*----------------------------------------------------------------------------------------*/
            $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
            //$dataSnasNivelAtendSolic=  $SosTbSnasNivelAtendSolic->fetchNew()->toArray();

            Zend_Debug::dump($dataSnasNivelAtendSolic);
            $dataSnasNivelAtendSolic["SNAS_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataSnasNivelAtendSolic["SNAS_DH_FASE"] = $datahora;
//            $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = ;
            $dataSnasNivelAtendSolic["SNAS_ID_DOCUMENTO"] = $idDocmDocumento;
            Zend_Debug::dump($dataSnasNivelAtendSolic);

            $rowSnasNivelAtendSolic =  $SosTbSnasNivelAtendSolic->createRow($dataSnasNivelAtendSolic);
            $rowSnasNivelAtendSolic->save();
            /*----------------------------------------------------------------------------------------*/
            
            /*Retira do atendente*/
             /*----------------------------------------------------------------------------------------*/
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
            $rowSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();
            $rowSolicitacao->setFromArray($dataSsolSolicitacao);
            $rowSolicitacao->save();
             /*----------------------------------------------------------------------------------------*/
            
            //Ultima Fase do lançada na Solicitação.//
            /*----------------------------------------------------------------------------------------*/
             $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();;
            $rowUltima_fase->setFromArray($dataUltima_fase);
            Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();
            /*----------------------------------------------------------------------------------------*/
            
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
             /*----------------------------------------------------------------------------------------*/
            $db->commit();
             
            /*----------------------ACOMPANHAMENTO DE BAIXA DA SOLICITAÇÃO NA TROCA DE NIVEL   ---------*/
            if ($acompanhar == "S") {
                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                $tabelaPapd->addAcompanhanteSostiCaixaAtendimento($idDocmDocumento);
            }
        
            $retorno['ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            $retorno['DATA_HORA'] = $datahora;
            return $retorno;
        
        } catch (Exception $exc) {
              $db->rollBack();
              throw $exc;
        }
    }
    
    public function getMaiorIndicador($grupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MAX(SNAT_CD_NIVEL) MAIOR
                            FROM   SOS_TB_SNAT_NIVEL_ATENDIMENTO
                            WHERE  SNAT_ID_GRUPO = $grupo");
        $result = $stmt->fetchAll();
        return $result[0]['MAIOR'];
    }
    
    public function getNiveis($order)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SNAT.SNAT_ID_NIVEL, SNAT.SNAT_ID_GRUPO, SNAT.SNAT_CD_NIVEL,
                                   SNAT.SNAT_DS_NIVEL, SNAT.SNAT_SG_NIVEL, SNAT.SNAT_PZ_ATENDIMENTO,
                                   SGRS.SGRS_ID_GRUPO, SGRS.SGRS_DS_GRUPO, 
                                       RH_LOTA.LOTA_SIGLA_SECAO, RH_LOTA.LOTA_COD_LOTACAO, RH_LOTA.LOTA_DSC_LOTACAO, RH_LOTA.LOTA_SIGLA_LOTACAO
                              FROM SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                              INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                              ON SNAT.SNAT_ID_GRUPO = SGRS.SGRS_ID_GRUPO
                              INNER JOIN RH_CENTRAL_LOTACAO RH_LOTA
                              ON RH_LOTA.LOTA_SIGLA_SECAO = SGRS_SG_SECAO_LOTACAO
                              AND RH_LOTA.LOTA_COD_LOTACAO =  SGRS_CD_LOTACAO
                              ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getNiveisPorGrupo($idGrupo, $order)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SNAT.SNAT_ID_NIVEL, SNAT.SNAT_ID_GRUPO, SNAT.SNAT_CD_NIVEL,
                                   SNAT.SNAT_DS_NIVEL, SNAT.SNAT_SG_NIVEL, SNAT.SNAT_PZ_ATENDIMENTO,
                                   SGRS.SGRS_ID_GRUPO, SGRS.SGRS_DS_GRUPO, 
                                       RH_LOTA.LOTA_SIGLA_SECAO, RH_LOTA.LOTA_COD_LOTACAO, RH_LOTA.LOTA_DSC_LOTACAO, RH_LOTA.LOTA_SIGLA_LOTACAO
                              FROM SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                              INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                              ON SNAT.SNAT_ID_GRUPO = SGRS.SGRS_ID_GRUPO
                              INNER JOIN RH_CENTRAL_LOTACAO RH_LOTA
                              ON RH_LOTA.LOTA_SIGLA_SECAO = SGRS_SG_SECAO_LOTACAO
                              AND RH_LOTA.LOTA_COD_LOTACAO =  SGRS_CD_LOTACAO
                              WHERE SGRS.SGRS_ID_GRUPO = $idGrupo
                              ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getNiveisPorServico($idServico)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SNAT.SNAT_ID_NIVEL, SNAT.SNAT_ID_GRUPO, SNAT.SNAT_CD_NIVEL,
                                     SNAT.SNAT_DS_NIVEL, SNAT.SNAT_SG_NIVEL, SNAT.SNAT_PZ_ATENDIMENTO
                              FROM SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                              INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                              ON SNAT.SNAT_ID_GRUPO = SGRS.SGRS_ID_GRUPO
                              INNER JOIN SOS_TB_SSER_SERVICO SSER
                              ON  SGRS.SGRS_ID_GRUPO = SSER.SSER_ID_GRUPO
                              WHERE  SSER.SSER_ID_SERVICO = $idServico");
        return $stmt->fetchAll();
    }
    
    public function getNiveisGrupoServicoAtendimentoUsuPorLotacao($sgsecao, $codlotacao)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        try {
        /**
         * Obtendo a lotacao pai tribunal ou seção.
         */
        $stmt = $db->query(" SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO A
                                        WHERE   LOTA_SIGLA_SECAO   = '$sgsecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_LOTA_COD_LOTACAO_PAI = LOTA_COD_LOTACAO
                                START WITH LOTA_COD_LOTACAO = $codlotacao
                              )
                              WHERE LOTA_TIPO_LOTACAO IN(9,1)/*LOTAÇÃO PAI SEÇÃO OU TRIBUNAL*/");
        $lotaPai =  $stmt->fetch();
        //Zend_Debug::dump($lotaPai);
        /**
         * Tratamento para não trazer as lotações subseções filhas das seções caso seja uma seção a lotação pai  
         */
        $stmt = $db->query(" SELECT CASE WHEN RHLOTA.LOTA_TIPO_LOTACAO = 1 /*SECAO*/ THEN 2 /*SUBSECAO*/
                                           ELSE -1
                                           END AS LOTA_TIPO_LOTACAO
                                    FROM RH_CENTRAL_LOTACAO RHLOTA
                                    WHERE RHLOTA.LOTA_SIGLA_SECAO = '{$lotaPai["LOTA_SIGLA_SECAO"]}'
                                    AND   RHLOTA.LOTA_COD_LOTACAO = {$lotaPai["LOTA_COD_LOTACAO"]} 
                                    AND  RHLOTA.LOTA_DAT_FIM IS NULL ");
        $nao_do_tipo =  $stmt->fetch();
        //Zend_Debug::dump($nao_do_tipo);
        /**
         * Obtendo o grupo de serviço que atende à Seção ou ao tribunal e o id da caixa junto com a lotação responsável pelo grupo de serviço
         */
        $stmt = $db->query("SELECT SNAT.SNAT_ID_NIVEL, SNAT.SNAT_ID_GRUPO, SNAT.SNAT_CD_NIVEL,
                                SNAT.SNAT_DS_NIVEL, SNAT.SNAT_SG_NIVEL, SNAT.SNAT_PZ_ATENDIMENTO
                                    FROM SAD_TB_TPCX_TIPO_CAIXA TPCX
                                    INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
                                    ON TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                    INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
                                    ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                    INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                    ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                    INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                                    ON SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                    INNER JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
  									ON SNAT.SNAT_ID_GRUPO = SGRS.SGRS_ID_GRUPO
                                    AND   SGRS.SGRS_CD_LOTACAO       = RHLOTA.LOTA_COD_LOTACAO
                                    WHERE TPCX.TPCX_ID_TIPO_CAIXA = 1 /*ATENDIMENTO AOS USUÁRIOS*/
                                    AND   (SGRS.SGRS_SG_SECAO_LOTACAO,SGRS.SGRS_CD_LOTACAO) IN
                                    (	
                                                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                                                      FROM    
                                                      (
                                                        SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
                                                        FROM (                           
                                                                SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                                                                FROM RH_CENTRAL_LOTACAO
                                                                WHERE   LOTA_SIGLA_SECAO   = '{$lotaPai["LOTA_SIGLA_SECAO"]}'
                                                                AND  LOTA_DAT_FIM IS NULL
                                                            )
                                                        CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
                                                        /*TRATAMENTO PARA O CASO DE SER UMA SUBSECAO*/
                                                        AND LOTA_TIPO_LOTACAO NOT IN ({$nao_do_tipo["LOTA_TIPO_LOTACAO"]})
                                                        START WITH LOTA_COD_LOTACAO = {$lotaPai["LOTA_COD_LOTACAO"]}
                                                    )
                                            )
                                ORDER BY SNAT.SNAT_CD_NIVEL ASC");
        $grupoServico = $stmt->fetchAll();
        $db->rollBack();
        
        return  $grupoServico;
        
        } catch (Exception $exc) {
            $db->rollBack();
        }
    }
    
}