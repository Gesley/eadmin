<?php
class Application_Model_DbTable_SadTbPapdParteProcDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_PAPD_PARTE_PROC_DOC';
    protected $_primary = 'PAPD_ID_PARTE_PROC_DOC';
    protected $_sequence = 'SAD_SQ_PAPD';
    
    
     /*
     * Insere partes e vistas no documento/processo
     * @params array $partePessoa - array com as pessoas do TRF
     * @params array $parteLotacao - array com as lotacoes
     * @params array $partePessExterna - array com as pessoas externas
     * @params array $partePessJur - array com as pessoas juridicas
     * @params array $dataDocumento - array com os dados do documento
     * @params array $dataProcesso - array com os dados do processo
     * @params bool  $autoCommit - flag que indica se o procedimento realizara o autocommit ou nao - padrão é false
     * 
     * @return string - $msg_user - mensagem das partes cadastradas com sucesso
     */
     public function adicionaPartesDocmProc( array $partePessoa, array $parteLotacao, array $partePessExterna, array $partePessJur, array $dataDocumento, array $dataProcesso, $autoCommit = false){
        /*
        Zend_debug::dump($partePessoa);
        Zend_debug::dump($parteLotacao);
        Zend_debug::dump($partePessExterna);
        Zend_debug::dump($partePessJur);
        Zend_debug::dump($dataDocumento, 'documento ');
        Zend_debug::dump($dataProcesso, 'dados processo');
       */
        
        try{
             if($autoCommit){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            }
                        
            if( count($partePessoa) > 0){
                $this->addPartesPessoa($partePessoa, $dataDocumento, $dataProcesso);
            }
            if( count($parteLotacao) > 0 &&  !in_array($dataDocumento['DOCM_ID_CONFIDENCIALIDADE'], array('3','4'))){
                $this->addPartesLotacao($parteLotacao, $dataDocumento, $dataProcesso);
            }
            if( count($partePessExterna) > 0 ){
                $this->addPartesPessoaExterna($partePessExterna, $dataDocumento, $dataProcesso);
            }
            if( count($partePessJur) > 0 ){
                $this->addPartesPessoaJuridica($partePessJur, $dataDocumento, $dataProcesso);
            }
            
             if($autoCommit){
            $db->commit();
             }
             
        }catch(Exception $e){
             if($autoCommit){
            $db->rollBack();
        }
        
            $exec = new Exception('Não foi possível cadastrar partes/vistas no documento. Erro: '.$e, 1, null);
            throw $exec;
            }
        
        }
    
    /*
     * Adiciona partes/vistas pessoa trf no documento/processo
     * @param array - array com as partes do trf
     * @param array - array com os dados do documento
     * @param array - array com os dados do processo
     */
    public function addPartesPessoa(array $partePessoa, array $dataDocumento, array $dataProcesso){
        
        $userNs = new Zend_Session_Namespace('userNs');
        
        foreach($partePessoa as $dados){ 
                $value = explode("-",$dados);
                $dataParteProcDoc = array(  "PAPD_ID_DOCUMENTO"                 => $dataDocumento['DOCM_ID_DOCUMENTO'],
                                            "PAPD_CD_MATRICULA_INCLUSAO"        => $userNs->matricula,
                                            "PAPD_ID_TP_PARTE"                  => $value[2], // 1-parte, 3-vista
                                            "PAPD_ID_PROCESSO_DIGITAL"          => $dataProcesso['PRDI_ID_PROCESSO_DIGITAL'],
                                            "PAPD_CD_MATRICULA_INTERESSADO"     => $value[0],
                                            "PAPD_DH_INCLUSAO"                  => new Zend_Db_Expr('SYSDATE'),
                                            "PAPD_ID_PESSOA_FISICA"             => $value[1] //id da parte do TRF
                                         );
                
                if( isset($dataDocumento["replicaVistas"]) &&  $dataDocumento["replicaVistas"] == 'S' 
                       && $dataDocumento["DOCM_ID_CONFIDENCIALIDADE"] != '0'){
                    $this->replicaVista($dataParteProcDoc);
                }
                
                $rowSadTbPapdParteProcDoc = $this->createRow($dataParteProcDoc);
                $rowSadTbPapdParteProcDoc->save(); 
            }
           // Zend_debug::dump($dataParteProcDoc, 'partes pessoa'); //exit;
    }
    
    /*
     * Adiciona partes/vistas pessoa externa no documento/processo
     * @param array - array com as partes externas
     * @param array - array com os dados do documento
     * @param array - array com os dados do processo
     */
    public function addPartesPessoaExterna(array $partePessExterna,array $dataDocumento, array $dataProcesso){
        
        $userNs = new Zend_Session_Namespace('userNs');
        
        foreach($partePessExterna as $dados){
                $value = explode("-",$dados);
                $dataParteProcDoc = array(  "PAPD_ID_DOCUMENTO"                 => $dataDocumento['DOCM_ID_DOCUMENTO'],
                                            "PAPD_CD_MATRICULA_INCLUSAO"        => $userNs->matricula,
                                            "PAPD_ID_TP_PARTE"                  => $value[1], // 1-parte,
                                            "PAPD_ID_PROCESSO_DIGITAL"          => $dataProcesso['PRDI_ID_PROCESSO_DIGITAL'],
                                            "PAPD_DH_INCLUSAO"                  => new Zend_Db_Expr('SYSDATE'),
                                            "PAPD_ID_PESSOA_FISICA"             => $value[0] //id da parte do TRF
                                         );
               
                if( isset($dataDocumento["replicaVistas"]) &&  $dataDocumento["replicaVistas"] == 'S' 
                        && $dataDocumento["DOCM_ID_CONFIDENCIALIDADE"] != '0' ){
                    $this->replicaVista($dataParteProcDoc);
                }
                
                $rowSadTbPapdParteProcDoc = $this->createRow($dataParteProcDoc);
                $rowSadTbPapdParteProcDoc->save(); 
            }
          //  Zend_debug::dump($dataParteProcDoc, 'partes externas'); //exit;
    }
    
     /*
     * Adiciona partes/vistas lotacao no documento/processo
     * @param array - array com as partes unidades administrativas
     * @param array - array com os dados do documento
     * @param array - array com os dados do processo
     */
    public function addPartesLotacao(array $parteLotacao, array $dataDocumento, array $dataProcesso){
        
        $userNs = new Zend_Session_Namespace('userNs');
        
        foreach($parteLotacao as $dados){ 
                $dadosLotacao = explode("-", $dados);
                $dataParteProcDoc = array(  "PAPD_ID_DOCUMENTO"                 => $dataDocumento['DOCM_ID_DOCUMENTO'],
                                            "PAPD_CD_MATRICULA_INCLUSAO"        => $userNs->matricula,
                                            "PAPD_ID_TP_PARTE"                  => $dadosLotacao[2], // 1-parte, 3-vistas
                                            "PAPD_ID_PROCESSO_DIGITAL"          => $dataProcesso['PRDI_ID_PROCESSO_DIGITAL'],
                                            "PAPD_SG_SECAO"                     => $dadosLotacao[0],
                                            "PAPD_CD_LOTACAO"                   => $dadosLotacao[1],
                                            "PAPD_DH_INCLUSAO"                  => new Zend_Db_Expr('SYSDATE')
                                         );
                if( isset($dataDocumento["replicaVistas"]) &&  $dataDocumento["replicaVistas"] == 'S' 
                        && $dataDocumento["DOCM_ID_CONFIDENCIALIDADE"] != '0' ){
                    $this->replicaVista($dataParteProcDoc);
                }
                $rowSadTbPapdParteProcDoc = $this->createRow($dataParteProcDoc);
                $rowSadTbPapdParteProcDoc->save(); 
            }
          //  Zend_debug::dump($dataParteProcDoc, 'partes lotacao'); //exit;
    }
    
   /*
     * Adiciona partes/vistas pessoa juridica no documento/processo
    *  @param array - array com as partes pessoa juridica
     * @param array - array com os dados do documento
     * @param array - array com os dados do processo
     */
    public function addPartesPessoaJuridica( array $partePessJur, array $dataDocumento, array $dataProcesso){
        
        $userNs = new Zend_Session_Namespace('userNs');
        
        foreach($partePessJur as $dados){ 
                $value = explode("-",$dados);
                $dataParteProcDoc = array(  "PAPD_ID_DOCUMENTO"                 => $dataDocumento['DOCM_ID_DOCUMENTO'],
                                            "PAPD_CD_MATRICULA_INCLUSAO"        => $userNs->matricula,
                                            "PAPD_ID_TP_PARTE"                  => $value[1], // 1-parte, 3-vistas
                                            "PAPD_ID_PROCESSO_DIGITAL"          => $dataProcesso['PRDI_ID_PROCESSO_DIGITAL'],
                                            "PAPD_DH_INCLUSAO"                  => new Zend_Db_Expr('SYSDATE'),
                                            "PAPD_ID_PESSOA_JURIDICA"           => $value[0]
                                         );
                if( isset($dataDocumento["replicaVistas"]) &&  $dataDocumento["replicaVistas"] == 'S' 
                        && $dataDocumento["DOCM_ID_CONFIDENCIALIDADE"] != '0' ){
                    $this->replicaVista($dataParteProcDoc);
                }
                $rowSadTbPapdParteProcDoc = $this->createRow($dataParteProcDoc);
                $rowSadTbPapdParteProcDoc->save(); 
            }
          //  Zend_debug::dump($dataParteProcDoc, 'partes juridica'); //exit;
    }
    
    /*
     * Adiciona interessados em acompanhar baixa de solicitação
     */
    public function addAcompanhanteSostiCaixaAtendimento($idDocumento) {
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;
        $existe = $this->fetchRow("PAPD_CD_MATRICULA_INTERESSADO = '$matricula' AND PAPD_ID_DOCUMENTO = $idDocumento");
        if (is_null($existe)) {
            $dataAcompanhanteSOSTI = array("PAPD_ID_DOCUMENTO" => $idDocumento,
                "PAPD_ID_TP_PARTE" => 6, // 6-Acompanhante SOSTI
                "PAPD_CD_MATRICULA_INCLUSAO" => $matricula,
                "PAPD_CD_MATRICULA_INTERESSADO" => $matricula,
                "PAPD_DH_INCLUSAO" => new Zend_Db_Expr('SYSDATE'),
                "PAPD_CD_MATRICULA_EXCLUSAO" => NULL,
                "PAPD_DH_EXCLUSAO" => NULL
            );
            $rowAcompanhante = $this->createRow($dataAcompanhanteSOSTI);
            $rowAcompanhante->save();
        }
    }
    
    /*
     * Replica os usuarios cadastrados como partes para serem tambem cadastrados 
     * como vistas
     */
    public function replicaVista($dados){
        $dataParteProcDocVista = array();
        $dataParteProcDocVista = $dados;
        $dataParteProcDocVista['PAPD_ID_TP_PARTE'] = 3;
        $rowParteVista = $this->createRow($dataParteProcDocVista);
        $rowParteVista->save();
    }
     
    public function addAcompanhanteSostiCadastroSolicitacao($idDocumento, $matricula) {
        $userNs = new Zend_Session_Namespace('userNs');
        $existe = $this->fetchRow("PAPD_CD_MATRICULA_INTERESSADO = '$matricula' 
                                   AND PAPD_ID_DOCUMENTO = $idDocumento
                                   AND PAPD_DH_EXCLUSAO IS NULL");
        if (is_null($existe)) {
            $tb_dual = new Application_Model_DbTable_Dual(); 
            $dataAcompanhanteSOSTI = array("PAPD_ID_DOCUMENTO" => $idDocumento,
                "PAPD_ID_TP_PARTE" => 6, // 6-Acompanhante SOSTI
                "PAPD_CD_MATRICULA_INCLUSAO" => $userNs->matricula,
                "PAPD_CD_MATRICULA_INTERESSADO" => $matricula,
                "PAPD_DH_INCLUSAO" => $tb_dual->sysdate(),
                "PAPD_CD_MATRICULA_EXCLUSAO" => NULL,
                "PAPD_DH_EXCLUSAO" => NULL
            );
            $rowAcompanhante = $this->createRow($dataAcompanhanteSOSTI);
            $rowAcompanhante->save();
        }
    }
    
    public function addPorOrdemDeCadastroSolicitacao($idDocumento, $matricula) {
        
        $userNs = new Zend_Session_Namespace('userNs');
        $existe = $this->fetchRow("PAPD_CD_MATRICULA_INTERESSADO = '$matricula' AND PAPD_ID_DOCUMENTO = $idDocumento AND PAPD_ID_TP_PARTE = 7");
        if (is_null($existe)) {

            $tb_dual = new Application_Model_DbTable_Dual(); 
            $dataPorOrdemDe = array("PAPD_ID_DOCUMENTO" => $idDocumento,
                "PAPD_ID_TP_PARTE" => 7, // 7-Por ordem de
                "PAPD_CD_MATRICULA_INCLUSAO" => $userNs->matricula,
                "PAPD_CD_MATRICULA_INTERESSADO" => $matricula,
                "PAPD_DH_INCLUSAO" => $tb_dual->sysdate(),
                "PAPD_CD_MATRICULA_EXCLUSAO" => NULL,
                "PAPD_DH_EXCLUSAO" => NULL
            );
            $rowPorOrdemDe = $this->createRow($dataPorOrdemDe);
            $rowPorOrdemDe->save();
        }
    }
     
    public function getAcompanhantesSosti($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PAPD_CD_MATRICULA_INTERESSADO)NOME,
               PAPD_CD_MATRICULA_INTERESSADO,
               SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PAPD_CD_MATRICULA_INCLUSAO) AS CADASTRANTE,
               PAPD_CD_MATRICULA_INCLUSAO,
               TO_CHAR(PAPD_DH_INCLUSAO,'DD/MM/YYYY HH24:MI:SS') AS PAPD_DH_INCLUSAO
        FROM SAD_TB_PAPD_PARTE_PROC_DOC
        WHERE PAPD_ID_TP_PARTE = 6
        AND PAPD_ID_DOCUMENTO = $idDocumento
        AND PAPD_DH_EXCLUSAO IS NULL
        ORDER BY PAPD_CD_MATRICULA_INCLUSAO, PAPD_DH_INCLUSAO");
        return $stmt->fetchAll();
    }
    
    public function getSolAcompanhamento($matricula){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->innerJoinPapd();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereEmAtendimento(true);
        $stmt .= $CaixasQuerys->whereAcompanhamentoSosti($matricula);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }
    
    public function getQtdeSolAcompanhamento($matricula){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(15);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->innerJoinPapd();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereEmAtendimento(true);
        $stmt .= $CaixasQuerys->whereAcompanhamentoSosti($matricula);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }
    
    /**
     * Função que seleciona no banco de dados as partes do documento que estão cadastradas com o tipo "Por ordem de"
     * @author Daniel Rodrigues 
     * @param String $idDocumento
     * @return Array
     */
    public function getPorOrdemDeSosti($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $resultado = $db->fetchRow("SELECT SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PAPD_CD_MATRICULA_INTERESSADO)NOME,
               PAPD_CD_MATRICULA_INTERESSADO,
               SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PAPD_CD_MATRICULA_INCLUSAO) AS CADASTRANTE,
               PAPD_CD_MATRICULA_INCLUSAO,
               TO_CHAR(PAPD_DH_INCLUSAO,'DD/MM/YYYY HH24:MI:SS') AS PAPD_DH_INCLUSAO
        FROM SAD_TB_PAPD_PARTE_PROC_DOC
        WHERE PAPD_ID_TP_PARTE = 7
        AND PAPD_ID_DOCUMENTO = $idDocumento
        AND PAPD_DH_EXCLUSAO IS NULL
        ORDER BY PAPD_CD_MATRICULA_INCLUSAO, PAPD_DH_INCLUSAO");
        return $resultado;
    }
    
    public function getAcompanhantesAtivos($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PAPD_CD_MATRICULA_INTERESSADO)NOME,
               PAPD_CD_MATRICULA_INTERESSADO,
               SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(PAPD_CD_MATRICULA_INCLUSAO) AS CADASTRANTE,
               PAPD_CD_MATRICULA_INCLUSAO,
               TO_CHAR(PAPD_DH_INCLUSAO,'DD/MM/YYYY HH24:MI:SS') AS PAPD_DH_INCLUSAO
        FROM SAD_TB_PAPD_PARTE_PROC_DOC
        WHERE PAPD_ID_TP_PARTE = 6
        AND PAPD_ID_DOCUMENTO = $idDocumento
        AND PAPD_DH_EXCLUSAO IS NULL
        ORDER BY NOME, CADASTRANTE, PAPD_CD_MATRICULA_INCLUSAO, PAPD_DH_INCLUSAO ASC");
        return $stmt->fetchAll();
    }
    
   public function getMinhasSolicitacoesAcompanhamento($matricula,$order){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(5);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->innerJoinPapd();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->leftJoinFaseNivel();
        $stmt .= $CaixasQuerys->leftJoinFaseEspera();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereUltimoNivel();
        $stmt .= $CaixasQuerys->whereUltimaEspera();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereEmAtendimento(true);
        $stmt .= $CaixasQuerys->whereAcompanhamentoSosti($matricula);
        $stmt .= $CaixasQuerys->ordem($order);
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }
    
    public function delAcompanhamento($solicitacoes, $matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            foreach ($solicitacoes as $solicitacao) {
                $decode_sol = Zend_Json_Decoder::decode($solicitacao);
                $idSol = $decode_sol['SSOL_ID_DOCUMENTO'];
                $row_acompanhar = $this->fetchRow("PAPD_CD_MATRICULA_INTERESSADO = '$matricula' AND PAPD_ID_DOCUMENTO = $idSol");
                if (!is_null($row_acompanhar)) {
                    $registro_acomp["PAPD_CD_MATRICULA_EXCLUSAO"] = $matricula;
                    $registro_acomp["PAPD_DH_EXCLUSAO"] = new Zend_Db_Expr('SYSDATE');
                    $row_acompanhar->setFromArray($registro_acomp)->save();
                }
            }
            $db->commit();
            return TRUE;
        } catch (Exception $exc) {
            $db->rollBack();
            throw $exc;
        }
    }
    /*
     * Funcao que busca no banco as partes/interessados do documento/processo
     * @params $dcmto - id do documento
     * @params $idproc - id do processo
     * @return array com os interessados
     */
    public function getPartesVistas($dcmto, $idproc, $tipo = 3){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $and = "";
        
        if( !empty($dcmto) ){
            $and = " AND PAPD_ID_DOCUMENTO = $dcmto ";
        }
        if( !empty($idproc) ){
            $and = " AND PAPD_ID_PROCESSO_DIGITAL = $idproc ";
        }
        
        
        $query ="SELECT PNAT_NO_PESSOA||' - '||PAPD_CD_MATRICULA_INTERESSADO NOME,
                        PAPD_ID_DOCUMENTO ID_DOC, 
                        PAPD_ID_PESSOA_FISICA ID, 
                        PAPD_CD_MATRICULA_INTERESSADO||'-'||PAPD_ID_PESSOA_FISICA VALUE,
                        'partes_pessoa_trf[]' TIPO,
                        1 ORDEM, -- nao alterar esse valor
                        PAPD_CD_MATRICULA_INTERESSADO USUARIO,
                        TPDP_ID_PARTE TIPO_PARTE
                FROM OCS_TB_PNAT_PESSOA_NATURAL,
                     SAD_TB_PAPD_PARTE_PROC_DOC,
                     OCS_TB_PMAT_MATRICULA,
                     SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
                WHERE PMAT_CD_MATRICULA = PAPD_CD_MATRICULA_INTERESSADO
                AND PAPD_ID_PESSOA_FISICA = PNAT_ID_PESSOA 
                AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
                AND TPDP_ID_PARTE = $tipo
                AND PAPD_DH_EXCLUSAO IS NULL  
                $and
                UNION
                SELECT  PNAT_NO_PESSOA NOME,
                        PAPD_ID_DOCUMENTO ID_DOC, 
                        PAPD_ID_PESSOA_FISICA ID, 
                        PAPD_ID_PESSOA_FISICA||'' VALUE,
                        'partes_pess_ext[]' TIPO,
                        2 ORDEM, -- nao alterar esse valor
                        '' USUARIO,
                        TPDP_ID_PARTE TIPO_PARTE
                FROM OCS_TB_PNAT_PESSOA_NATURAL,
                     SAD_TB_PAPD_PARTE_PROC_DOC,
                     OCS_TB_PMAT_MATRICULA,
                     SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
                WHERE PMAT_CD_MATRICULA(+) = PAPD_CD_MATRICULA_INTERESSADO
                AND PAPD_ID_PESSOA_FISICA = PNAT_ID_PESSOA 
                AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
                AND TPDP_ID_PARTE = $tipo
                AND PAPD_DH_EXCLUSAO IS NULL
                $and
                AND PAPD_ID_PESSOA_FISICA NOT IN (SELECT PAPD_ID_PESSOA_FISICA
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC P
                                                    WHERE P.PAPD_ID_PESSOA_FISICA = PAPD_ID_PESSOA_FISICA
                                                    AND PAPD_CD_MATRICULA_INTERESSADO IS NOT NULL
                                                    $and)
                UNION
                SELECT PJUR_NO_RAZAO_SOCIAL NOME,
                        PAPD_ID_DOCUMENTO ID_DOC,
                        PAPD_ID_PESSOA_JURIDICA ID,
                        PAPD_ID_PESSOA_JURIDICA||'' VALUE,
                        'partes_pess_jur[]' TIPO,
                        3 ORDEM, -- nao alterar esse valor
                        '' USUARIO,
                        TPDP_ID_PARTE TIPO_PARTE
                 FROM OCS_TB_PJUR_PESSOA_JURIDICA,
                      SAD_TB_PAPD_PARTE_PROC_DOC,
                      SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
                 WHERE PAPD_ID_PESSOA_JURIDICA = PJUR_ID_PESSOA
                 AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
                 AND TPDP_ID_PARTE = $tipo
                 AND PAPD_DH_EXCLUSAO IS NULL
                 $and
                 UNION
                 SELECT  LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(PAPD_SG_SECAO,PAPD_CD_LOTACAO),'-',' ') ||' - '||PAPD_CD_LOTACAO||' - '||PAPD_SG_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(PAPD_SG_SECAO,PAPD_CD_LOTACAO) NOME,
                         PAPD_ID_DOCUMENTO ID_DOC,
                         PAPD_CD_LOTACAO ID,
                         PAPD_SG_SECAO||'-'||PAPD_CD_LOTACAO VALUE,
                         'partes_unidade[]' TIPO,
                         4 ORDEM, -- nao alterar esse valor
                         TO_CHAR(PAPD_CD_LOTACAO) USUARIO,
                         TPDP_ID_PARTE TIPO_PARTE
                  FROM SAD_TB_PAPD_PARTE_PROC_DOC,
                       RH_CENTRAL_LOTACAO,
                       SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
                  WHERE PAPD_SG_SECAO = LOTA_SIGLA_SECAO
                  AND PAPD_CD_LOTACAO = LOTA_COD_LOTACAO
                  AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
                  AND TPDP_ID_PARTE = $tipo
                  AND PAPD_DH_EXCLUSAO IS NULL
                  $and 
                  ORDER BY ORDEM";
                                  
        $stmt = $db->query($query);
  //     Zend_debug::dump($query);
        return $stmt->fetchAll();
    }
    
    
    /* Funçao que retorna o array com todas as partes/vistas 
     * @param array $documentos - Array com os documentos/processos 
     * 
     * @return array $interessados - Array com todas as partes/vistas cadastrados
     */
    public function mostraPartesVistas(array $documentos, $tipo){
        
        //Zend_debug::dump($documentos); exit;
        
        foreach ($documentos as $value) {
            
                    $dados_input = Zend_Json::decode($value);
                    //  Zend_Debug::dump($dados_input);
                    $id_documento = $dados_input['DOCM_ID_DOCUMENTO'];
                    //    Zend_Debug::dump($id_documento);

                    $interessados_docs = $this->getPartesVistas($id_documento, null, $tipo);
                        if( !empty($interessados_docs) ){
                            foreach($interessados_docs as $int){
                                $inter[] = array( 
                                                    "nome" => $int['NOME'],
                                                    "id" => $int['ID'],
                                                    "input_name" => $int['TIPO'],
                                                    "value" => $int['VALUE'],
                                                    "tipo_parte" => $int['TIPO_PARTE']
                                                );
                            }
                        }
                 }
                 
         return $inter;
    }
    
    
    /*
     * Funcao que verifica se o usuario é parte/vista no documento ou processo,
     * se nao for passada uma matricula por parametro, é verificada a matricula do
     * usuário que está logado no sistema.
     * 
     * @params int $idDocumento
     * @params int $idProcesso
     * @params int $tipo - parte ou vista
     * @params string $matricula
     * 
     * @return true - se encontrar o interessado
     * @return false - se não encontrar o interessado
     */
    public function verificaParteVista( $idDocumento = null, $idProcesso = null, $tipo = 3, $matricula = null){
        
   //     Zend_debug::dump($idDocumento, 'id documento');
   //     Zend_debug::dump($idProcesso, 'id processo');
        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
        
        if( empty($matricula) ){
            $userNs = new Zend_Session_Namespace('userNs');
            $matricula = $userNs->matricula;
        }
        
        //verifico se a pessoa que esta cadastrando é parte/interessado
         $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
         $responsavelCaixas = $OcsTbUnpeUnidadePerfil->getResponsávelCaixaUnidade($matricula);
         $interessados = $this->getPartesVistas($idDocumento, $idProcesso, $tipo);
       //  Zend_debug::dump($interessados);
         
         foreach ($interessados as $parte){
            foreach($responsavelCaixas as $caixa){
                $unidade = $caixa['LOTA_SIGLA_SECAO'].'-'.$caixa['LOTA_COD_LOTACAO'];
                if ((strcmp($matricula, $parte['USUARIO'])== '0') ||
                     strcmp($unidade, $parte['VALUE']) == '0'){
                    return true;
                }
            }
         } 
         return false;
    }
    
    /*
     * Funcao que faz as validacoes se o usuario pode cadastrar vistas em documentos
     * @param array - $dataDocumento
     * 
     * @return false 
     * @return true
     */
    public function verificaPermissaoCadastroVistas($dataDocumento){
        
            if ($dataDocumento['DTPD_ID_TIPO_DOC'] == '152') { //se for processo, busca o id
                $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
                $dataProcesso = $SadTbPrdiProcessoDigital->getProcesso($dataDocumento['DOCM_ID_DOCUMENTO']);
                $verifica = $this->verificaParteVista($dataDocumento['DOCM_ID_DOCUMENTO'], $dataProcesso['PRDI_ID_PROCESSO_DIGITAL'], 3);
            } else {
                $verifica = $this->verificaParteVista($dataDocumento['DOCM_ID_DOCUMENTO'], null, 3);
}
            if (!$verifica) {
                return false;
            }else{
                return true;
            }
    }
   
    /**
     * Exclui acompanhantes do Sosti
     */
     public function delAcompanhanteSostiCadastroSolicitacao($idDocumento, $matricula) {
         $userNs = new Zend_Session_Namespace('userNs');
         $existe = $this->fetchRow("PAPD_CD_MATRICULA_INTERESSADO = '".$matricula."'
                                    AND PAPD_ID_DOCUMENTO = '".$idDocumento."'
                                    AND PAPD_DH_EXCLUSAO IS NULL");
         if (!is_null($existe)) {
             $row_acompanhar = $this->fetchRow("PAPD_CD_MATRICULA_INTERESSADO = '".$matricula."'
                                                AND PAPD_ID_DOCUMENTO = '".$idDocumento."'
                                                AND PAPD_DH_EXCLUSAO IS NULL");
            
             $registro_acomp["PAPD_CD_MATRICULA_EXCLUSAO"] = $userNs->matricula;
             $registro_acomp["PAPD_DH_EXCLUSAO"] = new Zend_Db_Expr('SYSDATE');
             $row_acompanhar->setFromArray($registro_acomp)->save(); 
          }
     }

}
