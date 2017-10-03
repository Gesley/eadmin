<?php

class App_Auth_Adapter_Db extends Zend_Auth_Adapter_DbTable /* implements Zend_Auth_Adapter_Interface */
{
    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;
    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;
    /**
     * $_dbName - Database name
     *
     * @var string
     */
    protected $_dbName = null;
    /**
     * $_resultRow - Results of database authentication query
     *
     * @var array
     */
    protected $_resultRow = null;

    public function __construct($identity = null, $credential = null, $dbName = null)
    {
        if (null !== $identity) {
            $this->setIdentity($identity);
        }
        if (null !== $credential) {
            $this->setCredential($credential);
        }
        if (null !== $dbName) {
            $this->setDbName($dbName);
        }
    }

    public function setIdentity($name)
    {
        $this->_identity = mb_strtoupper($name, 'UTF-8');
        return $this;
    }

    public function setCredential($pass)
    {
        $this->_credential = $pass;
        return $this;
    }

    public function setDbName($dbName)
    {
        $this->_dbName = $dbName;
        return $this;
    }

    public function authenticate($authAdapter = null)
    {
        /**
         * Verifica se o usuário escolheu um banco de dados
         * Caso não tenha escolhido nenhum por default o banco é TRF1
         */
        if ($this->_dbName === '0') {
            $this->_dbName = 'TRF1';
        }
        /**  
         * Está é a única requisição ao banco onde o sistema consulta
         *  Todas as senhas do usuário
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT COU_NM_BANCO, COU_COD_SECAO, COU_COD_MATRICULA,
                         COU_COD_PASSWORD, COU_COD_NOME, COU_ST_STATUS
                  FROM   CO_USER_ID
                  WHERE  COU_COD_MATRICULA = '".mb_strtoupper($this->_identity, 'UTF-8')."'";
        $stmt = $db->query($query);
        $this->_resultRow = $stmt->fetchAll();
        /**
         *  Se o usuário não estiver cadastrado na tabela CO_USER_ID não loga no sistema
         */
        if (count($this->_resultRow) === 0) {
            $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $authResult['messages'][] = 'Usuário não cadastrado';
            $authResult['identity'] = $this->_identity;
        } else {
         /**
          * Confere a senha digitada e o banco escolhidos pelo usuário
          * O sistema verifica no array se existe um hash com :
          *    - A senha digitada pelo usuário
          *    - A senha digitada e convertida para maiúsculo
          *    - A senha digitada e convertida para minúsculo
          */
            $log = false;
            $bancoUsuario = false;
            $senhaBloqueada = false;
            $hashnormal = md5($this->_credential);
            $hashmin = md5(mb_strtolower($this->_credential, 'UTF-8'));
            $hashmax = md5(mb_strtoupper($this->_credential, 'UTF-8'));

            foreach ($this->_resultRow as $r) {
               /**
                * Verifica se a senha e o banco estão no array
                */
                if (($r["COU_NM_BANCO"] === $this->_dbName) &&
                   (($r["COU_COD_PASSWORD"] === $hashnormal) ||
                    ($r["COU_COD_PASSWORD"] === $hashmax) ||
                    ($r["COU_COD_PASSWORD"] === $hashmin))) {
                    /**
                     * Verifica se a senha está ativa ou inativa
                     */
                    if ($r["COU_ST_STATUS"] == 1) {
                        $log = true;
                    } else {
                        $senhaBloqueada = true;
                    }
                }
               /**
                * Verifica se o usuário tem acesso aquele banco
                */
               if ($r["COU_NM_BANCO"] === $this->_dbName) {
                    $bancoUsuario = true;
                }
                /**
                 * Senha inválida e usuário inativo
                 */
               if (($r["COU_NM_BANCO"] === $this->_dbName)&&($r["COU_ST_STATUS"] == 2)) {
                    $senhaBloqueada = true;
               }
            }
            
            if ($log == true) {
                /**
                 * Sucesso na autenticação
                 */
                $authResult['code'] = Zend_Auth_Result::SUCCESS;
                $authResult['messages'][] = 'Login succesful direct';
                $authResult['messages'][] = $this->_resultRow[0]["COU_NM_BANCO"];
                $authResult['messages'][] = $this->_resultRow[0]["COU_COD_SECAO"];
                $authResult['identity'] = $this->_identity;
            } else {
                /**
                 * Tenta logar no banco de origem com o login, senha e banco informados pelo usuário
                 */
                $secoes = new App_Secoes();
                $secao = $secoes->getPorAlias(mb_strtoupper($this->_dbName, 'UTF-8'));
                $dbSucses = false;
                try {
                    $conn = @new PDO('oci:dbname=' . $secao->tns . ';charset=AL32UTF8', $this->_identity, mb_strtoupper($this->_credential, 'UTF-8'));
                    if ($conn == true) {
                       /**
                        * Sucesso na autenticação na base dados Forms
                        */ 
                        $dbSucses = true;
                    }
                } catch ( Exception $e ) {
                    $msgAux = explode(':', $e->getMessage());
                    $msg = explode('(', $msgAux[3]);
                    $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                    $authResult['messages'][] = $msg[0];
                    $authResult['identity'] = $this->_identity;
                }
                /**
                 * Verifica se o usuário logou no banco escolhido
                 */
                if ($dbSucses === true) {
                    
                    if($authAdapter == 'assinatura'){
                      /*Se estiver no sistema de assinatura - para aqui*/
                      $authResult['code'] = Zend_Auth_Result::SUCCESS;
                      $authResult['identity'] = $this->_identity;
                      $authResult['messages'][] = 'Login succesful'; 
                      $this->_resultRow = $authResult;
                      return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
                    }
                    /**
                     * Conexão com o banco de dados TRF1 para realizar operações de update da senha na CO_USER_ID no TRF1 ou no TRF1DSV
                     */
                    $resource = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getPluginResource('multidb');
                    $dbTrf1 = $resource->getDb('ocs');
                    Zend_Db_Table::setDefaultAdapter($dbTrf1);
                    /**
                     * Faz update da senha do usuário na tabela CO_USER_ID levando em consideração o banco escolhido
                     */
                    $banco = explode('|',$dbSucses);
                    $stmt = $dbTrf1->query("UPDATE CO_USER_ID SET COU_COD_PASSWORD = '".$hashmax."'
                                            WHERE COU_COD_MATRICULA = '".  mb_strtoupper($this->_identity, 'UTF-8')."'
                                            AND COU_NM_BANCO = '".mb_strtoupper($this->_dbName, 'UTF-8')."'
                                            AND COU_ST_STATUS = 1");

                    $authResult['code'] = Zend_Auth_Result::SUCCESS;
                    $authResult['identity'] = $this->_identity;
                    $authResult['messages'][] = 'Login succesful';
                    $authResult['messages'][] = $banco[0];
                    $authResult['messages'][] = $banco[1];
                    /**
                     * Retorna a conexão padrão para a conexão do guardião
                     */
                    $db = $resource->getDb('guardiao');
                    Zend_Db_Table::setDefaultAdapter($db);
                } else {
                    $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                    if($senhaBloqueada == true){
                         unset($authResult['messages']);
                        $authResult['messages'][] = 'Usuário inativo';
                    }
                    if($bancoUsuario == false){
                        unset($authResult['messages']);
                        $authResult['messages'][] = 'Usuário sem acesso ao banco de dados'; 
                    }
                    $authResult['identity'] = $this->_identity;
                }
            }
        }
        $this->_resultRow = $authResult;
        return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
    }
    
    public function verify()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COU_NM_BANCO, COU_COD_SECAO, COU_COD_MATRICULA,
                                   COU_COD_PASSWORD, COU_COD_NOME
                            FROM   CO_USER_ID
                            WHERE  COU_ST_STATUS = 1
                            AND    COU_COD_MATRICULA = '".mb_strtoupper($this->_identity , 'UTF-8')."'
                            AND    (COU_COD_PASSWORD = '".md5($this->_credential)."'
                             OR     COU_COD_PASSWORD = '".md5(mb_strtoupper($this->_credential, 'UTF-8'))."'
                             OR     COU_COD_PASSWORD = '".md5(mb_strtolower($this->_credential, 'UTF-8'))."' )");
        return $stmt->fetch();
    }

}