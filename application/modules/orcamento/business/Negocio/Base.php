<?php

/**
 * Contém métodos e propriedades elementares para as regras negociais
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza recursos elementares para as regras negociais
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Base
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */
class Orcamento_Business_Negocio_Base {

    /**
     * Objeto Model / DbTable
     *
     * @var object
     */
    protected $_model = null;

    /**
     * Identifica essa regra negocial para chamadas externas
     *
     * @var string
     */
    protected $_negocio = null;

    /**
     * Método construtor
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct () {
        // Chama o método init()
        $this->init();
    }

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init () {
        
    }

    /**
     * Método destrutor que força o fechamento da conexão com o banco
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __destruct () {
        try {
            $banco = Zend_Db_Table::getDefaultAdapter();

            if ($banco->isConnected()) {
                // Fecha a conexão com o banco
                $banco->closeConnection();
            }
        } catch (Exception $e) {
            // não há tratamento de erro para essa funcionalidade
        }
    }

    /**
     * Retorna o objeto da classe Model / DbTable da principal tabela desta
     * classe negocial
     *
     * @return object Classe Model / DbTable
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function dados () {
        try {
            // Devolve o objeto _model
            return $this->_model;
        } catch (Exception $e) {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao buscar o objeto _model.';
            $cod = 1;

            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe
     * negocial
     *
     * @return array Chave primária ou composta
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function chavePrimaria () {
        try {
            // Devolve a chave primária (ou composta) desta tabela
            return $this->_model->chavePrimaria();
        } catch (Exception $e) {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao buscar a chave primária.';
            $cod = 2;

            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna o nome da tabela principal desta classe negocial
     *
     * @return string Nome da tabela
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function tabela () {
        try {
            // Devolve a chave primária (ou composta) desta tabela
            return $this->_model->tabela();
        } catch (Exception $e) {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao buscar o objeto tabela.';
            $cod = 3;

            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna o alias da tabela principal desta classe negocial
     *
     * @return string Alias do nome da tabela
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaAlias () {
        try {
            // Devolve o alias da tabela
            return $this->_model->retornaAlias();
        } catch (Exception $e) {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao buscar o alias da tabela.';
            $cod = 4;

            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna array contendo os registros desta tabela
     *
     * @param boolean $bUsaCache
     *        Determina se a listagem deverá ser cacheada ou não
     * @return array
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaListagem ($bUsaCache = true) {
        try {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao buscar parâmetros de cache.';
            $cod = 5;

            // Instancia cache
            $cache = new Trf1_Cache ();

            // Retorna id de cache de listagem
            $cacheId = $this->retornaCacheIds('index');

            // Retorna tags definidos por negócio
            $cacheTags = $this->retornaCacheTags();

            // Retorna tempo de vida do cache
            $cacheTempo = $this->retornaCacheLifetime();

            // Verifica existência dos dados em cache
            $dados = false;
            if ($bUsaCache == true) {
                // Define variáveis para tratamento de erro - 2
                $msg = 'Erro ao ler o cache anterior.';
                $cod = 6;

                $dados = $cache->lerCache($cacheId);
            }

            if ($dados === false) {
                // Define variáveis para tratamento de erro - 3
                $msg = 'Erro ao buscar instrução sql.';
                $cod = 7;

                // Retorna instrução sql para listagem dos dados
                $sql = $this->retornaSqlBase('index');

                // Zend_Debug::dump ( $sql );
                // exit;
                // Define variáveis para tratamento de erro - 4
                $msg = 'Erro ao buscar dados do banco.';
                $cod = 8;

                // Retorna default adapter de banco
                $banco = Zend_Db_Table::getDefaultAdapter();

                // Retorna todos os registros e campos da instrução sql
                $dados = $banco->fetchAll($sql);

                if ($dados) {
                    // Define variáveis para tratamento de erro - 5
                    $msg = 'Erro ao cachear dados.';
                    $cod = 9;

                    // Cria o cache, apenas se houver dados
                    $cache->criarCache($dados, $cacheId, $cacheTempo, $cacheTags);
                }
            }

            // Devolve os dados
            return $dados;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna um ou mais registros de acordo com a chave primária (ou composta)
     * e $acao informada utilizando ou não ALIAS dos campos pretendidos
     *
     * @param string $acao
     *        Informa a Action para o retorno correto dos campos
     * @param int $chaves
     *        Chave primária (ou composta) da tabela informada
     * @return array $dados
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRegistro ($acao, $chaves = null) {
        try {
            // Retorna a instrução sql para busca dos registros
            $sql = $this->retornaSqlBase($acao, $chaves);

            // Retorna default adapter de banco
            $banco = Zend_Db_Table::getDefaultAdapter();

            if ($acao == Orcamento_Business_Dados::ACTION_EXCLUIR ||
                $acao == Orcamento_Business_Dados::ACTION_RESTAURAR ||
                is_array($chaves)) {
                // Retorna todos os registros e campos da instrução sql
                $dados = $banco->fetchAll($sql);
            } else {
                // Retorna único registro contendo os campos da instrução sql
                $dados = $banco->fetchRow($sql);
            }

            // Devolve os dados
            return $dados;
        } catch (Exception $e) {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao retornar registros.';
            $cod = 10;

            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna array contendo dados para exibição de combo desta funcionalidade
     *
     * @return array
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCombo () {
        try {
            // Instancia cache
            $cache = new Trf1_Cache ();

            // Retorna id de cache de listagem
            $cacheId = $this->retornaCacheIds('combo');

            // Verifica existência dos dados em cache
            $dados = $cache->lerCache($cacheId);

            if ($dados === false) {
                // Retorna instrução sql para listagem dos dados
                $sql = $this->retornaSqlBase('combo');

                // Retorna default adapter de banco
                $banco = Zend_Db_Table::getDefaultAdapter();

                // Retorna todos os registros e campos da instrução sql
                $dados = $banco->fetchPairs($sql);

                // Cria o cache
                $cache->criarCache($dados, $cacheId);
            }

            // Devolve os dados
            return $dados;
        } catch (Exception $e) {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao retornar registros.';
            $cod = 11;

            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Monta a instrução sql contendo as regras negociais. Esta instrução deve
     * ser, se possível, única para todos os retornos de listagem, registros e
     * afins.
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @param string $chavePrimaria
     *        Chave primária (ou composta)
     * @return string
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlBase ($acao, $chavePrimaria = null) {
        try {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao buscar os dados para a Sql base.';
            $cod = 12;

            // Retorna os campos conforme ação informada
            $campos = $this->retornaCampos($acao);

            // Retorna o nome da tabela
            $tabela = $this->tabela();

            // Retorna o alias da tabela
            $alias = $this->retornaAlias();

            // Define variáveis para tratamento de erro - 2
            $msg = 'Erro ao tratar as chaves.';
            $cod = 13;

            // Trata o parâmetro $chave
            $chaves = $this->separaChave($chavePrimaria);

            // Define variáveis para tratamento de erro - 3
            $msg = 'Erro ao buscar os dados complementares para a Sql base.';
            $cod = 14;

            // Retorna restrições
            $restricoes = $this->retornaRestricoes($acao, $chaves);

            // Retorna joins
            $joins = $this->retornaJoins($acao);

            // Retorna group bys
            $groupBy = $this->retornaGroupBy($acao);

            // Define variáveis para tratamento de erro - 4
            $msg = 'Erro na montagem da instrução Sql.';
            $cod = 15;

            // Montagem da instrução Sql
            $sql = "SELECT " . PHP_EOL;
            $sql .= " $campos " . PHP_EOL;

            $sql .= "FROM " . PHP_EOL;
            $sql .= "$tabela $alias " . PHP_EOL;

            $sql .= " $joins ";

            if ($restricoes) {
                // Monta condição
                $sql .= "WHERE " . PHP_EOL;
                $sql .= " 0 = 0 ";
                $sql .= " $restricoes ";
            }

            if ($groupBy) {
                // Monta agrupamento de dados
                $sql .= "GROUP BY ";
                $sql .= " $groupBy ";
            }

            // Devolve a instrução
            return $sql;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna string contendo as condições de cada classe
     *
     * @return NULL
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRestricoes () {
        return null;
    }

    /**
     * Retorna string contendo as relações (joins) com outras tabelas
     *
     * @return NULL
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaJoins () {
        return null;
    }

    /**
     * Retorna string contendo os agrupamentos de campos
     *
     * @return NULL
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaGroupBy () {
        return null;
    }

    /**
     * Inclui um registro na base de dados
     *
     * @param array $dados
     *        Campos do registro a inserir
     * @return NULL array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function incluir ($dados) {
        // Define o sucesso, ou não, da execução da(s) instrução(ões) sql
        $bSucesso = true;

        try {
            // Define a ação
            $acao = Orcamento_Business_Dados::ACTION_INCLUIR;

            // Retorna a tabela para inclusão do registro
            $modelo = $this->_model;

            // Valida negocialmente a inclusão do registro
            $resultado = $this->validaInclusao($dados);

            if ($resultado ['sucesso'] != true) {
                $sMsg = '';
                $sMsg .= $resultado ['msgErro'];

                $resultado ['msgErro'] = $sMsg;

                return $resultado;
            }

            // Transforma os dados, se aplicável
            $dados = $this->transformaDados($dados, $acao);

            // Cria o novo registro...
            $registro = $modelo->createRow($dados);

            // Zend_Debug::dump ( $dados );
            // Zend_Debug::dump ( $registro );
            // exit;
            // Grava o novo registro no banco
            $codigo = $registro->save ();         
           
            // Grava o log no banco
            $this->gravaLog($codigo);                
            
            // Exclui caches referentes a estes dados
            $this->excluiCaches($this->_negocio);
        } catch (Exception $e) {
            // Informa que deu erro em uma das instruções sql
            $bSucesso = false;

            $erroCodigo = $e->getCode();
            $erroMensagem = $e->getMessage();
        }

        // Define detalhes sobre a execução
        $resultado ['sucesso'] = $bSucesso;
        $resultado ['codigo'] = $codigo;
        $resultado ['erro'] = $erroCodigo;
        $resultado ['msgErro'] = $erroMensagem;

        // Devolve o array informando o resultado da operação
        return $resultado;
    }

    /**
     * Edita um registro da base de dados
     *
     * @todo Melhorar tratamento da variável $chavePrimaria para ver se é string
     *       (chave primária) ou array (chave composta) e atuar de acordo com
     *       cada situação.
     * @param array $dados
     *        Campos do formulário para edição
     * @return NULL array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function editar ($dados) {
        // Define o sucesso, ou não, da execução da(s) instrução(ões) sql
        $bSucesso = true;

        try {
            // Define a ação
            $acao = Orcamento_Business_Dados::ACTION_EDITAR;

            // Retorna a tabela para inclusão do registro
            $modelo = $this->_model;

            // Retorna o campo chave primária (ou composta)
            $chavePrimaria = $this->chavePrimaria();

            // Retorna o valor do campo chave primária
            // @todo Tratar chave composta
            $chavePrimariaValor = $dados [$chavePrimaria [0]];

            // Valida negocialmente a edição do registro
            $resultado = $this->validaEdicao($dados);

            if ($resultado ['sucesso'] != true) {
                $sMsg = '';
                $sMsg .= $resultado ['msgErro'];

                $resultado ['msgErro'] = $sMsg;

                return $resultado;
            }

            // Transforma os dados, se aplicável
            $dados = $this->transformaDados($dados, $acao);

            // Retorna o registro para a ser editado
            $registro = $modelo->find($chavePrimariaValor)->current();

            // Não permite alteração na chave primária
            foreach ($chavePrimaria as $chave) {
                unset($dados [$chave]);
            }

            // Faz a associação entre campos e dados vindos do $formulario
            $registro->setFromArray($dados);

            // Atualiza registro pré existente no banco
            $codigo = $registro->save();

            // Grava o log no banco
            $this->gravaLog($codigo); 
            
            // Grava o log no banco
            $this->gravaLog($codigo); 
            
            // Exclui caches referentes a estes dados
            $this->excluiCaches($this->_negocio);
        } catch (Exception $e) {
            // Informa que deu erro em uma das instruções sql
            $bSucesso = false;

            $erroCodigo = $e->getCode();
            $erroMensagem = $e->getMessage();
        }

        // Define detalhes sobre a execução
        $resultado ['sucesso'] = $bSucesso;
        $resultado ['codigo'] = $codigo;
        $resultado ['erro'] = $erroCodigo;
        $resultado ['msgErro'] = $erroMensagem;

        // Devolve o array informando o resultado da operação
        return $resultado;
    }

    /**
     * Realiza a exclusão física - e definitiva - de um ou mais registros no
     * banco
     *
     * @param string|array $chaves
     *        Chave primária (ou composta) da tabela informada
     * @param boolean $bLimpaCache
     *        Exclui ou não os caches para dado negócio
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluir ($chaves, $bLimpaCache = true) {
        // Define o sucesso, ou não, da execução da(s) instrução(ões) sql
        $bSucesso = true;

        try {
            // Define limpeza ou não de cache
            $bCache = $bLimpaCache;

            // Verifica se o registro pode ser ou não excluído
            $resultado = $this->validaExclusao ( $chaves );
                        
            if ( $resultado [ 'sucesso' ] != true ) {
                $sMsg = '';
                $sMsg .= $resultado ['msgErro'];

                $resultado ['msgErro'] = $sMsg;

                return $resultado;
            }

            // Retorna a tabela para exclusão do registro
            $modelo = $this->_model;

            // Retorna o campo chave primária (ou composta)
            $chavePrimaria = $this->chavePrimaria();

            // Monta condição para filtragem dos registros da chave
            $condicao = "";
            foreach ($chavePrimaria as $campoChave) {
                $condicao .= " $campoChave || ";
            }

            // Conclui a montagem da condição
            $condicao = substr($condicao, 0, -4) . " IN ( ??? ) ";

            // Ajusta os valores para inclusão na condição
            $valor = "";
            if (is_array($chaves)) {
                foreach ($chaves as $chave) {
                    // Agrega cada chave informada na $valor
                    $valor .= "$chave, ";
                }
                // Acerta final dos valores
                $valor = substr($valor, 0, -2);
            } else {
                // Se não for array, a $valor é a própria $chave
                $valor = $chaves;
            }

            // Insere os valores na condição
            $condicao = str_replace("???", $valor, $condicao);

            // Exclui fisicamente um ou mais registros no banco
            $qtdeRegistrosAfetados = $modelo->delete($condicao);

            // Exclui caches referentes a estes dados
            $this->excluiCaches($this->_negocio);
        } catch (Exception $e) {
            // Informa que deu erro em uma das instruções sql
            $bSucesso = false;

            $erroCodigo = $e->getCode();
            $erroMensagem = $e->getMessage();
        }

        try {
            $negocio = $this->_negocio;
            $cacheStatus = "Limpeza do cache ($negocio) não solicitada.";

            // Define se a limpeza de cache foi ou não efetuada
            $bCache = false;

            if ($bLimpaCache) {
                // Exclui o(s) cache(s) existentes conforme regra negocial
                $this->excluiCaches($negocio);

                // Identifica que o cache foi excluído
                $bCache = true;
                $cacheStatus = "Cache ($negocio) excluído com sucesso";
            }
        } catch (Exception $e) {
            // apenas não limpa o cache
            $cacheStatus = "Erro na exclusão do cache ($negocio)";
        }

        // Define detalhes sobre a execução
        $resultado ['sucesso'] = $bSucesso;
        $resultado ['cache'] = $bCache;
        $resultado ['cacheStatus'] = $cacheStatus;
        $resultado ['qtdeRegistrosAfetados'] = $qtdeRegistrosAfetados;
        $resultado ['erro'] = $erroCodigo;
        $resultado ['msgErro'] = $erroMensagem;

        // Devolve o array informando o resultado da operação
        return $resultado;
    }

    /**
     * Executa a instrução sql para exclusão lógica de registros
     *
     * @param array $chaves
     *        Chave primária (ou composta) da tabela informada
     * @return Exception
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function exclusaoLogica ($chaves) {
        try {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro na validação de dados para exclusão.';
            $cod = 16;

            // Verifica se o registro pode ser ou não excluído
            $bPodeExcluir = $this->validaExclusao($chaves);

            if ($bPodeExcluir) {
                // Define variáveis para tratamento de erro - 2
                $msg = 'Erro ao buscar a instrução sql para exclusão.';
                $cod = 17;

                // Retorna a instrução sql de exclusão
                $sql = $this->retornaSqlExclusaoLogica($chaves);

                // Define variáveis para tratamento de erro - 3
                $msg = 'Erro ao excluir dados.';
                $cod = 18;

                // Executa a instrução sql
                $resultado = $this->executaQuery($sql, true);
            }

            // Devolve o resultado
            return $resultado;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Executa a instrução sql para a restauração de registos logicament
     * excluídos
     *
     * @param array $chaves
     *        Chave primária (ou composta) da tabela informada
     * @return Exception
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function restauraExclusaoLogica ($chaves) {
        try {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao buscar a instrução sql para restauração.';
            $cod = 19;

            // Retorna a instrução sql de restauração de registros
            $sql = $this->retornaSqlRestauracaoLogica($chaves);

            // Define variáveis para tratamento de erro - 2
            $msg = 'Erro ao restaurar dados.';
            $cod = 20;

            // Executa a instrução sql
            $resultado = $this->executaQuery($sql, true);

            // Devolve o resultado
            return $resultado;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Executa a instrução sql informada, e limpa o cache conforme parâmetro
     *
     * @param mixed $sqls
     *        Uma ou mais instruções sql a serem executadas não tendo nenhum
     *        retorno. Caso seja um array de sqls todas serão efetuadas dentro
     *        de uma única transação.
     * @param boolean $bLimpaCache
     *        Exclui ou não os caches para dado negócio
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function executaQuery ($sqls, $bLimpaCache = false) {
        // Define o sucesso, ou não, da execução da(s) instrução(ões) sql
        $bSucesso = true;

        try {
            // Retorna default adapter de banco
            $banco = Zend_Db_Table::getDefaultAdapter();

            if (!is_array($sqls)) {
                // Converte a string em array para tratamento único
                $sqls = array($sqls);
            }

            // Inicia a transação
            $banco->beginTransaction();

            // Inicializa contador
            $i = 0;

            foreach ($sqls as $sql) {
                // Executa a query informada
                $execucao = $banco->query($sql);

                // Define a quantidade de registros afetados em cada sql
                $qtdeRegistrosAfetados [$i ++] = $execucao->rowCount();
            }

            // Confirma a execução das instruções sql
            $banco->commit();
        } catch (Exception $e) {
            // Desfaz a execução das instruções sql
            $banco->rollBack();

            // Informa que deu erro em uma das instruções sql
            $bSucesso = false;

            $erroCodigo = $e->getCode();
            $erroMensagem = $e->getMessage();
        }

        try {
            $negocio = $this->_negocio;
            $cacheStatus = "Limpeza do cache ($negocio) não solicitada.";

            // Define se a limpeza de cache foi ou não efetuada
            $bCache = false;

            if ($bLimpaCache == true) {
                // Exclui o(s) cache(s) existentes conforme regra negocial
                $this->excluiCaches($negocio);

                // Identifica que o cache foi excluído
                $bCache = true;
                $cacheStatus = "Cache ($negocio) excluído com sucesso";
            }
        } catch (Exception $e) {
            // apenas não limpa o cache
            $cacheStatus = "Erro na exclusão do cache ($negocio)";
        }

        // Define detalhes sobre a execução
        $resultado ['sucesso'] = $bSucesso;
        $resultado ['cache'] = $bCache;
        $resultado ['cacheStatus'] = $cacheStatus;
        $resultado ['qtdeRegistrosAfetados'] = $qtdeRegistrosAfetados;
        $resultado ['erro'] = $erroCodigo;
        $resultado ['msgErro'] = $erroMensagem;

        return $resultado;
    }

    /**
     * Retorna a chave informada e a devolve como string tratada
     *
     * @param array|string $chaves
     *        Chave primária (ou composta) da tabela informada
     * @return Exception
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function separaChave ($chaves) {
        try {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao separar as chaves.';
            $cod = 21;

            // Preserva o parâmetro
            $strChaves = $chaves;

            if (is_array($chaves)) {
                // Junta numa string os valores separados por vírgula
                $strChaves = implode(', ', $chaves);
            }

            // Devolve a string tratada
            return $strChaves;
        } catch (Exception $e) {
            // Retorna o objeto de erro
            // return $e;
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna a matrícula do usuário logado
     *
     * @return Exception
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaMatricula () {
        try {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao buscar a matrícula do usuário logado.';
            $cod = 22;

            // Instancia a namespace
            $sessao = new Zend_Session_Namespace('userNs');

            // Identifica a matrícula
            $matricula = $sessao->matricula;

            // Devolve a matrpicula
            return $matricula;
        } catch (Exception $e) {
            // Retorna o objeto de erro
            // return $e;
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Exclui o cache negocial, basicamente a listagem e combo
     *
     * @param string $controle
     *        Nome da controle
     * @param array $cacheIds
     *        Array contendo todos os ids a serem excluídos
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluiCaches () {
        try {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao buscar os IDs dos caches envolvidos.';
            $cod = 23;

            // Instancia o cache
            $cache = new Trf1_Cache ();

            // Retorna uma ou mais ids dos caches
            $cacheIds = $this->retornaCacheIds();

            if ($cacheIds) {
                // Define variáveis para tratamento de erro - 2
                $msg = 'Erro ao excluir cache por ID.';
                $cod = 24;

                // Remove os caches, conforme ids informados
                foreach ($cacheIds as $cacheId) {
                    // Exclui o cache conform id da listagem
                    $cache->excluirCache($cacheId);
                }
            }

            // Define variáveis para tratamento de erro - 3
            $msg = 'Erro ao buscar as Taga dos caches envolvidos.';
            $cod = 25;

            // Retorna uma ou mais tags dos caches
            $cacheTags = $this->retornaCacheTags();

            if ($cacheTags) {
                // Define variáveis para tratamento de erro - 4
                $msg = 'Erro ao excluir cache por Tag.';
                $cod = 26;

                $cache->excluirCachesPorTag($cacheTags);
            }

            /*
             * if ( $cacheTags ) { // Remove os caches, conforme ids informados
             * foreach ( $cacheIds as $cacheId ) { // Exclui o cache conform id
             * da listagem $cache->excluirCache ( $cacheId ); } }
             */
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Efetua transformações nos dados, se aplicável
     *
     * @param array $dados
     *        Dados do registro a ser transformado, se aplicável
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return array $dados
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaDados ($dados, $acao) {
        // Retorna os dados
        return $dados;
    }

