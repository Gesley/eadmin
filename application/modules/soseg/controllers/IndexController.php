<?php

class Soseg_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
	$this->view->titleBrowser = 'e-Soseg - Sistema de Atendimento aos Serviços Editoriais e Gráficos';
    }

    public function indexAction()
    {
        $this->view->title = 'Seja Bem-Vindo ao Sistema e-Soseg!';
        /*$userNs = new Zend_Session_Namespace('userNs');
        
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $arrayPerfis = $ocsTbPupePerfilUnidPessoa->getPerfilUnidadePessoa($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula);
        
        foreach ($arrayPerfis as $perfil) {
            if($perfil["PERF_ID_PERFIL"] == 54 && $perfil["PUPE_ID_UNIDADE_PERFIL"] != NULL){
                $this->view->dashboardSosti = 'ATIVO';
                $this->view->title = 'Dashboard Sosti';
            }else {
                
                $this->view->title = 'Seja Bem-Vindo ao Sistema e-Soseg!';
            }
        }*/
    }
}