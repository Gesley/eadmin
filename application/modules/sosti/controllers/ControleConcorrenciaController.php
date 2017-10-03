<?php
/**
 * O Controller cria uma instancia de SostiFactoryFacade para trazer a Facade
 * com o método que lista as solicitações com controle de concorrência para
 * serem realizadas as exclusões quando for necessário.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_ControleConcorrenciaController extends Zend_Controller_Action 
{

    public function init() 
    {
        $this->facade = Trf1_Factory_Facade::createInstance(
            'Sosti_Facade_ControleConcorrencia'
        );
    }

    public function indexAction() 
    {
        $moduleControllerAction = $this->_helper->moduleControllerAction();
        $filtroSessao = new Zend_Session_Namespace($moduleControllerAction.'_filtroNs');
        /**
         * Grava o resultado do post na sessão
         */
        if ($this->getRequest()->isPost()) {
            $filtroSessao->post = $this->getRequest()->getPost();
        }
        $postParam = $filtroSessao->post;
        $param = $postParam["GRUPO"];
        $nivel = $postParam["NIVEL"];
        $grupo = explode('|', $param);
        /**
         * Lista as solicitações
         */
        $data = $this->facade->listBusiness(
            $grupo[1], $grupo[2], $nivel, $filtroSessao->order
        );
        $gridConfig = $this->_helper->gridConfigPaginator(
            array('direction' => 'DESC',
                  'order'     => 'DATA_ACAO'),
            $data
        );
        $ordenacao = $gridConfig['ordenacao'];
        $filtroSessao->order = $ordenacao['order'];
        /**
         * Formulário do filtro
         */
        $form = new Sosti_Form_PesquisaListaCaixaAcessoUsuario();
        $form->populate($filtroSessao->post);
        /**
         * Manda para a view
         */
        $this->view->assign(array(
            'title'                  => "Desbloquear Solicitações",
            'form'                   => $form,
            'nivel'                  => $nivel ? $nivel : '',
            'ordem'                  => $ordenacao['orderColumn'],
            'direcao'                => $ordenacao['direction'],
            'arrayRowset'            => $gridConfig["dataPaginator"],
            'moduleControllerAction' => $moduleControllerAction
        ));
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function deletarAction()
    {
        if ($this->facade->deleteBusiness($this->_getParam('id'))) {
            $this->_helper->flashMessenger(array(
                'message' => "Solicitação desbloqueada.",'status'  => 'success'
            ));
            $this->_helper->_redirector('index', 'controle-concorrencia', 'sosti');
        }
    }
    
    public function jsonNivelAtendimentoAction() 
    {
        $param = $this->_getParam('caixa');
        $niveis = $this->facade->niveisAtendimentoPorCaixaBusiness($param);
        $this->_helper->json->sendJson($niveis);
    }
    
    public function geradbAction()
    {
        
    }

}
