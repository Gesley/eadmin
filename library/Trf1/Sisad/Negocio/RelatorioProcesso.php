<?php
/**
 * @category            TRF1
 * @package		Trf1_Sisad_Negocio_RelatorioProcesso
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Dayane Oliveira Freire
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial            Tutorial abaixo
 * 
 * TRF1, Classe negocial dos relatórios de processos do Sisad
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
class Trf1_Sisad_Negocio_RelatorioProcesso { 

     /**
     * Armazena o objeto do adaptador
     *
     * @var  $_db
     */
    protected $_db = null;
   
    /**
     * Método init
     * 
     * @param	none
     * @author	Dayane Oliveira Freire
     */
    public function __construct ()
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }
    
    public function retornaApensadosAnexados ($dados) {
      
        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) ) && 
             (isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ) ){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        if( isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ){
            $lot = explode(' - ', $dados["DOCM_CD_LOTACAO_GERADORA"]); 
            $codSecao = $lot[2];
        }
        
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_APENSA_ANEXA_VINCULA (:P_SECAO, :P_COD_SECAO, "
                                             ." :P_COD_LOTACAO, :P_MATRICULA, TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_SECAO', $siglaSecao);
        $stmt->bindParam(':P_COD_SECAO', $codSecao);
        $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            REPR_NR_DOCUMENTO_ANEXO NR_PROCESSO_FILHO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_JUNTADA,
                            REPR_DS_DESCRICAO TIPO_JUNTADA,
                            REPR_DS_RESP_JUNTADA RESPONSAVEL_JUNTADA,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO_ANEXO ), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO_ANEXO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO_ANEXO)
                                    ) MASC_NR_DOCUMENTO_FILHO,
                             REPR_DS_RESP_EXCLUSAO RESPONSAVEL_EXCLUSAO,
                             REPR_DH_EXCLUSAO DH_EXCLUSAO
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    public function retornaArquivadosUnidade ($dados ){

        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) ) && 
             (isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ) ){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        if( isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ){
            $lot = explode(' - ', $dados["DOCM_CD_LOTACAO_GERADORA"]); 
            $codSecao = $lot[2];
        }
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_ARQUIVADOS_UNIDADE (:P_SECAO, :P_COD_SECAO, :P_COD_LOTACAO, " 
                                                         ." :P_MATRICULA, TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_SECAO', $siglaSecao);
        $stmt->bindParam(':P_COD_SECAO', $codSecao);
        $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            TO_CHAR(REPR_DH_AUTUACAO, 'DD/MM/YYYY hh24:mi:ss') DH_ARQUIVAMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_DS_RESP_JUNTADA TEMPO_GUARDA_CORRENTE,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY') DH_PROVAVEL_DESCARTE
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    

    /*
     * Processos que foram autuados por Unidade, Assunto
     */
    public function retornaAutuadosUnidadeAssunto ($dados ){
        
        
        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) ) && 
             (isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ) ){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        if( isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ){
            $lot = explode(' - ', $dados["DOCM_CD_LOTACAO_GERADORA"]); 
            $codSecao = $lot[2];
        }
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        if ( isset($dados["DOCM_ID_PCTT"]) && !empty($dados["DOCM_ID_PCTT"]) ) {
            $assunto = $dados["DOCM_ID_PCTT"];
        }
        
        $userNs = new Zend_Session_Namespace("userNs");
        //Zend_Debug::dump($dados);
                    
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_AUTUADOS_UNIDADE_ASSUN (:P_SECAO, :P_COD_SECAO, :P_COD_LOTACAO, " 
                                                        ." :P_COD_ASSUNTO, :P_MATRICULA , TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
                      
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_SECAO', $siglaSecao);
        $stmt->bindParam(':P_COD_SECAO', $codSecao);
        $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
        $stmt->bindParam(':P_COD_ASSUNTO', $assunto);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
                      
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            TO_CHAR(REPR_DH_AUTUACAO, 'DD/MM/YYYY hh24:mi:ss') DH_AUTUACAO,
                            --REPR_DH_AUTUACAO DH_AUTUACAO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            RH_DESCRICAO_CENTRAL_LOTACAO(REPR_SG_SECAO, REPR_CD_LOTACAO) UNIDADE_AUTUADORA,
                            RH_SIGLAS_FAMILIA_CENTR_LOTA (REPR_SG_SECAO, REPR_CD_LOTACAO) SIGLA_FAMILIA,
                            REPR_DS_PARTE PARTE
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
                      
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
        }
    
    public function retornaProcessosDistribuidosRedistribuidos ($dados) {
        /*Zend_Debug::dump($dados);
        exit; */
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
                
        $userNs = new Zend_Session_Namespace("userNs");
        
         $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_PROC_DISTRI_REDISTRI (:P_MATRICULA, " 
                                                        ." TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_DISTRIBUICAO,
                            REPR_DS_DESCRICAO MODALIDADE_DISTRIBUICAO,
                            REPR_DS_RESP_EXCLUSAO ORGAO_JULGADOR,
                            REPR_CD_TEMPO_UNIDADE QTDE_DISTRIBUIDOS, 
                            REPR_NR_DOCUMENTO_ANEXO QTDE_REDISTRIBUIDOS, 
                            REPR_DS_RESP_JUNTADA RELATOR, 
                            REPR_DH_JULGAMENTO DH_JULGAMENTO
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    public function retornaEncaminhadosUnidade ($dados ){
        
        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) ) && 
             (isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ) ){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        if( isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ){
            $lot = explode(' - ', $dados["DOCM_CD_LOTACAO_GERADORA"]); 
            $codSecao = $lot[2];
        }
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_ENCAMINHADOS_UNIDADE (:P_SECAO, :P_COD_LOTACAO, " 
                                                         ." :P_MATRICULA, TO_DATE(:P_DT_INICIO,'DD/MM/RRRR'), TO_DATE(:P_DT_FIM, 'DD/MM/RRRR') );"
             ." END; ";
        
        
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':P_SECAO', $siglaSecao);
            $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
            $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
            $stmt->bindParam(':P_DT_INICIO', $data_inicial);
            $stmt->bindParam(':P_DT_FIM', $data_final);
            $stmt->execute();
            
            if (!$stmt)
            {
                Zend_Debug::dump($stmt);
                throw new Exception($stmt->getUserInfo());
            }
            
        }
            catch (Exception $e)
            {
                Zend_Debug::dump($stmt);
                
                die ($e->getMessage());
                
                exit;
            }
        
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_ENCAMINHAMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            RH_DESCRICAO_CENTRAL_LOTACAO(REPR_SG_SECAO, REPR_CD_LOTACAO) UNIDADE_DESTINO,
                            RH_SIGLAS_FAMILIA_CENTR_LOTA (REPR_SG_SECAO, REPR_CD_LOTACAO) SIGLA_FAMILIA,
                            REPR_DS_PARTE PARTE,
                            REPR_DS_RESP_EXCLUSAO RESPONSAVEL,
                            REPR_DS_DESCRICAO LOCAL,
                            REPR_SG_LOTACAO SIGLA_LOTACAO_DESTINO
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    public function retornaProcessosOrgaoJulgador ($dados) {
        
        if( isset($dados["ORGAO_JULGADOR"]) && !empty($dados["ORGAO_JULGADOR"]) ){
            $orgao = $dados["ORGAO_JULGADOR"]; 
        }
        
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_PROC_ORGAO_JULG (:P_ORGAO_JULGADOR, " 
                                                        ." :P_MATRICULA , TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_ORGAO_JULGADOR', $orgao);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_DISTRIBUICAO,
                            REPR_DS_DESCRICAO MODALIDADE_DISTRIBUICAO,
                            REPR_DS_RESP_JUNTADA RELATOR,
                            REPR_DS_RESP_EXCLUSAO ORGAO_JULGADOR
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    /* Método que retorna os processos que estão na Unidade
     * 
     */
    public function retornaProcessosNaUnidade ($dados) {
   
        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) ) && 
             (isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ) ){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        if( isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ){
            $lot = explode(' - ', $dados["DOCM_CD_LOTACAO_GERADORA"]); 
            $codSecao = $lot[2];
        }
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_PROC_NA_UNIDADE (:P_SECAO, :P_COD_LOTACAO, " 
                                                        ." :P_MATRICULA , TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_SECAO', $siglaSecao);
        $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_SG_LOTACAO LOTACAO_ORIGEM,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_RECEBIMENTO,
                            REPR_CD_TEMPO_UNIDADE TEMPO_UNIDADE,
                            REPR_DS_RESP_EXCLUSAO RESPONSAVEL,
                            REPR_DS_DESCRICAO LOCAL
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    public function retornaProcessosSigilosos ($dados) {
       
        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) )){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
        
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_SIGILOSOS (:P_SECAO, :P_COD_LOTACAO, " 
                                                        ." :P_MATRICULA , TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_SECAO', $siglaSecao);
        $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            TO_CHAR(REPR_DH_AUTUACAO, 'DD/MM/YYYY hh24:mi:ss') DH_AUTUACAO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_SG_LOTACAO LOTACAO_ATUAL
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
     /* Método que retorna os processos que estão na Unidade
     * 
     */
    public function retornaProcessosParadosNaUnidade ($dados) {
        
        if( isset($dados["TRF1_SECAO"]) && !empty($dados["TRF1_SECAO"]) ){
            $secao = explode('|', $dados['TRF1_SECAO']); 
            $siglaSecao = $secao[0];
        }
        if( (isset($dados["SECAO_SUBSECAO"]) && !empty($dados["SECAO_SUBSECAO"]) ) && 
             (isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ) ){
            $subsecao = explode('|', $dados['SECAO_SUBSECAO']); 
            if($subsecao[2] == Trf1_Rh_Definicoes::TIPO_LOTA_SUBSECAO_JUDICIARIA){
                $codSecao = $subsecao[1];
            }else{
                $codSecao = NULL;
            }
        }
        if( isset($dados["DOCM_CD_LOTACAO_GERADORA"]) && !empty($dados["DOCM_CD_LOTACAO_GERADORA"]) ){
            $lot = explode(' - ', $dados["DOCM_CD_LOTACAO_GERADORA"]); 
            $codSecao = $lot[2];
        }
        
        $qtdeDias = $dados['QTDE_DIAS'];
                
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_PARADOS_NA_UNIDADE (:P_SECAO, :P_COD_LOTACAO, " 
                                                        ." :P_MATRICULA , :P_QTDE_DIAS );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_SECAO', $siglaSecao);
        $stmt->bindParam(':P_COD_LOTACAO', $codSecao);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_QTDE_DIAS', $qtdeDias);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_SG_LOTACAO LOTACAO_ORIGEM,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_RECEBIMENTO,
                            REPR_CD_TEMPO_UNIDADE TEMPO_UNIDADE,
                            REPR_DS_RESP_EXCLUSAO RESPONSAVEL,
                            REPR_DS_DESCRICAO LOCAL
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    public function retornaProcessosPorRelator ($dados) {
        /*Zend_Debug::dump($dados);
        exit; */
        $matricula_relator = $dados["MATRICULA_RELATOR"];
        $data_inicial = $dados["DATA_INICIAL"];
        $data_final = $dados["DATA_FINAL"];
                
        $userNs = new Zend_Session_Namespace("userNs");
        
        $sql = "BEGIN SAD_P.SAD_PKG_RELATORIOS_PROCESSOS.RETORNA_PROCESSOS_RELATOR (:P_MATRICULA_RELATOR, " 
                                                        ." :P_MATRICULA , TO_DATE(:P_DT_INICIO, 'dd/mm/yyyy'), TO_DATE(:P_DT_FIM, 'dd/mm/yyyy') );"
             ." END; ";
        
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':P_MATRICULA_RELATOR', $matricula_relator);
        $stmt->bindParam(':P_MATRICULA', $userNs->matricula);
        $stmt->bindParam(':P_DT_INICIO', $data_inicial);
        $stmt->bindParam(':P_DT_FIM', $data_final);
        $stmt->execute();
        
        $sqlTemp = "SELECT  REPR_ID_DOCUMENTO ID_DOCUMENTO,
                            REPR_NR_DOCUMENTO NR_PROCESSO,
                            DECODE( LENGTH( REPR_NR_DOCUMENTO), 14, 
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(REPR_NR_DOCUMENTO),
                                            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(REPR_NR_DOCUMENTO)
                                    ) MASC_NR_DOCUMENTO,
                            REPR_DS_ASSUNTO_DOC ASSUNTO,
                            REPR_DS_PARTE PARTE,
                            TO_CHAR(REPR_DH_JUNTADA, 'DD/MM/YYYY hh24:mi:ss') DH_DISTRIBUICAO,
                            REPR_DS_DESCRICAO MODALIDADE_DISTRIBUICAO,
                            REPR_DS_RESP_EXCLUSAO ORGAO_JULGADOR
                    FROM SAD_TT_REPR_RELATORIO_PROCESSO";
        
        $stmt = $this->_db->query($sqlTemp);
        $resultado = $stmt->fetchAll();
        return $resultado;
    }
    
    public function retornaRelatores($nome){
        
        $sql = "   SELECT PNAT_NO_PESSOA||' - '||PMAT_CD_MATRICULA LABEL, 
                          PMAT_CD_MATRICULA MATRICULA
                   FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM,
                         OCS_TB_PMAT_MATRICULA, 
                         OCS_TB_PNAT_PESSOA_NATURAL
                    WHERE PMAT_CD_MATRICULA = CDPA_CD_JUIZ
                    AND  PMAT_ID_PESSOA = PNAT_ID_PESSOA
                    AND PNAT_NO_PESSOA LIKE UPPER('%$nome%')
                    UNION
                    SELECT PNAT_NO_PESSOA||' - '||PMAT_CD_MATRICULA LABEL, 
                           PMAT_CD_MATRICULA MATRICULA 
                    FROM SAD_TB_CCPA_CONT_DIST_COMISSAO,
                         OCS_TB_PMAT_MATRICULA, 
                         OCS_TB_PNAT_PESSOA_NATURAL
                    WHERE PMAT_CD_MATRICULA = CCPA_CD_SERVIDOR
                    AND PMAT_ID_PESSOA = PNAT_ID_PESSOA
                    AND PNAT_NO_PESSOA LIKE UPPER('%$nome%')";
        
        $stmt = $this->_db->query($sql);
        /*Zend_Debug::dump($sql);
        exit;*/
        return $stmt->fetchAll();
        
    }
    /* Calcula a quantidade de processos autuados por unidade
     * 
     */
    public function somatorioPorUnidade ($arrResultado){
            
        for ($i = 0; $i < count($arrResultado) ; $i++) {
            $qtdeUnidade[] = $arrResultado[$i]['UNIDADE_AUTUADORA'].$arrResultado[$i]['SIGLA_FAMILIA'];
        } 
        if ( count($qtdeUnidade) > 0  ) {
                    $somatorioUnidade = array_count_values( $qtdeUnidade );
                    $arrResultado['somaUnidades'] = $somatorioUnidade;
        }
        return $arrResultado;
    }
            
}
