<?php
/**
 * Controle de permissões do sistema e-Eleição modificado para o sistema e-Admin
 * Por: Marcelo Caixeta Rocha
 */

class App_Acl extends Zend_Acl 
{
    private static $_permissaoUsu = '';
    private static $_matriculaUsu = '';
    private static $_authResult = '';

    public function __construct(Zend_Auth $auth)
    {
        $this->_addRoles();
        $this->_addResources();
        $this->_addPermissions();
    }

    public function _addRoles()
    {
        $this->addRole(new Zend_Acl_Role('guest'));
        $this->addRole(new Zend_Acl_Role('usuario'));
    }

    public function _addResources()
    {
        $this->add(new Zend_Acl_Resource('default:config')); 
        $this->add(new Zend_Acl_Resource('default:error'));
        $this->add(new Zend_Acl_Resource('default:index'));
        $this->add(new Zend_Acl_Resource('default:login'));
        try {
            self::$_permissaoUsu = new Application_Model_DbTable_OcsTbAspasPapelSistema();
            foreach (self::$_permissaoUsu->getResourcesAclNivelController() as $p) {
                $this->add(new Zend_Acl_Resource(strtolower($p['MODL_NM_MODULO']).':'.strtolower($p['CTRL_NM_CONTROLE_SISTEMA'])));
            }
            foreach (self::$_permissaoUsu->getResourcesAclNivelAction() as $p) {
                $this->add(new Zend_Acl_Resource(strtolower($p['MODL_NM_MODULO']).':'.strtolower($p['CTRL_NM_CONTROLE_SISTEMA']).'.'.strtolower($p['ACAO_NM_ACAO_SISTEMA'])));
            } 
            self::$_authResult = true;
        } catch (Exception $e) {
            self::$_authResult = $e;
        }
        return self::$_authResult;
    }

    public function _addPermissions ()
    {
        $this->deny();
        $this->allow(null,'default:config');
        $this->allow(null,'default:error');
        $this->allow(null,'default:index');
        $this->allow(null,'default:login');
        $this->allow(null,'guardiao:index.carregapermissao');
        try {
            self::$_permissaoUsu = new Application_Model_DbTable_OcsTbAspasPapelSistema();
            self::$_matriculaUsu = new Zend_Session_Namespace('userNs');
            /**
             * Carregar todas as controllers que o usuário tem acesso
             */
            foreach (self::$_permissaoUsu->getPermissionsAclNivelController(self::$_matriculaUsu->matricula) as $p) {
                $modulo = strtolower($p['MODL_NM_MODULO']);
                $controle = strtolower($p['CTRL_NM_CONTROLE_SISTEMA']);
                $this->allow('usuario',$modulo.':'.$controle);
            }
            /**
             * Carregar todas as ações que o usuário tem acesso
             */
            foreach (self::$_permissaoUsu->getPermissionsAclNivelAction(self::$_matriculaUsu->matricula) as $p) {
                $modulo = strtolower($p['MODL_NM_MODULO']);
                $controle = strtolower($p['CTRL_NM_CONTROLE_SISTEMA']);
                $acao = strtolower($p['ACAO_NM_ACAO_SISTEMA']);
                $this->allow('usuario',$modulo.':'.$controle.'.'.$acao);
            }
            /**
             * Negar as permissões nas actions que o usuário não pode acessar
             */
            foreach (self::$_permissaoUsu->getNotAccess(self::$_matriculaUsu->matricula) as $p) {
                $modulo = strtolower($p['MODL_NM_MODULO']);
                $controle = strtolower($p['CTRL_NM_CONTROLE_SISTEMA']);
                $acao = strtolower($p['ACAO_NM_ACAO_SISTEMA']);
                $this->deny('usuario',$modulo.':'.$controle,array($controle, $acao));
            }
            $this->allow(null,'guardiao:index');
            self::$_authResult = true;
        } catch (Exception $e) {
            self::$_authResult = $e;
        }
        return self::$_authResult;
    }
    
}