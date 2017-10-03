<?php
class Application_Model_DbTable_SadTbDocmDocumento extends Zend_Db_Table_Abstract
{
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_DOCM_DOCUMENTO';
    protected $_primary = array('DOCM_ID_DOCUMENTO');
    protected $_sequence = 'SAD_SQ_DOCM';
    public $sigilo;
    public $extencao;

    public function getSituacaoDoc()
    {
        $sigilo = array('0' => 'Público', '1' => 'Restrito as Partes', '2' => 'Restrito a Intranet', '3' => 'Segredo de Justica', '4' => 'Sigiloso');
        return $sigilo;
    }
    
    public function getExtencaoArquivo()
    {
        $extencao = array('doc', 'pdf', 'docx', 'avi', 'jpg', 'jpeg', 'mpeg', 'xls', 'xlsx', 'txt', 'rtf', 'odt');
        return $extencao;
    }

    public function getNumeroSequencialDCMTO($codSecaoReda,$codDocUnidReda,$codDocTipoDoc)
    {
        $codDocUnidReda = (int)$codDocUnidReda;
        $codDocTipoDoc  = (int)$codDocTipoDoc;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NVL(MAX(DOCM_NR_SEQUENCIAL_DOC)+1,1) AS DOCM_NR_SEQUENCIAL_DOC
                              FROM SAD_TB_DOCM_DOCUMENTO A
                            WHERE A.DOCM_ID_TIPO_DOC = $codDocTipoDoc
                               AND A.DOCM_SG_SECAO_REDATORA = '$codSecaoReda'
                               AND A.DOCM_CD_LOTACAO_REDATORA = $codDocUnidReda
			       AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = TO_CHAR(SYSDATE, 'YYYY')");

        $docm_nr_sequencial_doc =  $stmt->fetchAll();
        
        return $docm_nr_sequencial_doc[0]['DOCM_NR_SEQUENCIAL_DOC'];
    }
    
    public function getCodSecSubsec_PaiSecao($sgSecao,$codLotacao)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT SESB_SESU_CD_SECSUBSEC /*CODIGO SEÇÃO DA LOTAÇÃO PAI SEÇAÕ OU TRIBUNAL*/
                             FROM RH_CENTRAL_SECAO_SUBSECAO
                             WHERE (SESB_SIGLA_CENTRAL,SESB_LOTA_COD_LOTACAO) IN
                             (
                              SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                              FROM    
                              (
                                SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO            
                                FROM (                           
                                        SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO A
                                        WHERE   LOTA_SIGLA_SECAO   = '$sgSecao'
                                        AND  LOTA_DAT_FIM IS NULL
                                    )
                                CONNECT BY PRIOR LOTA_LOTA_COD_LOTACAO_PAI = LOTA_COD_LOTACAO
                                START WITH LOTA_COD_LOTACAO = $codLotacao
                              )
                              WHERE LOTA_TIPO_LOTACAO IN(9,1)/*LOTAÇÃO PAI SEÇÃO OU TRIBUNAL*/
                              )");

        $sesb_sesu_cd_secsubsec =  $stmt->fetch();
        
