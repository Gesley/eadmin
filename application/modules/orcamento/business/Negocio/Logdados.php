<?php
/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre log de dados
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Logdados
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Logdados extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Logd ();

        // Define a negocio
        $this->_negocio = 'logdados';
      
    }


    public function gravaLog( $codigo )
    {

        $this->incluirLog( $codigo );
    }

    /**
     * inclui um log na tabela de dados do orcamento
     *
     * @param      array  $dados  The dados
     */
    public function incluirLog( $codigos = null )
    {     
                
        try {
            
            $sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
            
            $router = new Zend_Controller_Router_Rewrite();
            $request = new Zend_Controller_Request_Http();
            $router->route($request);

            if( is_array($codigos) ){
                $codigos = $request->getParam('cod');
            }
            
            if( is_null($codigos) ){
                $codigos = $request->getParam('cod');

            }        

            switch (strtolower( $request->getActionName() )) {
                case 'incluir':
                    $acao =  1;
                    break;
                case 'editar':
                    $acao =  2;
                    break;
                case 'excluir':
                    $acao =  3;
                    break;
                case 'restaurar':
                    $acao = 4;
                    break;
            }        

            $descricaoFuncionalidade = Orcamento_Facade_Logdados::retornaLogDescricaoFuncionalidade( $request->getControllerName(), $request->getActionName() );
            $descricaoAcao = Orcamento_Facade_Logdados::retornaLogDescricaoAcao( $request->getControllerName(), $request->getActionName(), $codigos );                           

            $dadosArr = array (
                            'LOG_DT_DATA' => new Zend_Db_Expr('SYSDATE'),
                            'LOG_TP_ACAO' => $acao,
                            'LOG_DS_UNIDADE_GESTORA' => strtoupper($sessaoOrcamento->ug),
                            'LOG_CD_MATRICULA_USUARIO' => strtoupper($sessaoOrcamento->usuario),
                            'LOG_DS_FUNCIONALIDADE' => $descricaoFuncionalidade['LABEL'],
                            'LOG_DS_DESCRICAO' => $descricaoAcao
            );                
        
            $this->retornaInclusao( $dadosArr );
        
        } catch (Exception $e) {
            throw new Zend_Exception ( $e->getMessage () );
        }
        
    }

    /**
     * monta ao sql e faz a inclusão no banco de dados
     *
     * @param      array  $dadoss
     *
     * @return     obj  Zend_Db_Statement_Pdo_Oci
     */
    public function retornaInclusao($dados)
    {
        $sql = "
            INSERT INTO CEO.CEO_TB_LOG_DADOS
                (
                    LOG_DT_DATA,
                    LOG_TP_ACAO,
                    LOG_DS_UNIDADE_GESTORA,
                    LOG_CD_MATRICULA_USUARIO,
                    LOG_DS_FUNCIONALIDADE,
                    LOG_DS_DESCRICAO,
                    LOG_ID_DADOS
                )VALUES(
                    ".$dados['LOG_DT_DATA'].",
                    ".$dados['LOG_TP_ACAO'].",
                    '".$dados['LOG_DS_UNIDADE_GESTORA']."',
                    '".$dados['LOG_CD_MATRICULA_USUARIO']."',
                    '".$dados['LOG_DS_FUNCIONALIDADE']."',
                    '".$dados['LOG_DS_DESCRICAO']."',
                    CEO_SQ_TB_LOG.NEXTVAL
                )
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->query($sql);
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = "
LOG_ID_DADOS,
TO_CHAR(LOG_DT_DATA, 'DD-MM-YYYY') AS LOG_DT_DATA,
TO_CHAR(LOG_DT_DATA, 'HH24:MI') AS LOG_DT_HORA,
CASE
    WHEN LOG_TP_ACAO = 1
        THEN 'Inclusão '
    WHEN LOG_TP_ACAO = 2
        THEN 'Edição '
    WHEN LOG_TP_ACAO = 3
        THEN 'Exclusão '        
    ELSE 'Restauração  '
END AS LOG_TP_ACAO,
LOG_DS_UNIDADE_GESTORA,
LOG_CD_MATRICULA_USUARIO||' - '||PNAT_NO_PESSOA AS LOG_CD_MATRICULA_USUARIO,
LOG_DS_FUNCIONALIDADE,
LOG_DS_DESCRICAO
";

        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];

        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
LOG_ID_DADOS                  AS \"Código\",
LOG_DT_DATA                   AS \"Data da ação\",
LOG_TP_ACAO                   AS \"Ação\",
LOG_DS_UNIDADE_GESTORA        AS \"Ug\",
LOG_CD_MATRICULA_USUARIO      AS \"Usuário\",
LOG_DS_FUNCIONALIDADE         AS \"Funcionalidade\",
LOG_DS_DESCRICAO              AS \"Descrição\"
                                ";

        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ]   = "";

        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = "";

        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = "";

        // Devolve os campos, conforme ação
        return $campos [ $acao ];
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @param string $chaves
     *        Informa a chave, já tratada, se for o caso
     * @return string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {
        // Condição para ação editar
        $restricao [ 'index' ] = " ORDER BY LOG_DT_DATA DESC ";
        
        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND LOG_ID_DADOS IN ( $chaves ) ";

        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];

        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];

        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];

        // Condição para montagem do combo
        $restricao [ 'combo' ] = "";

        return $restricao [ $acao ];
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid
        $detalhes = array (
                'LOG_ID_DADOS' => array ( 'title' => 'Código', 'abbr' => '' ),
                'LOG_DT_DATA'  => array ( 'title' => 'Data', 'abbr' => '' ),
                'LOG_DT_HORA'  => array ( 'title' => 'Hora', 'abbr' => '' ),
                'LOG_TP_ACAO'  => array ( 'title' => 'Ação', 'abbr' => '' ),
                'LOG_DS_UNIDADE_GESTORA'  => array ( 'title' => 'Ug', 'abbr' => '' ),
                'LOG_CD_MATRICULA_USUARIO'  => array ( 'title' => 'Usuário', 'abbr' => '' ),
                'LOG_DS_FUNCIONALIDADE'  => array ( 'title' => 'Funcionalidade', 'abbr' => '' ),
                'LOG_DS_DESCRICAO' => array ( 'title' => 'Descrição', 'abbr' => '' )
        );

        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 'CAMPO_NAO_EXISTENTE' );

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCacheIds ( $acao = null )
    {
        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id [ 'index' ] = $cache->retornaID_Listagem ( 'orcamento', $negocio );

        // Id para combo
        $id [ 'combo' ] = $cache->retornaID_Combo ( 'orcamento', $negocio );

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [ $acao ] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }


        /**
     * Retorna string contendo as relações (joins) com outras tabelas
     *
     * @return NULL
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaJoins ()
    {
        $join = "
                LEFT JOIN
                        OCS_TB_PMAT_MATRICULA PMAT ON
                            PMAT.PMAT_CD_MATRICULA = LOG_CD_MATRICULA_USUARIO
                LEFT JOIN
                        OCS_TB_PNAT_PESSOA_NATURAL PNAT ON
                            PNAT.PNAT_ID_PESSOA = PMAT.PMAT_ID_PESSOA
        ";
        return $join;
    }

}