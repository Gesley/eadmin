<?php
class App_View_Helper_Tools extends Zend_View_Helper_Abstract
{
    public $view;
    protected $_id;
    protected $_url;
    protected $_send = false;
    protected $_validate = true;
    
    /*
    protected $_jsOnlyValidate;
    protected $_jsOnlySend;
    protected $_jsValidateAndSend;
    */  
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    public function Tools()
    {
//        $link_alterar = $this->url(array('module'=> 'default', 'controller' => 'candidato', 'action' => 'alterar'));
//        $link_logout  = $this->url(array('module'=> 'default', 'controller' => '', 'action' => ''));
//        $link_senha   = $this->url(array('module'=> 'default', 'controller' => '', 'action' => ''));

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()){
            $aNamespace = new Zend_Session_Namespace('userNs');
            $retorno =  ' <div id="username">' . $aNamespace->nome . '</div>
                            <div id="alterar"><a href="admin/alterardados">Alterar Dados</a></div>
                            <div id="senha"><a href="admin/alterarsenha">Alterar Senha</a></div>
                            <div id="sair"><a href="login/logout">Sair</a></div>
                            ';
        } else {
            $retorno = '';
        }
        return $retorno;
    }
}