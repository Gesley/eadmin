<?php
/**
 * Description of AcessoCaixaUnidade 
 *
 * @author Leonan Alves dos Anjos
 */


class App_Controller_Plugin_AcessoCaixaUnidade extends Zend_Controller_Plugin_Abstract {
    
    public $_sgsecao_caixa_unidade;
    public $_cdlotacao_caixa_unidade;
    public $_siglalotacao_caixa_unidade;
    public $_desclotacao_caixa_unidade;
    public $_matricula_usuario;
    public $_userNamespace;
    public $_inited;
    
    /**
     * 
     * Restaura a partir da sessão os atributos da classe
     */
    public function __construct(){
        $this->_userNamespace = new Zend_Session_Namespace('userNs');
        $this->_sgsecao_caixa_unidade = $this->_userNamespace->_sgsecao_caixa_unidade;
        $this->_cdlotacao_caixa_unidade = $this->_userNamespace->_cdlotacao_caixa_unidade; 
        $this->_siglalotacao_caixa_unidade = $this->_userNamespace->_siglalotacao_caixa_unidade; 
        $this->_desclotacao_caixa_unidade = $this->_userNamespace->_desclotacao_caixa_unidade; 
        $this->_matricula_usuario = $this->_userNamespace->_matricula_usuario; 
        $this->_inited = $this->_userNamespace->_inited; 
    }
    
