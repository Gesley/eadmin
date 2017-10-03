<?php

class Orcamento_Business_Plugin_Logdados extends Zend_Controller_Plugin_Abstract
{

    /**
     * Verifica a requisição e grava no banco de dados de log do orçamento
     *
     * @param      Zend_Controller_Request_Abstract  $requisicao  The requisicao
     */
    public function preDispatch ( Zend_Controller_Request_Abstract $requisicao )
    {
        $router = new Zend_Controller_Router_Rewrite();
        $request = new Zend_Controller_Request_Http();
        $router->route($request);

        // Verifica se a requisicao do usuario é post
        if( $request->isPost() ){

            $modelLog = new Orcamento_Business_Negocio_Logdados ();

            $sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );

            $dados = array(
                'controller' => $request->getControllerName(),
                'action' => $request->getActionName(),
                'ug' => strtoupper($sessaoOrcamento->ug),
                'matricula' => strtoupper($sessaoOrcamento->usuario),
            );

            $res = $modelLog->incluirLog($dados);
        }
    }
}
