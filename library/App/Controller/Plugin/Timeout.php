<?php

class App_Controller_Plugin_Timeout extends Zend_Controller_Plugin_Abstract {

    protected $_auth = null;
    protected $_acl = null;
    protected $_flashMessenger = null;
    protected static $_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS;

    public function __construct () {
        $savehandle = Zend_Session::getSaveHandler();
//        self::$_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS =  $savehandle->getLifetime();
        self::$_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS = 14400;
    }

    public function preDispatch (Zend_Controller_Request_Abstract $request) {
        /**
         * Pegando o helper Redirector
         */
        $this->_redirect = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');

        /*
         * Instanciando as variáveis de sessão do zend_auth
         */
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');

        /**
         * Copiando em variáves o modulo, controlle e action
         */
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());
        $action = strtolower($request->getActionName());

        /**
         * Se o usuário estiver autenticado
         */
        if (Zend_Auth::getInstance()->hasIdentity()) {

            if (( isset($authNamespace->timeout) && time() > $authNamespace->timeout)) {
                /**
                 * limpa a identidade do usuário que está um longo período sem acessar o controller
                 */
                $request->setModuleName('default');
                $request->setControllerName('login');
                $request->setActionName('logout');
                $authNamespace->erro = 'Sua sessão expirou, favor logar novamente';
            } else {
                /**
                 *  Usuário está ativo - atualizamos o time da sessão.
                 */
                $authNamespace->timeout = strtotime(self::$_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS . " seconds");
                /**
                 * Renovando o timeout das variáves de sessão
                 */
                $namesspaces = Zend_Session::getIterator();
                $namesspacesArrayCopy = $namesspaces->getArrayCopy();
                foreach ($namesspacesArrayCopy as $namesspace) {
                    $namesspace_each = new Zend_Session_Namespace($namesspace);
                    //$namesspace_each->setExpirationSeconds(self::$_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS);
                    $namesspace_each->timeout = strtotime(self::$_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS . " seconds");
                    $temp = $namesspace_each->timeout;
                }
            }
        }

        /** Se o usuário não possuir identidade ou a identidade foi removida devido ao timeout,
         * redirecionamos ele para a tela de login.
         */
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            if (!($module == 'default' && $controller == 'login' && $action == 'index') &&
                !($module == 'default' && $controller == 'login' && $action == 'ajaxbanco')) {
                $request->setModuleName('default');
                $request->setControllerName('login');
                $request->setActionName('index');
                $request->setParam('sessao', 'expirada');
            }
            return;
        }
    }

    /**
     * @abstract Retorna o valor em segundos da propriedade estática _ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS
     * @return int em segundos 
     */
    public static function getNamespaceExpirationSeconds () {
        return( self::$_ZEND_SESSION_NAMESPACE_EXPIRATION_SECONDS );
    }

    /**
     * @abstract Grava na sessão o valor do timeout usado no plugin de custom view
     */
    public static function initTimeOutNamespace () {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        $authNamespace->timeout = strtotime(App_Controller_Plugin_Timeout::getNamespaceExpirationSeconds() . " seconds");
    }

}