        return $sesb_sesu_cd_secsubsec["SESB_SESU_CD_SECSUBSEC"];
    }

    public function getNumeroDCMTO($sgSecao,$codDocUnidReda,$codDocUnidGera,$codDocTipoDoc,$NumeroSequencialDCMTO)
    {
        /*
         * Fórmula do Número do documento
         * ano(4)unidadeRedatora(4)unidadeGeradora(4)TipodoDocumento(3)Númerosequenciadocumento(5)
         * 
         */
        $codSecsubsec = $this->getCodSecSubsec_PaiSecao($sgSecao, $codDocUnidReda);
        
        $codDocAno = date('Y');
        $codSecsubsec = substr(sprintf('%04d', $codSecsubsec),0,4);
        $codDocUnidReda = substr(sprintf('%05d', $codDocUnidReda),0,5);
        $codDocUnidGera = substr(sprintf('%05d', $codDocUnidGera),0,5);
        $codDocTipoDoc = substr(sprintf('%04d', $codDocTipoDoc),0,4);
        $NumeroSequencialDCMTO = substr(sprintf('%06d', $NumeroSequencialDCMTO),0,6);

        $NumeroDCMTO = $codDocAno.$codSecsubsec.$codDocUnidReda.$codDocUnidGera.$codDocTipoDoc.$NumeroSequencialDCMTO;

        if(strlen($NumeroDCMTO) == 28){
            return $NumeroDCMTO;
        }else{
            return new Zend_Db_Expr("NULL");
        }
    }
    
    public function getUltimoTelefoneCadastrado($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.SSOL_DS_EMAIL_EXTERNO,A.SSOL_NR_TELEFONE_EXTERNO
                            FROM SOS_TB_SSOL_SOLICITACAO A,
                                 SAD_TB_DOCM_DOCUMENTO B
                            WHERE A.SSOL_ID_DOCUMENTO = B.DOCM_ID_DOCUMENTO
                            AND B.DOCM_DH_CADASTRO = (SELECT MAX(TE.DOCM_DH_CADASTRO)
                                                      FROM SAD_TB_DOCM_DOCUMENTO TE
                                                      WHERE TE.DOCM_CD_MATRICULA_CADASTRO = '".$matricula."')");
        return $stmt->fetchAll();
    }
    
     /**
     * Recebe como parametros de entrada
     * @param array $dataMofaMoviFase ('MOFA_ID_MOVIMENTACAO'=>'', 'MOFA_ID_FASE'=>'', 'MOFA_CD_MATRICULA'=>'', 'MOFA_DS_COMPLEMENTO'=>'')
     * @return void
     */
    public function parecerDocumento(array $dataMofaMoviFase,
                                     $nrDocsRed = null,
                                     $extensao = 1,
                                     $autoCommit = true)
    {
        /**
         * Parecer Documento
         * Com ou sem troca de nível.
         */
        if ($autoCommit) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            //Zend_Debug::dump($data);
            
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();
            
            //Zend_Debug::dump($dataMofaMoviFase);
//            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = INFORMAR;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
//            $dataMofaMoviFase["MOFA_ID_FASE"] = INFORMAR;
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = INFORMAR;
//            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'INFORMAR';
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            //Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $nrDocsRed["ID_DOCUMENTO"];
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $nrDocsRed["ID_MOVIMENTACAO"];
            if($nrDocsRed["incluidos"]){
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo =  $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            if ($autoCommit) {
                $db->commit();
            }
             return true;
        
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            //echo $exc->getMessage();
            return false;
        }
    }
    
    public function incluirArquivoMinuta(array $dataMofaMoviFase,
                                         $nrDocsRed = null)
    {
       
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            //Zend_Debug::dump($data);
            
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            //Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $nrDocsRed["ID_DOCUMENTO"];
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $nrDocsRed["ID_MOVIMENTACAO"];
            
            if($nrDocsRed["incluidos"]){
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                $cont = 0;
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];    
                    $rowAnexAnexo =  $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                    $cont++;
                }
            }
             $db->commit();
             return true;
        
        } catch (Exception $exc) {
            $db->rollBack();
            //echo $exc->getMessage();
            return false;
        }
    }
    
    public function solicitarDocumento($mofa_id_movimentacao, $matricula, $mensagem)
    {
        /**
         * Solicitar Documento na pesquisa de documentos
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            
            /*----------------------------------------------------------------------------------------*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $mofa_id_movimentacao;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1051;/*SOLICITAÇÃO DE DOCUMENTO/PROCESSO PARA ANÁLISE*/
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $mensagem . "'");
           
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            
             $db->commit();
             return true;
        
        } catch (Exception $exc) {
            $db->rollBack();
            return $exc->getMessage();
        }
    }
     
    /**
     * Recebe como parametros de entrada
     * @param array $dataMofaMoviFase ('MOFA_ID_MOVIMENTACAO'=>'', 'MOFA_ID_FASE'=>'', 'MOFA_CD_MATRICULA'=>'', 'MOFA_DS_COMPLEMENTO'=>'')
     * @return void
     */
    public function assinarDocumento(array $dataMofaMoviFase)
    {
        /**
         * Parecer Documento
         * Com ou sem troca de nível.
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            //Zend_Debug::dump($data);
            
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();
            
            //Zend_Debug::dump($dataMofaMoviFase);
//            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = INFORMAR;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
//            $dataMofaMoviFase["MOFA_ID_FASE"] = 1018; /*ASSINATURA SISAD*/
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = INFORMAR;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            //Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/

             $db->commit();
             return true;
        
        } catch (Exception $exc) {
            $db->rollBack();
            //echo $exc->getMessage();
            return false;
        }
    }
    
    public function cancelarDocumento(array $dataMofaMoviFase)
    {
        /**
         * Cancelar Documento
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            $dataMofaMoviFase_aux = $dataMofaMoviFase;
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            $dataMofaMoviFase = array();

//            Zend_Debug::dump($dataMofaMoviFase_aux);
//            exit;
            
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dataMofaMoviFase_aux['MOFA_ID_MOVIMENTACAO'];
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1004; /*CANCELAMENTO DE DOCUMENTO OU PROCESSO*/
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $dataMofaMoviFase_aux['MATRICULA_CAIXA_PESSOAL'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $dataMofaMoviFase_aux['MOFA_DS_COMPLEMENTO'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*-----------------------------------------------------------------------------------------*/
            
            /*-----------------------------------------------------------------------------------------*/
            /*segunda tabela*/
            $dataMofaMoviFase_aux["DOCM_IC_ATIVO"] = "N";
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $rowDocmDocumento_cancelar = $SadTbDocmDocumento->find($dataMofaMoviFase_aux["DOCM_ID_DOCUMENTO"])->current();;
            $rowDocmDocumento_cancelar->setFromArray($dataMofaMoviFase_aux);
            $rowDocmDocumento_cancelar->save();
            
            /*----------------------------------------------------------------------------------------*/

             $db->commit();
             return true;
        
        } catch (Exception $exc) {
            $db->rollBack();
            return false;
        }
    }
    
    public function arquivarDocumento(array $dataMofaMoviFase,$autoCommit = true)
    {
        /**
         * Arquivar Documento
         */
        if($autoCommit){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        }
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            $dataMofaMoviFase_aux = $dataMofaMoviFase;
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase = array();

//            Zend_Debug::dump($dataMofaMoviFase_aux);
//            exit;
            
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dataMofaMoviFase_aux['MOFA_ID_MOVIMENTACAO'];
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1012; /*ARQUIVAMENTO SISAD*/
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $dataMofaMoviFase_aux['MATRICULA_CAIXA_PESSOAL'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . str_replace("'", "''", $dataMofaMoviFase_aux["MOFA_DS_COMPLEMENTO"]) . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*-----------------------------------------------------------------------------------------*/
            
            /*-----------------------------------------------------------------------------------------*/
            /*segunda tabela*/
            $dadosAtualizardocumento["DOCM_IC_ARQUIVAMENTO"] = "S";
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $rowDocmDocumento_cancelar = $SadTbDocmDocumento->find($dataMofaMoviFase_aux["DOCM_ID_DOCUMENTO"])->current();;
            $rowDocmDocumento_cancelar->setFromArray($dadosAtualizardocumento);
            $rowDocmDocumento_cancelar->save();
            
            /*----------------------------------------------------------------------------------------*/
            if ($autoCommit) {
             $db->commit();
            }
             return true;
        
        } catch (Exception $exc) {
            if($autoCommit){
            $db->rollBack();
            }
            return false;
        }
    }
    
    public function desarquivarDocumentoPessoal(array $dataMofaMoviFase,$autoCommit = true)
    {
        /**
         * Desarquivar Documento
         */
        if($autoCommit){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            $dataMofaMoviFase_aux = $dataMofaMoviFase;
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase = array();

//            Zend_Debug::dump($dataMofaMoviFase_aux);
//            exit;
            
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dataMofaMoviFase_aux['MOFA_ID_MOVIMENTACAO'];
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1021; /*DESARQUIVAMENTO DE DOCUMENTO/PROCESSO DIGITAL*/
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $dataMofaMoviFase_aux['MATRICULA_CAIXA_PESSOAL'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $dataMofaMoviFase_aux['MOFA_DS_COMPLEMENTO'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*-----------------------------------------------------------------------------------------*/
            
            /*-----------------------------------------------------------------------------------------*/
            /*segunda tabela*/
            $dataAtualizardocumento["DOCM_IC_ARQUIVAMENTO"] = "N";
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $rowDocmDocumento_cancelar = $SadTbDocmDocumento->find($dataMofaMoviFase_aux["DOCM_ID_DOCUMENTO"])->current();;
            $rowDocmDocumento_cancelar->setFromArray($dataAtualizardocumento);
            $rowDocmDocumento_cancelar->save();
            
            /*----------------------------------------------------------------------------------------*/
            if ($autoCommit) {
                $db->commit();
            }
             return true;
        
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            return false;
        }
    }
    
    public function desarquivarDocumentoUnidade(array $dataMofaMoviFase)
    {
        /**
         * Desarquivar Documento da Unidade
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            $dataMofaMoviFase_aux = $dataMofaMoviFase;
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase = array();

//            Zend_Debug::dump($dataMofaMoviFase_aux);
//            exit;
            
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dataMofaMoviFase_aux['MOFA_ID_MOVIMENTACAO'];
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1021; /*DESARQUIVAMENTO DE DOCUMENTO/PROCESSO DIGITAL*/
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $dataMofaMoviFase_aux['MATRICULA_CAIXA_PESSOAL'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $dataMofaMoviFase_aux['MOFA_DS_COMPLEMENTO'];
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*-----------------------------------------------------------------------------------------*/
            
            /*-----------------------------------------------------------------------------------------*/
            /*segunda tabela*/
            $dataAtualizardocumento["DOCM_IC_ARQUIVAMENTO"] = "N";
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $rowDocmDocumento_cancelar = $SadTbDocmDocumento->find($dataMofaMoviFase_aux["DOCM_ID_DOCUMENTO"])->current();;
            $rowDocmDocumento_cancelar->setFromArray($dataAtualizardocumento);
            $rowDocmDocumento_cancelar->save();
            
            /*----------------------------------------------------------------------------------------*/

             $db->commit();
             return true;
        
        } catch (Exception $exc) {
            $db->rollBack();
            return false;
        }
    }
    
    public function getDadosDCMTO($idDocumento)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT 
                                   --TIPO DOCUMENTO
                                   DTPD.DTPD_NO_TIPO,
                
                                   --DOCUMENTOS
                                   DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                                   DOCM_CD_MATRICULA_CADASTRO,
                                   -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                   DOCM.DOCM_NR_DOCUMENTO_RED,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                                   DECODE(
                                            LENGTH( DOCM_NR_DOCUMENTO),
                                            14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                   ) MASC_NR_DOCUMENTO,
                
                                   --UNIDADE EMISSORA
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
                                   RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
                                   RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
                                   LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
                                   LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,
                
                                   --UNIDADE REDATORA
                                   RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
                                   RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
                                   LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
                                   LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,
                
                                   --SITUACAO DOCUMENTO
                                   TPSD.TPSD_DS_TIPO_SITUACAO_DOC,
                                   
                                   --CONFIDENCIALIDADE
                                   CONF.CONF_ID_CONFIDENCIALIDADE,
                                   CONF.CONF_DS_CONFIDENCIALIDADE,
                
                                   --ASSUNTO 
                                   AQVP.AQVP_ID_PCTT,
                                   AQVP.AQVP_CD_PCTT,  
                                   AQAT.AQAT_DS_ATIVIDADE,
                
                                   --MOVIMENTACAO
                                   TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
                                   TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                
                                   --FASE
                                    MOFA.MOFA_ID_MOVIMENTACAO,
                                   DOCM.DOCM_ID_TP_EXTENSAO,
                
                                   MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                   MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                   LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
                                   DOCM_ID_TP_EXTENSAO
                            FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                   ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                   ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                   ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                   ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
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
                                   ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
                                        LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
                                        LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
                                        LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
                                        FROM RH_CENTRAL_LOTACAO
                                    ) LOTA_P
                            WHERE  DOCM.DOCM_ID_DOCUMENTO = $idDocumento
                            AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
                            AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA
                            AND    MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)");
        return $stmt->fetch();
    }
    
    public function getDadosDCMTORascunho($idDocumento)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT 
                            --TIPO DOCUMENTO
                            DTPD.DTPD_NO_TIPO,

                            --DOCUMENTOS
                            DOCM.DOCM_ID_DOCUMENTO,
                            DOCM.DOCM_NR_DOCUMENTO,
                            DOCM.DOCM_NR_DCMTO_USUARIO,
                            DOCM_CD_MATRICULA_CADASTRO,
                            -- DOCM.DOCM_DS_ASSUNTO_DOC,
                            DOCM.DOCM_NR_DOCUMENTO_RED,
                            TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,

                            --UNIDADE EMISSORA
                            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
                            RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
                            RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
                            LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
                            LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,

                            --UNIDADE REDATORA
                            RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
                            RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
                            LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
                            LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,

                            --SITUACAO DOCUMENTO
                            TPSD.TPSD_DS_TIPO_SITUACAO_DOC,

                            --CONFIDENCIALIDADE
                            CONF.CONF_ID_CONFIDENCIALIDADE,
                            CONF.CONF_DS_CONFIDENCIALIDADE,

                            --ASSUNTO 
                            AQVP.AQVP_ID_PCTT,
                            AQVP.AQVP_CD_PCTT,  
                            AQAT.AQAT_DS_ATIVIDADE,
                            DOCM.DOCM_ID_TP_EXTENSAO



                            FROM   SAD_TB_DOCM_DOCUMENTO DOCM
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
                                ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
                                        LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
                                        LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
                                        LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
                                        FROM RH_CENTRAL_LOTACAO
                                    ) LOTA_P
                            WHERE  DOCM.DOCM_ID_DOCUMENTO = $idDocumento
                            AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
                            AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA");
        return $stmt->fetch();
    }
    
    public function getDadosProcesso($idProcesso)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD.DTPD_NO_TIPO,
                                   DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                
                                   RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
                                   RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
                                   LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
                                   LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,
                                   
                                   RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
                                   RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
                                   LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
                                   LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,
                
                
                                   DOCM_CD_MATRICULA_CADASTRO,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
                                   -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                                   TPSD.TPSD_DS_TIPO_SITUACAO_DOC,
                                   CONF.CONF_DS_CONFIDENCIALIDADE,
                                   DOCM.DOCM_NR_DOCUMENTO_RED,
                                   AQVP.AQVP_ID_PCTT,
                                   AQVP.AQVP_CD_PCTT,  
                                   AQAT.AQAT_DS_ATIVIDADE
                            FROM   SAD_TB_DOCM_DOCUMENTO DOCM
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
                                   INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                   ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                   ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                   ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
                                        LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
                                        LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
                                        LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
                                        FROM RH_CENTRAL_LOTACAO
                                    ) LOTA_P
                            WHERE  DCPR.DCPR_ID_PROCESSO_DIGITAL = $idProcesso
                            AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
                            AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA
                            AND    DOCM.DOCM_ID_TIPO_DOC = 152");
        return $stmt->fetch();
    }
    
    public function getHistoricoDCMTO($idDocumento)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO ,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MOFA.MOFA_DS_COMPLEMENTO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA.MOFA_CD_MATRICULA) MOFA_CD_MATRICULA_NOME,
                                       MOFA.MOFA_CD_MATRICULA,
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       FADM.FADM_ID_FASE,
                                       FADM.FADM_DS_FASE,
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       LOTA_1.LOTA_SIGLA_LOTACAO_1 LOTA_SIGLA_LOTACAO_ORIGEM,
                                       LOTA_1.LOTA_DSC_LOTACAO_1 LOTA_DSC_LOTACAO_ORIGEM,
                                       RH_SIGLAS_FAMILIA_CENTR_LOTA(MOVI.MOVI_SG_SECAO_UNID_ORIGEM,MOVI.MOVI_CD_SECAO_UNID_ORIGEM) FAMILIA_ORIGEM,
                                       MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
                                       MOVI.MOVI_CD_SECAO_UNID_ORIGEM,
                
                                       LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_DESTINO,
                                       LOTA.LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_DESTINO,
                                       RH_SIGLAS_FAMILIA_CENTR_LOTA(MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO) FAMILIA_DESTINO,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MODE_MOVI.MODE_CD_MATR_RECEBEDOR) RECEBEDOR,
                                       TO_CHAR(MODE_MOVI.MODE_DH_RECEBIMENTO ,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       --ANEX.ANEX_NR_DOCUMENTO_INTERNO NR_RED,
                                        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MODP.MODP_CD_MAT_PESSOA_DESTINO) MODP_CD_MAT_PESSOA_DESTINO,
                                        MODP_CD_MAT_PESSOA_DESTINO,
                                        MOFA_ID_MOVIMENTACAO,
                                       DOCM.DOCM_ID_TP_EXTENSAO
                                       --ANEX.ANEX_ID_TP_EXTENSAO
                
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       --LEFT JOIN SAD_TB_ANEX_ANEXO ANEX
                                       --ON ANEX.ANEX_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO 
                                       --AND    ANEX.ANEX_DH_FASE = MOFA.MOFA_DH_FASE
                                       --AND    ANEX.ANEX_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       INNER JOIN SAD_TB_FADM_FASE_ADM FADM
                                       ON FADM.FADM_ID_FASE = MOFA.MOFA_ID_FASE
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                                        ,
                                       ( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_1,
                                            LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_1, 
                                            LOTA_COD_LOTACAO LOTA_COD_LOTACAO_1,
                                            LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_1
                                            FROM RH_CENTRAL_LOTACAO
                                       ) LOTA_1                                       
                                       WHERE  DOCM.DOCM_ID_DOCUMENTO = $idDocumento
                                       AND    MOVI.MOVI_SG_SECAO_UNID_ORIGEM = LOTA_1.LOTA_SIGLA_SECAO_1
                                       AND    MOVI.MOVI_CD_SECAO_UNID_ORIGEM = LOTA_1.LOTA_COD_LOTACAO_1
                                       ORDER BY MOFA.MOFA_DH_FASE DESC");
        return $stmt->fetchAll();
    }
    
    public function getDadosDocumentoByNrDoc($numeroDoc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM_ID_DOCUMENTO FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_NR_DOCUMENTO_RED = $numeroDoc");
        return $stmt->fetchAll();
    }
  public function getDocumentoIdByNrDoc($numeroDoc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM_ID_DOCUMENTO FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_NR_DOCUMENTO = $numeroDoc");
        return $stmt->fetchAll();
    }
    
    
    public function getPesquisaDocumento($params,$order)
    {
        
//--- PARTES DOS PROCESSO - INICIO        
 // PESSOA TRF/SEÇÕES
        foreach ($params['partes_pessoa_trf'] as $value) {
            $partes_pessoa_trf = explode("-",$value);
            $partes_pessoa_trf_id[] = $partes_pessoa_trf[1];
        }
        $partes_pessoa_trf_id = implode(",",$partes_pessoa_trf_id);
        $params['partes_pessoa_trf'] = $partes_pessoa_trf_id;
 // PESSOA FÍSICA EXTERNA
        foreach ($params['partes_pess_ext'] as $value) {
            $partes_pessoa_ext_id[] = $value;
        }
        $partes_pessoa_ext_id = implode(",",$partes_pessoa_ext_id);   
        $params['partes_pess_ext'] =  $partes_pessoa_ext_id;
 // PESSOA JURIDICA
        foreach ($params['partes_pess_jur'] as $value) {
            $partes_pessoa_jur = explode("-",$value);
            $partes_pess_jur_id[] = $partes_pessoa_jur[0];
        }
        $partes_pess_jur_id = implode(",",$partes_pess_jur_id);   
        $params['partes_pess_jur'] =  $partes_pess_jur_id;
 // PESSOA JURIDICA
        foreach ($params['partes_unidade'] as $value) {
            $partes_unidade = explode("-",$value);
            $partes_unidade_id[] = "'".$partes_unidade[0].$partes_unidade[1]."'";
        }
        $partes_unidade_id = implode(",",$partes_unidade_id);   
        $params['partes_unidade'] =  $partes_unidade_id; 
        
//        Zend_Debug::dump($partes_pessoa_trf_id);
//        Zend_Debug::dump($partes_pessoa_ext_id);
//        Zend_Debug::dump($partes_pess_jur_id);
//        Zend_Debug::dump($partes_unidade_id);
//--- PARTES DOS PROCESSO - FIM    
//        Zend_Debug::dump($params);
//        exit;
        $mat = explode(' - ', $params["DOCM_CD_MATRICULA_CADASTRO"]);
        $geradora = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
        $redatora = explode(' - ', $params["DOCM_CD_LOTACAO_REDATORA"]);
        $secao = explode('|', $params["TRF1_SECAO_1"]);
        $docm_cd_matricula_cadastro = $mat[0];
        $docm_cd_lotacao_geradora = $geradora[3].$geradora[2];
        $docm_cd_lotacao_redatora = $redatora[3].$redatora[2];
        $sigla_sg_secao = $secao[0];
        $docm_id_tipo_doc = $params["DOCM_ID_TIPO_DOC"];
        $docm_nr_dcmto_usuario = $params["DOCM_NR_DCMTO_USUARIO"];
        $docm_nr_documento = $params["DOCM_ID_DOCUMENTO"];
        $docm_id_pctt = $params["DOCM_ID_PCTT"];
        $docm_ds_palavra_chave = $params["DOCM_DS_PALAVRA_CHAVE"];
        $docm_id_tipo_situacao_doc = $params["DOCM_ID_TIPO_SITUACAO_DOC"];
        //$docm_id_confidencialidade = $params["DOCM_ID_CONFIDENCIALIDADE"];
        $docm_id_confidencialidade = 0;
        $data_inicial = $params['DATA_INICIAL'];
        $data_final = $params['DATA_FINAL'];
        //Zend_Debug::dump($docm_id_confidencialidade); exit;
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT DISTINCT DOCM.DOCM_ID_DOCUMENTO,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       DOCM_DH_CADASTRO,
                                       -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                       DOCM_CD_MATRICULA_CADASTRO,
                                       DOCM_IC_ARQUIVAMENTO,
                                       DOCM_IC_ATIVO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       LOTA.LOTA_SIGLA_LOTACAO, 
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                               WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               --AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               --AND  DOCM_IC_ATIVO = 'S'
                               --AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N'
                               --AND DOCM_ID_CONFIDENCIALIDADE = 0
                               AND DOCM.DOCM_ID_TIPO_DOC <> 230 --MINUTAS
                               ";
        
            $q .= ($docm_cd_matricula_cadastro)?(" AND DOCM_CD_MATRICULA_CADASTRO = '$docm_cd_matricula_cadastro'"):('');
            $q .= ($sigla_sg_secao)?(" AND DOCM_SG_SECAO_REDATORA = '$sigla_sg_secao'"):("");
                $q .= ($docm_nr_documento)?(" AND DOCM_NR_DOCUMENTO = $docm_nr_documento"):("");
            $q .= ($docm_cd_lotacao_geradora)?(" AND DOCM.DOCM_SG_SECAO_GERADORA||DOCM.DOCM_CD_LOTACAO_GERADORA = '$docm_cd_lotacao_geradora'"):("");
            $q .= ($docm_cd_lotacao_redatora)?(" AND DOCM.DOCM_SG_SECAO_REDATORA||DOCM.DOCM_CD_LOTACAO_REDATORA = '$docm_cd_lotacao_redatora'"):("");
            $q .= ($docm_id_tipo_doc)?(" AND DOCM_ID_TIPO_DOC = '$docm_id_tipo_doc'"):("");
            $q .= ($docm_nr_dcmto_usuario)?(" AND DOCM_NR_DCMTO_USUARIO = '$docm_nr_dcmto_usuario'"):("");
            $q .= ($docm_id_pctt)?(" AND DOCM_ID_PCTT = '$docm_id_pctt'"):("");
            
            
            if ($params["DOCM_DS_PALAVRA_CHAVE"]) {
                $docm_ds_palavra_chave = explode(',', $params["DOCM_DS_PALAVRA_CHAVE"]);
                foreach($docm_ds_palavra_chave as $chave){
                    $q .= "AND UPPER (DOCM_DS_PALAVRA_CHAVE) LIKE UPPER('%$chave%')";
                }
            }
            
            /**
             *Pesquisa por partes e interressados 
             */
            $q .= ($params['partes_pessoa_trf'])?(" 
                                                    AND DOCM_ID_DOCUMENTO IN 
                                                    (

                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pessoa_trf'] . ")
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pessoa_trf'] . ")
                                                    
                                                    )
                                               "):("");
            $q .= ($params['partes_pess_ext'])?(" 
                                                    AND DOCM_ID_DOCUMENTO IN 
                                                    (

                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pess_ext'] . ")
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pess_ext'] . ")
                                                    
                                                    )
                                               "):(""); 
            $q .= ($params['partes_pess_jur'])?(" 
                                                    AND DOCM_ID_DOCUMENTO IN 
                                                    (

                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_ID_PESSOA_JURIDICA IN (". $params['partes_pess_jur'] . ")
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_ID_PESSOA_JURIDICA IN (". $params['partes_pess_jur'] . ")
                                                    
                                                    )
                                               "):("");         
          $q .= ($params['partes_unidade'])?(" 
                                                   AND DOCM_ID_DOCUMENTO IN 
                                                   (
                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND (PAPD_SG_SECAO||PAPD_CD_LOTACAO) IN (" . $params['partes_unidade'] . ")             

                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND (PAPD_SG_SECAO||PAPD_CD_LOTACAO) IN (" . $params['partes_unidade'] . ")
                                                    
                                                    )
                                               "):(""); 

          
            $q .= ($docm_id_tipo_situacao_doc)?(" AND DOCM_ID_TIPO_SITUACAO_DOC = '$docm_id_tipo_situacao_doc'"):("");
            $q .= ($docm_id_confidencialidade)?(" AND DOCM_ID_CONFIDENCIALIDADE = '$docm_id_confidencialidade'"):("");
            $q .= ($data_inicial && $data_final)?(" AND DOCM_DH_CADASTRO between TO_DATE('$data_inicial', 'DD/MM/YYYY') AND TO_DATE('$data_final', 'DD/MM/YYYY')+1-1/24/60/60 "):("");
            $q .= (($data_inicial == "") && ($data_final != ""))?(" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('".$data_final."', 'DD/MM/YYYY') AND TO_DATE('".$data_final."', 'DD/MM/YYYY')+1-1/24/60/60 "):("");
            $q .= (($data_inicial != "") && ($data_final == ""))?(" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('".$data_inicial."', 'DD/MM/YYYY') AND TO_DATE('".$data_inicial."', 'DD/MM/YYYY')+1-1/24/60/60 "):("");
            $q .= " ORDER BY $order";
       // }
//        Zend_Debug::dump($q);exit;
        $stmt = $db->query($q);
        return $stmt->fetchAll();

    }
    
    public function getPesquisaDocumentoCount($params,$order)
    {
//         Zend_Debug::dump($params);
//        exit;
                    
//--- PARTES DOS PROCESSO - INICIO        
 // PESSOA TRF/SEÇÕES
        foreach ($params['partes_pessoa_trf'] as $value) {
            $partes_pessoa_trf = explode("-",$value);
            $partes_pessoa_trf_id[] = $partes_pessoa_trf[1];
        }
        $partes_pessoa_trf_id = implode(",",$partes_pessoa_trf_id);
        $params['partes_pessoa_trf'] = $partes_pessoa_trf_id;
 // PESSOA FÍSICA EXTERNA
        foreach ($params['partes_pess_ext'] as $value) {
            $partes_pessoa_ext_id[] = $value;
        }
        $partes_pessoa_ext_id = implode(",",$partes_pessoa_ext_id);   
        $params['partes_pess_ext'] =  $partes_pessoa_ext_id;
 // PESSOA JURIDICA
        foreach ($params['partes_pess_jur'] as $value) {
            $partes_pess_jur_id[] = $value;
        }
        $partes_pess_jur_id = implode(",",$partes_pess_jur_id);   
        $params['partes_pess_jur'] =  $partes_pess_jur_id;
 // PESSOA JURIDICA
        foreach ($params['partes_unidade'] as $value) {
            $partes_unidade = explode("-",$value);
            $partes_unidade_id[] = "'".$partes_unidade[0].$partes_unidade[1]."'";
        }
        $partes_unidade_id = implode(",",$partes_unidade_id);   
        $params['partes_unidade'] =  $partes_unidade_id;         
        
//        Zend_Debug::dump($params);
//        Zend_Debug::dump($partes_pessoa_trf_id);
//        Zend_Debug::dump($partes_pessoa_ext_id);
//        Zend_Debug::dump($partes_pess_jur_id);
//        Zend_Debug::dump($partes_unidade_id);
//Zend_Debug::dump($params);
//--- PARTES DOS PROCESSO - FIM   
        
        
        $mat = explode(' - ', $params["DOCM_CD_MATRICULA_CADASTRO"]);
        $geradora = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
        $redatora = explode(' - ', $params["DOCM_CD_LOTACAO_REDATORA"]);
        $secao = explode('|', $params["TRF1_SECAO_1"]);
        $docm_cd_matricula_cadastro = $mat[0];
        $docm_cd_lotacao_geradora = $geradora[3].$geradora[2];
        $docm_cd_lotacao_redatora = $redatora[3].$redatora[2];
        $sigla_sg_secao = $secao[0];
        $docm_id_tipo_doc = $params["DOCM_ID_TIPO_DOC"];
        $docm_nr_dcmto_usuario = $params["DOCM_NR_DCMTO_USUARIO"];
        $docm_nr_documento = $params["DOCM_ID_DOCUMENTO"];
        $docm_id_pctt = $params["DOCM_ID_PCTT"];
        $docm_ds_palavra_chave = $params["DOCM_DS_PALAVRA_CHAVE"];
        $docm_id_tipo_situacao_doc = $params["DOCM_ID_TIPO_SITUACAO_DOC"];
        //$docm_id_confidencialidade = $params["DOCM_ID_CONFIDENCIALIDADE"];
        $docm_id_confidencialidade = 0;
        $data_inicial = $params['DATA_INICIAL'];
        $data_final = $params['DATA_FINAL'];
        //Zend_Debug::dump($docm_id_confidencialidade); exit;
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT  COUNT(DISTINCT DOCM_ID_DOCUMENTO) COUNT
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                               WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               --AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               --AND  DOCM_IC_ATIVO = 'S'
                               --AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N'
                               --AND DOCM_ID_CONFIDENCIALIDADE = 0
                               AND DOCM.DOCM_ID_TIPO_DOC <> 230 --MINUTAS
                               ";
        
            $q .= ($docm_cd_matricula_cadastro)?(" AND DOCM_CD_MATRICULA_CADASTRO = '$docm_cd_matricula_cadastro'"):('');
            $q .= ($sigla_sg_secao)?(" AND DOCM_SG_SECAO_REDATORA = '$sigla_sg_secao'"):("");
                $q .= ($docm_nr_documento)?(" AND DOCM_NR_DOCUMENTO = $docm_nr_documento"):("");
            $q .= ($docm_cd_lotacao_geradora)?(" AND DOCM.DOCM_SG_SECAO_GERADORA||DOCM.DOCM_CD_LOTACAO_GERADORA = '$docm_cd_lotacao_geradora'"):("");
            $q .= ($docm_cd_lotacao_redatora)?(" AND DOCM.DOCM_SG_SECAO_REDATORA||DOCM.DOCM_CD_LOTACAO_REDATORA = '$docm_cd_lotacao_redatora'"):("");
            $q .= ($docm_id_tipo_doc)?(" AND DOCM_ID_TIPO_DOC = '$docm_id_tipo_doc'"):("");
            $q .= ($docm_nr_dcmto_usuario)?(" AND DOCM_NR_DCMTO_USUARIO = '$docm_nr_dcmto_usuario'"):("");
            $q .= ($docm_id_pctt)?(" AND DOCM_ID_PCTT = '$docm_id_pctt'"):("");
            
            if ($params["DOCM_DS_PALAVRA_CHAVE"]) {
            $docm_ds_palavra_chave = explode(',', $params["DOCM_DS_PALAVRA_CHAVE"]);
            foreach($docm_ds_palavra_chave as $chave){
                $q .= "AND UPPER(DOCM_DS_PALAVRA_CHAVE) LIKE UPPER('%$chave%')";
            }
            }
            
            /**
             *Pesquisa por partes e interressados 
             */
            $q .= ($params['partes_pessoa_trf'])?(" 
                                                    AND DOCM_ID_DOCUMENTO IN 
                                                    (

                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pessoa_trf'] . ")
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pessoa_trf'] . ")
                                                    
                                                    )
                                               "):("");
            $q .= ($params['partes_pess_ext'])?(" 
                                                    AND DOCM_ID_DOCUMENTO IN 
                                                    (

                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pess_ext'] . ")
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_ID_PESSOA_FISICA IN (". $params['partes_pess_ext'] . ")
                                                    
                                                    )
                                               "):(""); 
            $q .= ($params['partes_pess_jur'])?(" 
                                                    AND DOCM_ID_DOCUMENTO IN 
                                                    (

                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_ID_PESSOA_JURIDICA IN (". $params['partes_pess_jur'] . ")
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_ID_PESSOA_JURIDICA IN (". $params['partes_pess_jur'] . ")
                                                    
                                                    )
                                               "):("");         
          $q .= ($params['partes_unidade'])?(" 
                                                   AND DOCM_ID_DOCUMENTO IN 
                                                   (
                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND (PAPD_SG_SECAO||PAPD_CD_LOTACAO) IN (" . $params['partes_unidade'] . ")             

                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND (PAPD_SG_SECAO||PAPD_CD_LOTACAO) IN (" . $params['partes_unidade'] . ")
                                                    
                                                    )
                                               "):(""); 
          
          
            $q .= ($docm_id_tipo_situacao_doc)?(" AND DOCM_ID_TIPO_SITUACAO_DOC = '$docm_id_tipo_situacao_doc'"):("");
            $q .= ($docm_id_confidencialidade)?(" AND DOCM_ID_CONFIDENCIALIDADE = '$docm_id_confidencialidade'"):("");
            $q .= ($data_inicial && $data_final)?(" AND DOCM_DH_CADASTRO between TO_DATE('$data_inicial', 'DD/MM/YYYY') AND TO_DATE('$data_final', 'DD/MM/YYYY')+1-1/24/60/60 "):("");
            $q .= (($data_inicial == "") && ($data_final != ""))?(" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('".$data_final."', 'DD/MM/YYYY') AND TO_DATE('".$data_final."', 'DD/MM/YYYY')+1-1/24/60/60 "):("");
            $q .= (($data_inicial != "") && ($data_final == ""))?(" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('".$data_inicial."', 'DD/MM/YYYY') AND TO_DATE('".$data_inicial."', 'DD/MM/YYYY')+1-1/24/60/60 "):("");
            $q .= " ORDER BY $order";
//        Zend_Debug::dump($q);
////        exit;
        $stmt = $db->query($q);
        return $stmt->fetchAll();

    }
    
public function getProcessosUnidade($siglasecao,$codlotacao, $matricula, $order, $parametro = null){
        
    if (!isset($order)) {
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
    if ($parametro == null) {
    } else {
          //Busca pela localização  
        $parametro = str_replace('MOVI.MOVI_CD_SECAO_UNID_ORIGEM', 'LOTA.LOTA_COD_LOTACAO', $parametro);
          }
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       DOCM.DOCM_CD_LOTACAO_GERADORA,
                                       DOCM.DOCM_SG_SECAO_GERADORA,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA, 
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                WHERE 
                                      DOCM.DOCM_ID_TIPO_DOC=152 AND
                                      DOCM.DOCM_CD_MATRICULA_CADASTRO = '$matricula'
                               
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL 
                               AND LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                                    $parametro
                               
                               UNION 
                               
                               SELECT  DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       DOCM.DOCM_CD_LOTACAO_GERADORA,
                                       DOCM.DOCM_SG_SECAO_GERADORA,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA, 
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                               WHERE 
                               
                               DOCM.DOCM_ID_TIPO_DOC=152 AND
                                 DOCM.DOCM_SG_SECAO_REDATORA = '$siglasecao'
                                 AND DOCM.DOCM_CD_LOTACAO_REDATORA = $codlotacao                               
                                 
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL 
                               AND LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                                   $parametro
                                       
                               UNION 
                               
                               
                               SELECT  DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       DOCM.DOCM_CD_LOTACAO_GERADORA,
                                       DOCM.DOCM_SG_SECAO_GERADORA,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA, 
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                               WHERE 
                                      DOCM.DOCM_ID_TIPO_DOC=152 AND
                                      DOCM.DOCM_SG_SECAO_GERADORA = '$siglasecao'
                              	AND DOCM.DOCM_CD_LOTACAO_REDATORA = $codlotacao
                               
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL 
                               AND LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND  DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                                   $parametro
                              ORDER BY $order
                                
       ");
       

        return $stmt->fetchAll();
    	
    }
    
public function getDocumentosUnidade($siglasecao,$codlotacao, $matricula, $order, $parametro){
        //Zend_Debug::dump($order,'order');exit;
    	
    	if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        if ($parametro == null ){
        }else{
            //Busca pela localização
            $parametro = str_replace('MOVI.MOVI_CD_SECAO_UNID_ORIGEM','LOTA.LOTA_COD_LOTACAO',$parametro);  
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       DOCM.DOCM_CD_LOTACAO_GERADORA,
                                       
                                       DOCM.DOCM_SG_SECAO_GERADORA,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA, 
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,                                       
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                               WHERE 
                                DOCM.DOCM_ID_TIPO_DOC!=152 AND
                                DOCM.DOCM_CD_MATRICULA_CADASTRO = '$matricula'                               
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL 
                               AND LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               AND DOCM.DOCM_ID_TIPO_DOC <> 230 --MINUTAS
                               $parametro
                               
                               UNION 
                               
                               SELECT DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       
                                       DOCM.DOCM_CD_LOTACAO_GERADORA,
                                       DOCM.DOCM_SG_SECAO_GERADORA,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA, 
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO, 
                                       
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                               WHERE 
                               
                                 DOCM.DOCM_ID_TIPO_DOC!=152 AND
                                 DOCM.DOCM_SG_SECAO_REDATORA = '$siglasecao'
                                 AND DOCM.DOCM_CD_LOTACAO_REDATORA = $codlotacao                               
                                 
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL 
                               AND LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               $parametro
                               
                               UNION 
                               
                               
                               SELECT DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       DOCM.DOCM_CD_LOTACAO_GERADORA,
                                       DOCM.DOCM_SG_SECAO_GERADORA,
                                       DOCM.DOCM_CD_LOTACAO_REDATORA, 
                                       TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                              14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                              sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                               WHERE 
                                       DOCM.DOCM_ID_TIPO_DOC!=152 AND
                                       DOCM.DOCM_SG_SECAO_GERADORA = '$siglasecao'
                                       AND DOCM.DOCM_CD_LOTACAO_REDATORA = $codlotacao
                               
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL 
                               AND LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               $parametro                               
                               ORDER BY $order
                                
       ");
        //Zend_Debug::dump($stmt);exit;
        return $stmt->fetchAll();
    }
    
    public function getIdProcesso($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DCPR.DCPR_ID_PROCESSO_DIGITAL, 
                                   DCPR.DCPR_ID_DOCUMENTO, 
                                   TO_CHAR(DCPR.DCPR_DH_VINCULACAO_DOC, 'dd/mm/yyy HH24:MI:SS') DCPR_DH_VINCULACAO_DOC
                              FROM SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON DOCM.DOCM_ID_DOCUMENTO = DCPR.DCPR_ID_DOCUMENTO
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            ON DCPR.DCPR_ID_PROCESSO_DIGITAL = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            WHERE DCPR.DCPR_ID_DOCUMENTO = $idDocumento");
        return $stmt->fetchAll();
    }
    
        public function despachoDocumento(array $dataMofaMoviFase,
                                         $nrDocsRed = null,$autoCommit = true)
    {
        /**
         * Despacho Documento
         * Com ou sem troca de nível.
         */
        if($autoCommit){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            //Zend_Debug::dump($data);
            
            /*----------------------------------------------------------------------------------------*/
            /*primeira tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();
            
            //Zend_Debug::dump($dataMofaMoviFase);
//            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = INFORMAR;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
//            $dataMofaMoviFase["MOFA_ID_FASE"] = INFORMAR;
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = INFORMAR;
//            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'INFORMAR';
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            //Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $nrDocsRed["ID_DOCUMENTO"];
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $nrDocsRed["ID_MOVIMENTACAO"];
            if($nrDocsRed["incluidos"]){
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo =  $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            if($autoCommit){
                $db->commit();
            }
             return true;
        
        } catch (Exception $exc) {
            if($autoCommit){
                $db->rollBack();
            }
            //echo $exc->getMessage();
            return false;
        }
    }
    
    public function getNumeroDocumento($idDocOrigem, $idMovimentacao, $dhFase){ 
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $query = ("SELECT DOCM_NR_DOCUMENTO
                          FROM SAD_TB_DOCM_DOCUMENTO
                               INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                               ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                               INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                               ON  MOFA.MOFA_DH_FASE = DCPR_DH_VINCULACAO_DOC
                          WHERE MOFA_ID_MOVIMENTACAO = $idMovimentacao
                          AND DOCM_ID_TIPO_DOC <> 152 
                          AND DCPR_ID_PROCESSO_DIGITAL IN (SELECT DCPR_ID_PROCESSO_DIGITAL
                                                           FROM SAD_TB_DOCM_DOCUMENTO
                                                                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                                                ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                                                INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                                                ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                                           WHERE DOCM_ID_DOCUMENTO = $idDocOrigem)
                          AND TO_DATE(TO_CHAR(MOFA.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS'),'DD/MM/YYYY HH24:MI:SS') = TO_DATE('".$dhFase."','DD/MM/YYYY HH24:MI:SS')");

        //Zend_Debug::dump($query);    exit; 

       $stmt = $db->query($query);
       $numeros = array();
       foreach($stmt->fetchAll()as $value){
         $numeros[] = $value['DOCM_NR_DOCUMENTO'];
       }
       return $numeros;
    }
    
    
    /*
     * Metodo que prepara os dados a serem inseridos no cadastro de documentos
     */
    public function preparaArrayDocumento(array $data, array $dadosMinuta = array()){
        
        $userNs = new Zend_Session_Namespace('userNs');
        $Dual = new Application_Model_DbTable_Dual();
        $datahora = $Dual->sysdate();
        $dataMinuta = array();
        $dataMofaMoviFaseMin = array();
        $anexAnexo = array();

        // verifica se envia para caixa de entrada da unidade ou rascunho
         if($data['DESTINO_DOCUMENTO'] == 'E'){
                $dados_input = Zend_Json::decode($data['UNIDADE']);

                if( !empty($dados_input) ){
                    $siglaSecaoDestino = $dados_input['LOTA_SIGLA_SECAO'];
                    $codCaixaDestino   = $dados_input['LOTA_COD_LOTACAO'];
                    $siglaCaixa        = $dados_input['LOTA_SIGLA_LOTACAO'];
                }else{
                    $siglaSecaoDestino = $userNs->siglasecao;
                    $codCaixaDestino   = $userNs->codlotacao;
                    $siglaCaixa        = $userNs->siglalotacao;
                }
         }
         
         // prepara arrays para insercao de movimentacao
         $dataMoviMovimentacao = array('MOVI_SG_SECAO_UNID_ORIGEM' => $siglaSecaoDestino,
                                       'MOVI_CD_SECAO_UNID_ORIGEM' => $codCaixaDestino,
                                       'MOVI_CD_MATR_ENCAMINHADOR' => $userNs->matricula);
         $dataModeMoviDestinatario = array('MODE_SG_SECAO_UNID_DESTINO' => $siglaSecaoDestino,
                                           'MODE_CD_SECAO_UNID_DESTINO' => $codCaixaDestino,
                                           'MODE_IC_RESPONSAVEL' => 'N');
         $dataMofaMoviFase = array('MOFA_ID_FASE' => 1010, /*ENCAMINHAMENTO SISAD*/
                                   'MOFA_CD_MATRICULA' => $userNs->matricula,
                                   'MOFA_DS_COMPLEMENTO' => 'Documento cadastrado e enviado para a Caixa da Unidade - '.$siglaCaixa
         );
         $dataModpDestinoPessoa =  array();
         
         //prepara os campos que nao vem do form
         $data["DOCM_DH_CADASTRO"] = new Zend_Db_Expr("SYSDATE");
         $data["DOCM_CD_MATRICULA_CADASTRO"] = $userNs->matricula;

         $aux_DOCM_CD_LOTACAO_GERADORA = $data['DOCM_CD_LOTACAO_GERADORA'];
         $docm_cd_lotacao_geradora_array = explode(' - ', $data['DOCM_CD_LOTACAO_GERADORA']);
         $data['DOCM_SG_SECAO_GERADORA'] = $docm_cd_lotacao_geradora_array[3];
         $data['DOCM_CD_LOTACAO_GERADORA'] = $docm_cd_lotacao_geradora_array[2];

         $aux_DOCM_CD_LOTACAO_REDATORA = $data['DOCM_CD_LOTACAO_REDATORA'];
         $docm_cd_lotacao_redatora_array = explode(' - ', $data['DOCM_CD_LOTACAO_REDATORA']);
         $data['DOCM_SG_SECAO_REDATORA'] = $docm_cd_lotacao_redatora_array[3];
         $data['DOCM_CD_LOTACAO_REDATORA'] = $docm_cd_lotacao_redatora_array[2];

         // prepara array cadastro de partes/vistas
          $dataPartePessoa = array();
          $dataParteLotacao = array();
          $dataPartePessExterna = array();
          $dataPartePessJur = array();
          
          if( count($data['partes_pessoa_trf']) > 0 ){
                    $dataPartePessoa = array_unique($data['partes_pessoa_trf']);
          }
          if( count($data['partes_unidade']) > 0 ){
                    $dataParteLotacao = array_unique($data['partes_unidade']);
          }
          if( count($data['partes_pess_ext']) > 0 ){
                    $dataPartePessExterna = array_unique($data['partes_pess_ext']);
          }
          if( count($data['partes_pess_jur']) > 0 ){
                    $dataPartePessJur = array_unique($data['partes_pess_jur']);
          }

          unset($data["DOCM_ID_DOCUMENTO"]);

          $data["DOCM_NR_SEQUENCIAL_DOC"] = $this->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_ID_TIPO_DOC']);
          $data["DOCM_NR_DOCUMENTO"] = $this->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'],$data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], $data['DOCM_ID_TIPO_DOC'], $data["DOCM_NR_SEQUENCIAL_DOC"]);
          $data["DOCM_DS_ASSUNTO_DOC"] = new Zend_Db_Expr( " CAST( '". $data['DOCM_DS_ASSUNTO_DOC'] ."' AS VARCHAR(4000)) " );
          
          if(count($dadosMinuta) > 0){             
             $dataMinuta = array( "DTPD_NO_TIPO" => $dadosMinuta["DTPD_NO_TIPO"], 
                                  "DOCM_ID_DOCUMENTO" => $dadosMinuta["DOCM_ID_DOCUMENTO"],
                                  "DOCM_NR_DOCUMENTO_RED" => $dadosMinuta["DOCM_NR_DOCUMENTO_RED"]
             );
             
             $dataMofaMoviFaseMin = $dadosMinuta['dataMofaMoviFaseMin'];
             
             $anexAnexo['ANEX_ID_DOCUMENTO'] = $dadosMinuta['anexAnexo']["ANEX_ID_DOCUMENTO"];
             $anexAnexo['ANEX_DH_FASE'] = $datahora;
             $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $dadosMinuta['anexAnexo']["ANEX_NR_DOCUMENTO_INTERNO"];
          }
          
          $dadosInserir = array( 
                                'dataDocmDocumento'         => $data, 
                                'dataMoviMovimentacao'      => $dataMoviMovimentacao,
                                'dataModeMoviDestinatario'  => $dataModeMoviDestinatario,
                                'dataMofaMoviFase'          => $dataMofaMoviFase,
                                'dataPartePessoa'           => $dataPartePessoa,
                                'dataParteLotacao'          => $dataParteLotacao,
                                'dataPartePessExterna'      => $dataPartePessExterna,
                                'dataPartePessJur'          => $dataPartePessJur,
                                'dataMinuta'                => $dataMinuta,
                                'dataMofaMoviFaseMin'       => $dataMofaMoviFaseMin,
                                'dataAnexAnexoMin'          => $anexAnexo,
                                'replicaVistas'             => $data['REPLICA_VISTAS']
                                );
          
         return $dadosInserir;
    }
    
    
    /*
     * Metodo de cadastro de documentos
     */
    public function cadastrarDocumento(array $data, $nrDocsRed = null, $autoCommit = true){
        
            $userNs = new Zend_Session_Namespace('userNs');
            
            if($autoCommit){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
        try{   
             //Zend_Debug::dump($data, 'data antes de salvar'); //exit;
             
             $row = $this->createRow($data['dataDocmDocumento']);
             $idDocumento = $row->save();
             $rowDocmDocumento = $this->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento")->toArray();
             $rowDocmDocumento['replicaVistas'] = $data['replicaVistas'];
              
             //Zend_Debug::dump($rowDocmDocumento, 'rowDocmDocumento');
             //Zend_Debug::dump($idDocumento, 'id documento');
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed');
             //exit;
            
             //encminha o documento para caixa de entrada da unidade, se for o caso
             if($data['dataDocmDocumento']['DESTINO_DOCUMENTO'] == 'E'){
                 $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
                 $dataModpDestinoPessoa = array();
                 $encaCaixaUnidade = $SadTbModoMoviDocumento->encaminhaDocumento($idDocumento, $data['dataMoviMovimentacao'], $data['dataModeMoviDestinatario'], $data['dataMofaMoviFase'], $dataModpDestinoPessoa, $nrDocsRed, false);
             } else {
                $Dual = new Application_Model_DbTable_Dual();
                $datahora = $Dual->sysdate();
                $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocumento;
                $anexAnexo['ANEX_DH_FASE'] = $datahora;
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
            }
             
             // Cadastra partes/vistas no documento 
             $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
             if( count($data['dataPartePessoa']) > 0 ||  count($data['dataParteLotacao']) > 0 || count($data['dataPartePessExterna']) > 0 || count($data['dataPartePessJur']) > 0 ){
                 if(!empty($rowDocmDocumento))
                 $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($data['dataPartePessoa'], $data['dataParteLotacao'], $data['dataPartePessExterna'], $data['dataPartePessJur'], $rowDocmDocumento, array(), false);
             }
             
             if( count($data['dataMinuta']) > 0 &&  $data['dataMinuta']['DTPD_NO_TIPO']  == "Minuta"){  
              
                $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($data['dataMofaMoviFaseMin']);
                $idMofaMoviFaseMin = $rowMofaMoviFase->save();
               
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc(); 
                $vidcVinculacaoDoc = $SadTbVidcVinculacaoDoc->vincularMinuta($idDocumento, 
                                                                             $data['dataMinuta']['DOCM_ID_DOCUMENTO'],
                                                                             $userNs->matricula,
                                                                             false); 
             }
             if($autoCommit){
                 $db->commit();
             }

        }catch(Exception $e){
            
            if($autoCommit){
                $db->rollBack();
            }
            throw $e;
        }
        
        return $rowDocmDocumento;
    }
    
    public function criarArquivo(array $data, $extensao)
    {
       $nomearquivo = uniqid(); 
       $caminho = realpath(APPLICATION_PATH . '/../temp');
       $caminho .= DIRECTORY_SEPARATOR . 'SISADTEMPDOC' . date("dmYHisu") .$nomearquivo.".".$extensao ;

       $fopen = fopen($caminho, 'w');
       $fwrite = fwrite($fopen, $data["TEXTO_HTML"]);
       $fclose = fclose($fopen);

       return $caminho;
    }
    
    public function abrirArquivo(array $data, $codExtensao)
    {
     $userNs = new Zend_Session_Namespace('userNs');
    
     $parametros = new Services_Red_Parametros_Recuperar();
     $parametros->ip = substr($_SERVER['REMOTE_ADDR'],0,15);

     if(defined('APPLICATION_ENV')){
         if (APPLICATION_ENV == 'development') {
             $parametros->login = 'TR227PS';
         }else if(APPLICATION_ENV == 'production'){
             $parametros->login = $userNs->matricula;
         }
     }

     $parametros->sistema = 'EADMIN';
     $parametros->nomeMaquina = substr($_SERVER['HTTP_USER_AGENT'],0,50);
     $nrRedarray = explode('-', $data['anexos'][0]);
     $nrRed = $nrRedarray[0];
     $parametros->numeroDocumento = $nrRed;

     if (defined('APPLICATION_ENV')) {
         if (APPLICATION_ENV == 'development') {
           $red = new Services_Red_Recuperar(true);/*DESENVOLVIMENTO*/                    
         } else if (APPLICATION_ENV == 'production') {
           $red = new Services_Red_Recuperar(false);/*PRODUÇÃO*/
         }
     }

     $red->debug = false;
     $retorno = $red->recuperar($parametros);
     $arquivo = file_get_contents($retorno['url']);

     $nomearquivo = uniqid(); // 'arquivo';
     $caminho = realpath(APPLICATION_PATH . '/../temp');
     $caminho .= DIRECTORY_SEPARATOR . 'SISADTEMPDOC';

     include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
     $mpdf=new mPDF();

     $mpdf->WriteHTML($arquivo);

     $caminhoCompleto = $caminho . $nomearquivo . date("dmYHisu") . '.pdf';
     $mpdf->Output($caminhoCompleto,'F');
      
     return $caminhoCompleto;
    }
    
    public function retornaCodExtensao($extensao)
    {
     
     $db = Zend_Db_Table_Abstract::getDefaultAdapter();
     $query = ("SELECT TPEX_ID_TP_EXTENSAO
                FROM SAD.SAD_TB_TPEX_TIPO_EXTENSAO
                WHERE TPEX_DS_TP_EXTENSAO = '$extensao'");

       $stmt = $db->query($query);
       return $stmt->fetchAll();
    }
    
    public function retornaExtensao($cod)
    {
     
     $db = Zend_Db_Table_Abstract::getDefaultAdapter();
     $query = ("SELECT TPEX_DS_TP_EXTENSAO
                FROM SAD.SAD_TB_TPEX_TIPO_EXTENSAO
                WHERE TPEX_ID_TP_EXTENSAO = '$cod'");

       $stmt = $db->query($query);
       return $stmt->fetchAll();
    }
    /**
     * 1
     * Retorna os dados do documento pelo Número(2012010001155011550165000001) do documento ...
     * @param integer $NrDoc
     * @throws Exception
     */
    public function getDadosDocumentopeloNRDoc($NrDoc){
    	
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$query = "SELECT 
                                   --TIPO DOCUMENTO
                                   DTPD.DTPD_NO_TIPO,
                
                                   --DOCUMENTOS
                                   DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                                   DOCM_CD_MATRICULA_CADASTRO,
                                   -- DOCM.DOCM_DS_ASSUNTO_DOC,
                                   DOCM.DOCM_NR_DOCUMENTO_RED,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                                   DECODE(
                                            LENGTH( DOCM_NR_DOCUMENTO),
                                            14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                   ) MASC_NR_DOCUMENTO,
                
                                   --UNIDADE EMISSORA
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
                                   RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
                                   RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
                                   LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
                                   LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,
                
                                   --UNIDADE REDATORA
                                   RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
                                   RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
                                   LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
                                   LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,
                
                                   --SITUACAO DOCUMENTO
                                   TPSD.TPSD_DS_TIPO_SITUACAO_DOC,
                                   
                                   --CONFIDENCIALIDADE
                                   CONF.CONF_ID_CONFIDENCIALIDADE,
                                   CONF.CONF_DS_CONFIDENCIALIDADE,
                
                                   --ASSUNTO 
                                   AQVP.AQVP_ID_PCTT,
                                   AQVP.AQVP_CD_PCTT,  
                                   AQAT.AQAT_DS_ATIVIDADE,
                
                                   --MOVIMENTACAO
                                   TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
                                   TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                
                                   --FASE
                                    MOFA.MOFA_ID_MOVIMENTACAO,
                                   DOCM.DOCM_ID_TP_EXTENSAO,
                
                                   MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                   MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                   LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO            
                            FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                   ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                   ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                   ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                   ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
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
                                   ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
                                        LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
                                        LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
                                        LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
                                        FROM RH_CENTRAL_LOTACAO
                                    ) LOTA_P
                            WHERE  DOCM.DOCM_NR_DOCUMENTO = $NrDoc
                            AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
                            AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA
                            AND    MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)";
    	$stmt = $db->query($query);
    	return $stmt->fetchAll();
    }
    
    /**
     * 1
     * Verifica se um documento se encontra na caixa de rascunho
     * e retorna matricula, nome e lotação do usuário
     * @param integer $NrDoc
     * @throws Exception
     */
    public function getDocumentoRacunho($NrDoc){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
           $sql = " SELECT 
                                   DOCM.DOCM_CD_MATRICULA_CADASTRO || ' - ' ||  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)ENCAMINHADOR,
                                   RH_LOTA.LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(RH_LOTA.LOTA_SIGLA_SECAO,RH_LOTA.LOTA_COD_LOTACAO),'-',' ')
                                   ||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(RH_LOTA.LOTA_SIGLA_SECAO,RH_LOTA.LOTA_COD_LOTACAO) AS LOTACAO
                                   FROM SAD_TB_DOCM_DOCUMENTO DOCM
                                   INNER JOIN RH_CENTRAL_LOTACAO RH_LOTA
                                   ON RH_LOTA.LOTA_SIGLA_SECAO = DOCM.DOCM_SG_SECAO_REDATORA
                               AND RH_LOTA.LOTA_COD_LOTACAO = DOCM.DOCM_CD_LOTACAO_REDATORA
                                   INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                   ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                   INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                   ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                   INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                   ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                             WHERE 
                               DOCM.DOCM_NR_DOCUMENTO = $NrDoc
                               AND DOCM.DOCM_ID_DOCUMENTO NOT IN (SELECT MODO.MODO_ID_DOCUMENTO
                                                                    FROM SAD_TB_MODO_MOVI_DOCUMENTO MODO
                                                                   WHERE MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               AND DTPD_ID_TIPO_DOC <> 230 --MINUTAS";
                               $stmt = $db->query($sql);
                               return $stmt->fetchAll();
             }
     
     /**
     * 1
     * Verifica se um documento é uma minuta
     * e retorna matricula, nome e lotação do usuário
     * @param integer $NrDoc
     * @throws Exception
     */        
     public function getMinutaDocmNr($NrDoc){
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
           $sql = " SELECT   MODP.MODP_CD_MAT_PESSOA_DESTINO || ' - ' ||   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)ENCAMINHADOR,
                             LOTA.LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO),'-',' ')
                             ||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) AS LOTACAO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       INNER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                                       AND MODP.MODP_CD_SECAO_UNID_DESTINO = MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO
                               WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ATIVO = 'S'
                               AND  DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               AND  DTPD.DTPD_ID_TIPO_DOC = 230 /*Minuta*/
                               AND  DOCM.DOCM_NR_DOCUMENTO = $NrDoc";
                               $stmt = $db->query($sql);
                               return $stmt->fetchAll();   
     }   
     
    public function getPesquisaDocumentoEmProcesso($params,$dcpr_id) {

        $mat = explode(' - ', $params["DOCM_CD_MATRICULA_CADASTRO"]);
        $geradora = explode(' - ', $params["DOCM_CD_LOTACAO_GERADORA"]);
        $redatora = explode(' - ', $params["DOCM_CD_LOTACAO_REDATORA"]);
        $secao = explode('|', $params["TRF1_SECAO_1"]);
        $docm_cd_matricula_cadastro = $mat[0];
        $docm_cd_lotacao_geradora = $geradora[3] . $geradora[2];
        $docm_cd_lotacao_redatora = $redatora[3] . $redatora[2];
        $sigla_sg_secao = $secao[0];
        $docm_id_tipo_proc = $params["TIPO_PROCESSO"];
        $numero_ano = $params["NUMERO_ANO"];
        $docm_id_tipo_doc = $params["DOCM_ID_TIPO_DOC"];
        $docm_nr_dcmto_usuario = $params["DOCM_NR_DCMTO_USUARIO"];
        $docm_nr_documento = $params["DOCM_ID_DOCUMENTO"];
        $docm_id_pctt = $params["DOCM_ID_PCTT"];
        $docm_ds_palavra_chave = $params["DOCM_DS_PALAVRA_CHAVE"];
        $docm_id_tipo_situacao_doc = $params["DOCM_ID_TIPO_SITUACAO_DOC"];
        //$docm_id_confidencialidade = $params["DOCM_ID_CONFIDENCIALIDADE"];
        $docm_id_confidencialidade = 0;
        $data_inicial = $params['DATA_INICIAL'];
        $data_final = $params['DATA_FINAL'];
        //Zend_Debug::dump($docm_id_confidencialidade); exit;
        if (!isset($order)) {
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT DISTINCT DOCM.DOCM_ID_DOCUMENTO,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       DOCM_DH_CADASTRO,
                                       DOCM_CD_MATRICULA_CADASTRO,
                                       DOCM_IC_ARQUIVAMENTO,
                                       DOCM_IC_ATIVO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       LOTA.LOTA_SIGLA_LOTACAO, 
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                       DECODE(LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                                       LEFT OUTER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM.DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                       LEFT OUTER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                       LEFT OUTER JOIN SAD_TB_TPPR_TIPO_PROCESSO
                                       ON PRDI_ID_TIPO_PROCESSO = TPPR_ID_TIPO_PROCESSO
                               WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               --AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               --AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N'
                               --AND DOCM_ID_CONFIDENCIALIDADE = 0
                               AND DOCM.DOCM_ID_TIPO_DOC <> 230 --MINUTAS
                               ";
        $q .= "AND DOCM_ID_DOCUMENTO IN (SELECT DCPR_ID_DOCUMENTO
                                                         FROM SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                                         WHERE DCPR_ID_PROCESSO_DIGITAL =".$dcpr_id."  )";
        $q .= ($docm_cd_matricula_cadastro) ? (" AND DOCM_CD_MATRICULA_CADASTRO = '$docm_cd_matricula_cadastro'") : ('');
        $q .= ($sigla_sg_secao) ? (" AND DOCM_SG_SECAO_REDATORA = '$sigla_sg_secao'") : ("");

        if ($params['OPCAO_NR_DOCUMENTO'] == 'completo') {
            $q .= ($docm_nr_documento) ? (" AND DOCM_NR_DOCUMENTO = $docm_nr_documento") : ("");
        } else {
            $q .= ($docm_nr_documento) ? (" AND DOCM_NR_DOCUMENTO LIKE '%$docm_nr_documento%' ") : ("");
        }

        if ($params['OPCAO_DOCUMENTO'] == 'processo') {
            $q .= " AND DOCM_ID_TIPO_DOC = 152";
        } elseif ($params['OPCAO_DOCUMENTO'] == 'documento') {
            $q .= " AND DOCM_ID_TIPO_DOC <> 152 ";
        }

        if ($numero_ano) {
            $numero = substr($numero_ano, 0, strlen($numero_ano) - 4);
            $ano = substr($numero_ano, strlen($numero_ano) - 4, strlen($numero_ano));

            $q .= " AND SUBSTR(DOCM_NR_DOCUMENTO,1,4) = $ano";
            $q .= " AND SUBSTR(DOCM_NR_DOCUMENTO, LENGTH(DOCM_NR_DOCUMENTO)-5, LENGTH(DOCM_NR_DOCUMENTO)) = $numero";
        }

        $q .= ($docm_cd_lotacao_geradora) ? (" AND DOCM.DOCM_SG_SECAO_GERADORA||DOCM.DOCM_CD_LOTACAO_GERADORA = '$docm_cd_lotacao_geradora'") : ("");
        $q .= ($docm_cd_lotacao_redatora) ? (" AND DOCM.DOCM_SG_SECAO_REDATORA||DOCM.DOCM_CD_LOTACAO_REDATORA = '$docm_cd_lotacao_redatora'") : ("");
        $q .= ($docm_id_tipo_proc) ? (" AND TPPR_ID_TIPO_PROCESSO = '$docm_id_tipo_proc'") : ("");
        $q .= ($docm_id_tipo_doc) ? (" AND DOCM_ID_TIPO_DOC = '$docm_id_tipo_doc'") : ("");
        $q .= ($docm_nr_dcmto_usuario) ? (" AND DOCM_NR_DCMTO_USUARIO = '$docm_nr_dcmto_usuario'") : ("");
        $q .= ($docm_id_pctt) ? (" AND DOCM_ID_PCTT = '$docm_id_pctt'") : ("");


        if ($params["DOCM_DS_PALAVRA_CHAVE"]) {
            $docm_ds_palavra_chave = explode(',', $params["DOCM_DS_PALAVRA_CHAVE"]);
            foreach ($docm_ds_palavra_chave as $chave) {
                $q .= "AND UPPER (DOCM_DS_PALAVRA_CHAVE) LIKE UPPER('%$chave%')";
            }
        }

        $q .= ($docm_id_tipo_situacao_doc) ? (" AND DOCM_ID_TIPO_SITUACAO_DOC = '$docm_id_tipo_situacao_doc'") : ("");
        $q .= ($docm_id_confidencialidade) ? (" AND DOCM_ID_CONFIDENCIALIDADE = '$docm_id_confidencialidade'") : ("");
        $q .= ($data_inicial && $data_final) ? (" AND DOCM_DH_CADASTRO between TO_DATE('$data_inicial', 'DD/MM/YYYY') AND TO_DATE('$data_final', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $q .= (($data_inicial == "") && ($data_final != "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_final . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_final . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $q .= (($data_inicial != "") && ($data_final == "")) ? (" AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY') AND TO_DATE('" . $data_inicial . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        $q .= " ORDER BY $order";
        // }
//           Zend_Debug::dump($q);exit;
        $stmt = $db->query($q);
        return $stmt->fetchAll();
    }
    
    /**
     * Identifica quem cadastrou determinado documento
     * @param type $numDoc Número do documento para verificar quem cadastrou
     * @return type string Matrícula e nome da pessoa que cadastrou o documento
     */
    public function getCadastranteDoc($numDoc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT     PMAT_CD_MATRICULA ||' - '|| PNAT_NO_PESSOA NOME 
              FROM       OCS_TB_PNAT_PESSOA_NATURAL N
              INNER JOIN OCS_TB_PMAT_MATRICULA M
              ON         N.PNAT_ID_PESSOA = M.PMAT_ID_PESSOA
              INNER JOIN SAD_TB_DOCM_DOCUMENTO D
              ON         D.DOCM_CD_MATRICULA_CADASTRO = M.PMAT_CD_MATRICULA
              WHERE      D.DOCM_NR_DOCUMENTO = '".$numDoc."'";
        $stmt = $db->query($q);
        $array = $stmt->fetchAll();
        return $array[0]['NOME'];
    }
    
   }