    /**
     * Efetua transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaFormulario ($formulario, $acao) {
        // Devolve o formulário
        return $formulario;
    }

    /**
     * Verifica se há algum impedimento negocial para, em caso verdadeiro,
     * bloquear todos os campos do formulário
     *
     * @tutorial Esse método deve ser sobrescrito na classe pai
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param Zend_Form $formulário
     *        Formulário em uso para bloqueio dos campos
     * @param array $dados
     *        Dados para popular o formulário
     * @return boolean
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaDeveBloquearCampos ($acao, $formulario, $dados) {
        return false;
    }

    /**
     * Verifica se o registro pode ser incluído
     *
     * @param string $dados
     *        Dados para serem negocialmente validados
     * @return boolean
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function validaInclusao ($dados = null) {
        try {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao validar inclusão de dados.';
            $cod = 27;

            // Define detalhes sobre a validação
            $resultado ['sucesso'] = true;
            $resultado ['erro'] = null;
            $resultado ['msgErro'] = null;

            // Devolve o resultado da validação
            return $resultado;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Verifica se o registro pode ser editado
     *
     * @param string $dados
     *        Dados para serem negocialmente validados
     * @return boolean
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function validaEdicao ($dados = null) {
        try {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao validar edição de dados.';
            $cod = 28;

            // Define detalhes sobre a validação
            $resultado ['sucesso'] = true;
            $resultado ['erro'] = null;
            $resultado ['msgErro'] = null;

            // Devolve o resultado da validação
            return $resultado;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Verifica se um ou mais registros, conforme $chaves informada, pode ser
     * excluído
     *
     * @param string $chaves
     *        Chave primária (ou composta) da tabela informada
     * @return boolean
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function validaExclusao ($chaves = null) {
        try {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao validar exclusão de dados.';
            $cod = 29;

            // Define detalhes sobre a validação
            $resultado ['sucesso'] = true;
            $resultado ['erro'] = null;
            $resultado ['msgErro'] = null;

            // Devolve o resultado da validação
            return $resultado;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna, caso seja informado o parâmetro $acao, o nome do $cacheId ou
     * então devolve um array com todos os nomes previstos.
     *
     * @param string $acao
     *        Informa a Action para o retorno correto dos campos
     * @return string array
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheIds ($acao = null) {
        try {
            // Define variáveis para tratamento de erro - 1
            $msg = 'Erro ao buscar o perfil do usuário.';
            $cod = 30;

            // Instancia classe de sessão
            $sessao = new Orcamento_Business_Sessao ();

            // Busca dados sobre o perfil do usuário logado
            $perfilFull = $sessao->retornaPerfil();

            // Separa valores...
            $perfil = $perfilFull ['perfil'];
            $ug = $perfilFull ['ug'];
            $responsavel = $perfilFull ['responsavel'];
            $responsavel = str_replace('/', '_', $responsavel);

            // Se $ug for informada...
            $restricao = '';
            if ($ug != 'todas') {
                // Define restrição por $ug
                $restricao = "ug_$ug";
            }

            // Se $responsavel for informado...
            if ($responsavel != 'todos') {
                // Define restrição por $responsavel
                $restricao = "responsavel_$responsavel";
                $restricao = str_replace('__', '_', $restricao);
            }

            // Retorna o nome negocial
            $negocio = $this->_negocio;

            // Define variáveis para tratamento de erro - 2
            $msg = 'Erro ao buscar IDs da listagem.';
            $cod = 31;

            // Instancia o cache
            $cache = new Trf1_Cache ();

            // Id para listagem
            $cacheIndex = $cache->retornaID_Listagem('orcamento', $negocio);

            // Define variáveis para tratamento de erro - 3
            $msg = 'Erro ao buscar IDs do combo.';
            $cod = 32;

            // Id para combo
            $cacheCombo = $cache->retornaID_Combo('orcamento', $negocio);

            $id ['index'] = $cacheIndex;
            $id ['combo'] = $cacheCombo;

            if ($restricao != '') {
                $id ['index'] .= "_$restricao";
                $id ['combo'] .= "_$restricao";
            }

            // Determina qual valor será retornado
            $retorno = ( $acao != null ? $id [$acao] : $id );

            // Devolve o id, conforme $acao informada
            return $retorno;
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna array contendo as tags para uso no cache
     *
     * @return NULL
     * @throws Orcamento_Business_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheTags () {
        try {
            // Define variáveis para tratamento de erro
            $msg = 'Erro ao buscar Tags de cache.';
            $cod = 33;

            // Retorna o nome negocial
            $negocio = $this->_negocio;

            // Devolve as tags para cache
            return array($negocio);
        } catch (Exception $e) {
            // Gera o erro
            throw new Orcamento_Business_Exception($msg, $cod, $e);
        }
    }

    /**
     * Retorna valor em segundos para o tempo de vida do cache.
     *
     * @return integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheLifetime () {
        return 0;
    }

    public function gravaLog($codigo)
    {
        $logdados = New Orcamento_Business_Negocio_Logdados();                                      
        $logdados->incluirLog($codigo);        
    }
    
}
