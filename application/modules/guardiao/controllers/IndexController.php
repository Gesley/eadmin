<?php

class Guardiao_IndexController extends Zend_Controller_Action
{
	/**
	 * Timer para mensuracao do tempo de carregamento da pagina
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da pagina
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Guardião";
    }

    public function indexAction()
    {
		$this->view->title = "Seja Bem-Vindo ao Sistema e-Guardião!";
    }
    
    public function carregapermissaoAction()
    {
        
        /******************* CARREGA PERMISSÕES AUTOMÁTICAS */
        $userNs = new Zend_Session_Namespace('userNs');
        
        $matricula = $userNs->matricula;
        
        
        $matricula = strtoupper($matricula);
        $matricula_prefixo = substr($matricula, 0, 2);
        $matricula_sufixo = substr($matricula, 2);
        $matricula_prefixo[0]; 
        
        
        $matricula_sufixo_arr = str_split($matricula_sufixo);
        $matricula_final = '';
        foreach ($matricula_sufixo_arr as $value) {
            if(!is_numeric($value)){
                $matricula_final = $matricula_final.$value;
            }
        }
        if($matricula_final==''){
            
           switch ($matricula_prefixo):
                case 'DS':
                    $perfil = 'MAGISTRADO';
                break;
                case 'JU':
                    $perfil = 'MAGISTRADO';
                break;
                default :
                    if(!is_numeric($matricula_prefixo[0]) && !is_numeric($matricula_prefixo[1]) ){
                        $perfil = 'SERVIDOR';
                    }else{
                        $perfil = '';
                    }
            endswitch;
                
        }else{
            switch ($matricula_final):
                case 'PS':
                    $perfil = 'PRESTADOR DE SERVIÇO';
                break;
                case 'ES':
                    $perfil = 'ESTAGIÁRIO';
                break;
                case 'VO':
                    $perfil = 'VOLUNTÁRIO';
                break;
                case 'CO':
                    $perfil = 'CONCILIADOR';
                break;
                case 'BO':
                    $perfil = 'BOLSISTA';
                break;
                default :
                    $perfil = '';
            endswitch;
        }
        
        if($perfil==''){
            //echo 'matricula inválida';
        }else{
            //echo $perfil;
        }
        
