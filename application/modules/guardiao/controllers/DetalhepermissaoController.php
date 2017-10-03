<?php

class Guardiao_DetalhepermissaoController extends Zend_Controller_Action {
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
	
    public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Guardião - Sistema de Gerenciamento de Permissões';
    }

    public function indexAction() {
        
    }

    /**
     * Controle de visualização do detalhe de solicitação
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function detalhepermissaoAction() {
        
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            
            if (!is_numeric($data['LOTA_COD_LOTACAO'])) {
                
                $arrayAux = explode('|', $data['LOTA_COD_LOTACAO']);
                
                if (count($arrayAux) == 2) {
                    $data['LOTA_COD_LOTACAO'] = $arrayAux[1];
                    $data['LOTA_SIGLA_SECAO'] = $arrayAux[0];
                    
                } else {
                    $arrayAux = Zend_Json::decode($data['LOTA_COD_LOTACAO']);
                    $data['LOTA_COD_LOTACAO'] = $arrayAux['LOTA_COD_LOTACAO'];
                    $data['LOTA_SIGLA_SECAO'] = $arrayAux['LOTA_SIGLA_SECAO'];
                }
            }
            
            if ($data['PUPE_CD_MATRICULA']) {
                $arrayAux = explode(' - ', $data['PUPE_CD_MATRICULA']);
                //pega sempre a parte da matricula. Se não for composto com nome a matricula será também a posição 0
                $data['PMAT_CD_MATRICULA'] = $arrayAux[0];
            }elseif($data['PMAT_CD_MATRICULA']){
                $arrayAux = explode(' - ', $data['PMAT_CD_MATRICULA']);
                //pega sempre a parte da matricula. Se não for composto com nome a matricula será também a posição 0
                $data['PMAT_CD_MATRICULA'] = $arrayAux[0];
            }elseif($data['RESPCAIXA_CD_MATRICULA']){
                $arrayAux = explode(' - ', $data['RESPCAIXA_CD_MATRICULA']);
                //pega sempre a parte da matricula. Se não for composto com nome a matricula será também a posição 0
                $data['PMAT_CD_MATRICULA'] = $arrayAux[0];
            }
            
            $rn_permissao = new Trf1_Guardiao_Negocio_Permissao();
            $arrayDetalhe['historico_permissao'] = $rn_permissao->getHistoricoPermissao(array(
                'PMAT_CD_MATRICULA' => $data['PMAT_CD_MATRICULA']
                , 'UNPE_SG_SECAO' => $data['LOTA_SIGLA_SECAO']
                , 'UNPE_CD_LOTACAO' => $data['LOTA_COD_LOTACAO']));          
            
            $this->view->arrayDetalhe = $arrayDetalhe;
        }
    }

}