    /*
     * Executa no bootstrap
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        /**
         * Configuração para execução somente no modulo sisad
         */
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());
        $action = strtolower($request->getActionName());
        
        if ( !in_array($module,array('sisad','sosti')) ) {
            return;
        }
        $resource = Zend_Controller_Front::getInstance ()->getParam('bootstrap')->getPluginResource('multidb');
        $db = $resource->getDb($module);
        Zend_Db_Table::setDefaultAdapter($db);
        
        /**
         * Iniciando o primeiro acesso
         */
        $this->_userNamespace = new Zend_Session_Namespace('userNs');
    	$AcessoCaixaUnidade  = new App_Controller_Plugin_AcessoCaixaUnidade();
        if(! $this->_inited ){
            $AcessoCaixaUnidade->initAcessoCaixaUnidade($this->_userNamespace->matricula);
        }
        
    }
    
    public function setMatricula($matricula){
        $this->_matricula_usuario = $matricula;
        $this->_userNamespace->_matricula_usuario =  $this->_matricula_usuario;
    }
    public function setSgsecaoCaixaUnidade($sgsecao_caixa_unidade){
        $this->_sgsecao_caixa_unidade = $sgsecao_caixa_unidade;
        $this->_userNamespace->_sgsecao_caixa_unidade =  $this->_sgsecao_caixa_unidade;
    }
    public function setCdlotacaoCaixaUnidade($cdlotacao_caixa_unidade){
        $this->_cdlotacao_caixa_unidade = $cdlotacao_caixa_unidade;
        $this->_userNamespace->_cdlotacao_caixa_unidade =  $this->_cdlotacao_caixa_unidade;
    }
    public function setSiglaLotacaoCaixaUnidade($_siglalotacao_caixa_unidade){
        $this->_siglalotacao_caixa_unidade = $_siglalotacao_caixa_unidade;
        $this->_userNamespace->_siglalotacao_caixa_unidade =  $this->_siglalotacao_caixa_unidade;
    }
    public function setDescLotacaoCaixaUnidade($_desclotacao_caixa_unidade){
        $this->_desclotacao_caixa_unidade = $_desclotacao_caixa_unidade;
        $this->_userNamespace->_desclotacao_caixa_unidade =  $this->_desclotacao_caixa_unidade;
    }
    public function setInited($inited = true){
        $this->_inited = $inited;
        $this->_userNamespace->_inited =  $this->_inited;
    }
    
    
    public function getMatricula(){
        return $this->_matricula_usuario;
    }
    public function getSgsecaoCaixaUnidade(){
        return $this->_sgsecao_caixa_unidade;
    }
    public function getCdlotacaoCaixaUnidade(){
        return $this->_cdlotacao_caixa_unidade;
    }
    public function getSiglaLotacaoCaixaUnidade(){
        return $this->_siglalotacao_caixa_unidade;
    }
    public function getDescLotacaoCaixaUnidade(){
        return $this->_desclotacao_caixa_unidade;
    }
    public function getInited(){
        return $this->_inited;
    }
    
    public function initAcessoCaixaUnidade($matricula){
        
        $this->setInited();
        $flag_caixa_lotacao = 'N';
        
        $CaixasUnidadeAcesso =  $this->getAcessoCaixaUnidade($matricula);
        $CaixasUnidadeAcesso_aux = $CaixasUnidadeAcesso;
  
        $userNs = new Zend_Session_Namespace('userNs');
        
        /*
         * Verifica se a lotacao do usuario esta entre as caixas em que ele possui permissao
         * se estiver coloca a lotacao como caixa padrao
         */
        foreach($CaixasUnidadeAcesso_aux as $caixa){
            if( strcmp($userNs->siglasecao, $caixa['LOTA_SIGLA_SECAO']) == 0 && 
                strcmp($userNs->codlotacao, $caixa['LOTA_COD_LOTACAO']) == 0 )
            {
                    $sgsecao_caixa_unidade = $userNs->siglasecao;
                    $cdlotacao_caixa_unidade = $userNs->codlotacao;
                    $siglalotacao_caixa_unidade = $userNs->siglalotacao;
                    $desclotacao_caixa_unidade = $userNs->descicaolotacao;
                    $flag_caixa_lotacao = 'S';
            }
        }
        
        /*
         * Se a lotacao nao estiver entre as caixas com permissao
         * carrega a primeira caixa retornada da consulta  
         */
        if ($flag_caixa_lotacao == 'N'){
            $sgsecao_caixa_unidade = $CaixasUnidadeAcesso[0]['LOTA_SIGLA_SECAO'];
            $cdlotacao_caixa_unidade = $CaixasUnidadeAcesso[0]['LOTA_COD_LOTACAO'];
            $siglalotacao_caixa_unidade = $CaixasUnidadeAcesso[0]['LOTA_SIGLA_LOTACAO'];
            $desclotacao_caixa_unidade = $CaixasUnidadeAcesso[0]['LOTA_DSC_LOTACAO'];
        }
        
        $this->setMatricula($matricula);
        $this->setSgsecaoCaixaUnidade($sgsecao_caixa_unidade);
        $this->setCdlotacaoCaixaUnidade($cdlotacao_caixa_unidade);
        $this->setSiglaLotacaoCaixaUnidade($siglalotacao_caixa_unidade);
        $this->setDescLotacaoCaixaUnidade($desclotacao_caixa_unidade);
        
    }
    /**
     *
     * @param type $matricula
     * @return type array
     * @abstract Obtendo as caixas que os usuários tem acesso pela permissão PERF_ID_PERFIL = 9 RESPONSÁVEL PELA CAIXA DA UNIDADE
     */
    public function getAcessoCaixaUnidade($matricula){
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $ResponsávelCaixaUnidade = $OcsTbUnpeUnidadePerfil->getResponsávelCaixaUnidade($matricula);
        return $ResponsávelCaixaUnidade;
    }
    
    public function getAcessoCaixaUnidadePessoal($matricula){
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $ResponsávelCaixaUnidade = $OcsTbUnpeUnidadePerfil->getResponsavelCaixaUnidadePessoal($matricula);
        return $ResponsávelCaixaUnidade;
    }
    
    public function getAcessoCaixaUnidadeExtinta($matricula) {
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $resp = $OcsTbUnpeUnidadePerfil->getResponsavelCaixaUnidadeExtinta($matricula);
        return $resp;
    }
}

?>