        $OcsTbPerfPerfil = new Application_Model_DbTable_OcsTbPerfPerfil();
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $OcsTbUnpeAuditoria = new Application_Model_DbTable_OcsTbUnpeAuditoria();
        $OcsTbPepePerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $OcsTbPepeAuditoria = new Application_Model_DbTable_OcsTbPepeAuditoria();
        
        
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $OcsTbPupeAuditoria = new Application_Model_DbTable_OcsTbPupeAuditoria();
        
        
        if($perfil!= ''){
            $PerfPerfil = $OcsTbPerfPerfil->fetchRow("PERF_DS_PERFIL = '$perfil'")->toArray();
            //Zend_Debug::dump($PerfPerfil);
            
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            try {
                $db->beginTransaction();
            
                /**
                 * VERIFICA SE A LOTAÇÃO POSSUI A PERMISSÃO PADRÃO
                 * 
                 */
                $UnidadePerfil = $OcsTbUnpeUnidadePerfil->fetchAll("UNPE_ID_PERFIL = $PerfPerfil[PERF_ID_PERFIL] AND UNPE_SG_SECAO = '$userNs->siglasecao' AND UNPE_CD_LOTACAO = $userNs->codlotacao ")->toArray();
                $idUnidadePerfil = $UnidadePerfil[0]["UNPE_ID_UNIDADE_PERFIL"];
                
                if( !isset($UnidadePerfil[0]["UNPE_ID_UNIDADE_PERFIL"]) &&  !$UnidadePerfil[0]["UNPE_ID_UNIDADE_PERFIL"] ){

                    unset($dataUnpeUnidadePerfil["UNPE_ID_UNIDADE_PERFIL"]); 
                    $dataUnpeUnidadePerfil["UNPE_SG_SECAO"] = $userNs->siglasecao; 
                    $dataUnpeUnidadePerfil["UNPE_CD_LOTACAO"] = $userNs->codlotacao; 
                    $dataUnpeUnidadePerfil["UNPE_ID_PERFIL"] = $PerfPerfil["PERF_ID_PERFIL"]; 
                    $rowUnidadePerfil = $OcsTbUnpeUnidadePerfil->createRow($dataUnpeUnidadePerfil);
                    $idUnidadePerfil = $rowUnidadePerfil->save();
                    $dataAuditoriaNew['UNPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                    $dataAuditoriaNew['UNPE_CD_OPERACAO'] = 'I';
                    $dataAuditoriaNew['UNPE_CD_MATRICULA_OPERACAO'] = 'AUTOMÁTICO';
                    $dataAuditoriaNew['UNPE_CD_MAQUINA_OPERACAO'] = 'NOME_MAQUINA';
                    $dataAuditoriaNew['UNPE_CD_USUARIO_SO'] = 'NOME_USER_SO';
                    $dataAuditoriaNew['OLD_UNPE_ID_UNIDADE_PERFIL'] = 0;
                    $dataAuditoriaNew['NEW_UNPE_ID_UNIDADE_PERFIL'] = $idUnidadePerfil;
                    $dataAuditoriaNew['OLD_UNPE_SG_SECAO'] = 0;
                    $dataAuditoriaNew['NEW_UNPE_SG_SECAO'] = $userNs->siglasecao;;
                    $dataAuditoriaNew['OLD_UNPE_CD_LOTACAO'] = 0;
                    $dataAuditoriaNew['NEW_UNPE_CD_LOTACAO'] = $userNs->codlotacao;
                    $dataAuditoriaNew['OLD_UNPE_ID_PERFIL'] = 0;
                    $dataAuditoriaNew['NEW_UNPE_ID_PERFIL'] = $PerfPerfil["PERF_ID_PERFIL"];
                    $row = $OcsTbUnpeAuditoria->createRow($dataAuditoriaNew);
                    $row->save();
                }
                
                 /**
                 * VERIFICA SE A PESSOA POSSUI A PERMISSÃO PADRÃO
                 * 
                 */
                $Pessoa = $OcsTbPepePerfilPessoa->fetchAll("PEPE_ID_PERFIL = $PerfPerfil[PERF_ID_PERFIL] AND PEPE_CD_MATRICULA = '$matricula' ")->toArray();

                if(!$Pessoa){
                        unset($dataPepePerfilPessoa["PEPE_ID_PERFIL_PESSOA"]); 
                        $dataPepePerfilPessoa["PEPE_CD_MATRICULA"] = $matricula; 
                        $dataPepePerfilPessoa["PEPE_ID_PERFIL"] = $PerfPerfil["PERF_ID_PERFIL"];  ;
                        $rowPerfilPessoa = $OcsTbPepePerfilPessoa->createRow($dataPepePerfilPessoa);
                        $idsalvo = $rowPerfilPessoa->save();
                        $data_audit['PEPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                        $data_audit['PEPE_CD_OPERACAO'] = 'I';
                        $data_audit['PEPE_CD_MATRICULA_OPERACAO'] = 'AUTOMÁTICO';
                        $data_audit['PEPE_CD_MAQUINA_OPERACAO'] = 'SERVIDOR WEB';
                        $data_audit['PEPE_CD_USUARIO_SO'] = 'SERVIDOR WEB';
                        $data_audit['OLD_PEPE_ID_PERFIL_PESSOA'] = 0;
                        $data_audit['NEW_PEPE_ID_PERFIL_PESSOA'] = $idsalvo;
                        $data_audit['OLD_PEPE_CD_MATRICULA'] = 0; 
                        $data_audit['NEW_PEPE_CD_MATRICULA'] = $matricula;
                        $data_audit['OLD_PEPE_ID_PERFIL'] = 0;
                        $data_audit['NEW_PEPE_ID_PERFIL'] = $PerfPerfil["PERF_ID_PERFIL"];

                        $row = $OcsTbPepeAuditoria->createRow($data_audit);
                        $row->save();
                        
                }
                
                 /**
                 * VERIFICA SE A PESSOA POSSUI A PERMISSÃO PADRÃO
                 * 
                 */
                $Pessoa_pupe = $OcsTbPupePerfilUnidPessoa->fetchAll("PUPE_ID_UNIDADE_PERFIL = $idUnidadePerfil AND PUPE_CD_MATRICULA = '$matricula' ")->toArray();
                
                if( !isset($Pessoa_pupe[0]["PUPE_ID_UNIDADE_PERFIL"]) &&  !$Pessoa_pupe[0]["PUPE_ID_UNIDADE_PERFIL"] ){
                    
                    
                        $dataPupePerfilUnidPessoa["PUPE_ID_UNIDADE_PERFIL"] =  $idUnidadePerfil; 
                        $dataPupePerfilUnidPessoa["PUPE_CD_MATRICULA"] = $matricula; 
                        $rowPupePerfilUnidPessoa = $OcsTbPupePerfilUnidPessoa->createRow($dataPupePerfilUnidPessoa);
                        

                        $rowPupePerfilUnidPessoa->save();
                        
                        
                        $data_audit_pupe['PUPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                        $data_audit_pupe['PUPE_CD_OPERACAO'] = 'I';
                        $data_audit_pupe['PUPE_CD_MATRICULA_OPERACAO'] = 'AUTOMÁTICO';
                        $data_audit_pupe['PUPE_CD_MAQUINA_OPERACAO'] = 'SERVIDOR WEB';
                        $data_audit_pupe['PUPE_CD_USUARIO_SO'] = 'SERVIDOR WEB';
                        
                        $data_audit_pupe['OLD_PUPE_ID_UNIDADE_PERFIL'] = 0;
                        $data_audit_pupe['NEW_PUPE_ID_UNIDADE_PERFIL'] = $PerfPerfil[PERF_ID_PERFIL];
                        
                        $data_audit_pupe['OLD_PUPE_CD_MATRICULA'] = 0; 
                        $data_audit_pupe['NEW_PUPE_CD_MATRICULA'] = $matricula; 

                        $row = $OcsTbPupeAuditoria->createRow($data_audit_pupe);
                        $row->save();
                        
                }
                
                
                if ( $perfil == 'SERVIDOR' || $perfil == 'MAGISTRADO' ){

                    $matricula = $userNs->matricula;
                    $perfil =  9;

                    $sgsecao = $userNs->siglasecao;
                    $cdlotacao = $userNs->codlotacao;
                    
                    $UnidadePerfil = $OcsTbUnpeUnidadePerfil->fetchAll("UNPE_ID_PERFIL = $perfil AND UNPE_SG_SECAO = '$sgsecao' AND UNPE_CD_LOTACAO = $cdlotacao ")->toArray();
                    $idUnidadePerfil = $UnidadePerfil[0]["UNPE_ID_UNIDADE_PERFIL"];

                    if( !isset($UnidadePerfil[0]["UNPE_ID_UNIDADE_PERFIL"]) &&  !$UnidadePerfil[0]["UNPE_ID_UNIDADE_PERFIL"] ){

                        unset($dataUnpeUnidadePerfil["UNPE_ID_UNIDADE_PERFIL"]); 
                        $dataUnpeUnidadePerfil["UNPE_SG_SECAO"] = $sgsecao; 
                        $dataUnpeUnidadePerfil["UNPE_CD_LOTACAO"] = $cdlotacao; 
                        $dataUnpeUnidadePerfil["UNPE_ID_PERFIL"] = $perfil; 
                        $rowUnidadePerfil = $OcsTbUnpeUnidadePerfil->createRow($dataUnpeUnidadePerfil);
                        $idUnidadePerfil = $rowUnidadePerfil->save();
                    }

                    $Pessoa_pupe = $OcsTbPupePerfilUnidPessoa->fetchAll("PUPE_ID_UNIDADE_PERFIL = $idUnidadePerfil AND PUPE_CD_MATRICULA = '$matricula' ")->toArray();
                    if( !isset($Pessoa_pupe[0]["PUPE_ID_UNIDADE_PERFIL"]) &&  !$Pessoa_pupe[0]["PUPE_ID_UNIDADE_PERFIL"] ){

                        $dataPupePerfilUnidPessoa["PUPE_ID_UNIDADE_PERFIL"] =  $idUnidadePerfil; 
                        $dataPupePerfilUnidPessoa["PUPE_CD_MATRICULA"] = $matricula; 
                        $rowPupePerfilUnidPessoa = $OcsTbPupePerfilUnidPessoa->createRow($dataPupePerfilUnidPessoa);
                        $rowPupePerfilUnidPessoa->save();

                    }
                }
                
                $db->commit();
                
                
            } catch (Exception $exc) {
                    $db->rollBack();
                    
                    $erro = $exc->getMessage();
//        Zend_Debug::dump($erro );exit;
                    $msg_to_user = "Erro ao Carregar suas permissões. <br/> $erro";
                    
                    //$this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    //return $this->_helper->_redirector('index','login','default'); 
                    
                    echo "Erro ao Carregar suas permissões. <br/> $erro";
            }
        }
        
        /*******************FIM ---- CARREGA PERMISSÕES AUTOMÁTICAS */
        $initNs = new Zend_Session_Namespace('initNs');
        $urlUsuario = explode('/', $initNs->url);
        $posModule = array_search('sosti', $urlUsuario);
        if ($posModule) {
            return $this->_helper->_redirector($urlUsuario[$posModule + 2], $urlUsuario[$posModule + 1], 'sosti');
        }
        return $this->_helper->_redirector('index','index','admin');
    }
    
    public function assinarAction()
    {
        /*****************ATENÇÃO!!!!: ESTA FUNCTION É USADA SOMENTE PARA TESTE !!!!!*****************/
//        try{
//            
//            $vem_do_post['UNPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
//            $vem_do_post['UNPE_CD_OPERACAO'] = 'A';
//            $vem_do_post['UNPE_CD_MATRICULA_OPERACAO'] = 'TR18260PS';
//            $vem_do_post['UNPE_CD_MAQUINA_OPERACAO'] = 'NOME_MAQUINA';
//            $vem_do_post['UNPE_CD_USUARIO_SO'] = 'NOME_USER_SO';
//            $vem_do_post['OLD_UNPE_ID_UNIDADE_PERFIL'] = 00;
//            $vem_do_post['NEW_UNPE_ID_UNIDADE_PERFIL'] = 4;
//            $vem_do_post['OLD_UNPE_SG_SECAO'] = 00;
//            $vem_do_post['NEW_UNPE_SG_SECAO'] = TR;
//            $vem_do_post['OLD_UNPE_CD_LOTACAO'] = 00;
//            $vem_do_post['NEW_UNPE_CD_LOTACAO'] = 1155;
//            $vem_do_post['OLD_UNPE_ID_PERFIL'] = 00;
//            $vem_do_post['NEW_UNPE_ID_PERFIL'] = 12;
//            
//            $OcsTbUnpeAuditoria = new Application_Model_DbTable_OcsTbUnpeAuditoria();
//            $row = $OcsTbUnpeAuditoria->fetchAll("NEW_UNPE_ID_UNIDADE_PERFIL = 4")->toArray();
//            if($row){
//                $dataAuditoriaOLD = $row;
////                Zend_Debug::dump($dataAuditoriaOLD);
//                $hora = $dataAuditoriaOLD['UNPE_TS_OPERACAO'];
//                $dataAuditoriaNew['UNPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
//                $dataAuditoriaNew['UNPE_CD_OPERACAO'] = 'A';
//                $dataAuditoriaNew['UNPE_CD_MATRICULA_OPERACAO'] = 'TR18260PS';
//                $dataAuditoriaNew['UNPE_CD_MAQUINA_OPERACAO'] = 'NOME_MAQUINA';
//                $dataAuditoriaNew['UNPE_CD_USUARIO_SO'] = 'NOME_USER_SO';
//                $dataAuditoriaNew['OLD_UNPE_ID_UNIDADE_PERFIL'] = $dataAuditoriaOLD[0]['NEW_UNPE_ID_UNIDADE_PERFIL'];
//                $dataAuditoriaNew['NEW_UNPE_ID_UNIDADE_PERFIL'] = $vem_do_post['NEW_UNPE_ID_UNIDADE_PERFIL'];
//                $dataAuditoriaNew['OLD_UNPE_SG_SECAO'] = $dataAuditoriaOLD[0]['NEW_UNPE_SG_SECAO'];
//                $dataAuditoriaNew['NEW_UNPE_SG_SECAO'] = $vem_do_post['NEW_UNPE_SG_SECAO'];
//                $dataAuditoriaNew['OLD_UNPE_CD_LOTACAO'] = $dataAuditoriaOLD[0]['NEW_UNPE_CD_LOTACAO'];
//                $dataAuditoriaNew['NEW_UNPE_CD_LOTACAO'] = $vem_do_post['NEW_UNPE_CD_LOTACAO'];
//                $dataAuditoriaNew['OLD_UNPE_ID_PERFIL'] = $dataAuditoriaOLD[0]['NEW_UNPE_ID_PERFIL'];
//                $dataAuditoriaNew['NEW_UNPE_ID_PERFIL'] = $vem_do_post['NEW_UNPE_ID_PERFIL'];
////                Zend_Debug::dump($dataAuditoriaNew);
////                exit;
//                $row = $OcsTbUnpeAuditoria->createRow($dataAuditoriaNew);
//                $row->save();
//            }else{
//                //$row = $OcsTbUnpeAuditoria->createRow($dataAuditoriaNew);
//                //$row->save();
//            }
//
//            Zend_Debug::dump($dataAuditoriaNew);
//            Zend_Debug::dump($row);
//            exit;
//        }catch(Exception $e){
//            echo $e;
//        }
    }
    
    public function aplicacoesnaocadastradasAction()
    {    
        $this->view->title = "Aplicações não Cadastradas";
        $a = $this->_helper->AssetsList->getList();
        foreach($a as $list1=>$counter1) {
            foreach($counter1 as $list2=>$counter2) {
                foreach($counter2 as $list3=>$counter3) {
                    $app[] = $list2.'_'.$counter3;
                }
            }
        }
        
        $permissao = new Application_Model_DbTable_OcsTbAspasPapelSistema();
        $cad = $permissao->getAppsCadastradas();
        foreach ($cad as $k=>$v) {
            $cad[] = $v["MODL_NM_MODULO"]."_".$v["CTRL_NM_CONTROLE_SISTEMA"]."_".$v["ACAO_NM_ACAO_SISTEMA"];
        }
        
        $todas_apps = array_unique($app);
        $cadastradas_apps = array_unique($cad);
        unset ($cadastradas_apps[0]);
        $dados = array_diff($todas_apps, $cadastradas_apps);
        foreach ($dados as $i=>$d) {
            if (substr_count($d, '_') > 1) {
                $data[] = $d;
            }
        }
        /**
         * Aplicações que ainda não foram cadastradas
         */
        $this->view->data = $data;
    }
    
    public function sessionAction()
    {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        $this->_helper->json->sendJson(array('success' => $authNamespace->timeout));
    }
    
}
