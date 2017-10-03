<?php

/**
 * Retorna todos os dados para serem inclusos nos recursos e nos privilégios
 * dentro da estrutura do ACL.
 * 
 * Dentro de $usuario deverá incluir todos os que deverão receber privilégios.
 * 
 * Exemplo $usuario:
 * array("dipor", "desenvolvedor", "consulta")
 * 
 * Dentro de $controller a chave do array é o nome do controller e o valor
 * é um array com todas as ações utilizadas. Por padrão a index não é
 * necessário ser colocado devido a toda paǵina ter uma index.
 * 
 * Exemplo $controller: 
 * array("alinea" => array("incluir", "editar", "excluir"));
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */
abstract class Orcamento_Business_Acl_Base {

    protected static $usuario = array();
    protected static $controller = array();
    protected static $ini = false;

    /**
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     * @return mixed array
     */
    public static function retornarUsuario() {
        if (self::$ini === false) {
            static::inicializar();
            self::$ini = true;
        }

        return static::$usuario;
    }

    /**
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     * @return mixed array
     */
    public static function retonarController() {
        if (self::$ini === false) {
            static::inicializar();
            self::$ini = true;
        }
        
//        Zend_Debug::dump(static::$controller); exit;

        return static::$controller;
    }

    /**
     * Deverá implementar nesse método as variáveis estáticas:
     * $usuario e $controller
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    abstract public static function inicializar();

    /**
     * Cria todos os privilégios para o Acl.
     * Repassar $this no parâmetro Acl.
     * 
     * @param Zend_Acl $acl
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public static function criarPrivilegios($acl) {

        $controller = self::retonarController();
        $usuario = self::retornarUsuario();
        
        foreach ($usuario as $nomeUsuario) {

            foreach ($controller as $nomeController => $acoesController) {
                
                $acl->allow($nomeUsuario, $nomeController);

                foreach ($acoesController as $acao) {

                    if ("consulta" === $nomeUsuario) {
                        $acl->deny($nomeUsuario, "{$nomeController}:{$acao}");
                    } else {
                        $acl->allow($nomeUsuario, "{$nomeController}:{$acao}");
                    }
                    
                }
            }
        }
        
    }
    
    /**
     * Cria todos os recursos para o Acl.
     * Repassar $this no parâmetro Acl.
     * 
     * @param Zend_Acl $acl
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public static function criarRecursos($acl) {

        $controller = self::retonarController();
        
        foreach($controller as $nomeController => $acao) {

            $acl->add(new Zend_Acl_Resource($nomeController));
            $acl->add(new Zend_Acl_Resource("{$nomeController}:index"), $nomeController);
            
            foreach($acao as $nomeAcao) {
                $acl->add(new Zend_Acl_Resource("{$nomeController}:{$nomeAcao}"), $nomeController);
            }
        }

    }

}
