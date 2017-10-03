<?php

/**
 * Classe para tratamento de permissões do e-Orçamento
 *
 * e-Admin
 * e-Orçamento
 * Core
 *
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza os métodos e demais funcionalidades para o tratamento de
 * permissões para o sistema e-Orçamento, até que o e-Guardião completo esteja
 * concluído!
 *
 * @category Orcamento
 * @package Orcamento_Business_Acl
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
class Orcamento_Business_Acl extends Zend_Acl {

    /**
     * Método construtor que define perfis, recursos e privilégios
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct() {
        $this->definePerfis();
        $this->defineRecursos();
        $this->definePrivilegios();
    }

    /**
     * Método que valida, ou não, a permissão do usuário logado ao recurso
     * solicitado (na $requisicao); bem como define a variável global:
     * CEO_PERMISSAO_RESPONSAVEIS com parte da cláusula WHERE das instruções Sql
     * sobre despesa, responsáveis e outros, se for o caso.
     *
     * @param Zend_Controller_Request_Abstract $requisicao
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function autoriza(Zend_Controller_Request_Abstract $requisicao) {
        // Dados sobre requisição
        $modulo = strtolower($requisicao->getModuleName());
        $controle = strtolower($requisicao->getControllerName());
        $acao = strtolower($requisicao->getActionName());

        // Define o recurso
        $recurso = $controle . ':' . $acao;

        if ($recurso == 'index:erro') {
            // Não há impedimento de permissões para exibição da tela de erro!
            return true;
        }

        // Busca matrícula do usuário logado
        $sessao = new Orcamento_Business_Sessao();
        $matricula = $sessao->retornaMatricula();

        // ************************************************************
        // Matrículas para teste
        // ************************************************************
        // $matricula = 'tr17496ps'; // desenvolvedor
        // $matricula = 'tr58203';   // dipor
        // $matricula = 'df1289803'; // seccional - DF
        // $matricula = 'ba368603';  // seccional - BA
        // $matricula = 'ac11003';   // seccional - AC
        // $matricula = 'mg97403';   // seccional - MG
        // $matricula = 'tr300040';  // secretaria (Secad)
        // $matricula = 'tr85303';   // secretaria (COTAQ)
        // $matricula = 'tr86003';   // secretaria reserva cojef
        // $matricula = 'tr13003';   // diefi
        // $matricula = 'tr58503';   // planejamento - dipla
        // $matricula = 'tr45103';   // consulta - dipla
        // $matricula = 'tr47403';   // consulta - secin
        // $matricula = 'ninguem';   // sem acesso
        // ************************************************************
        // $matricula = 'xxxxxxx';
        // ************************************************************
        // Busca nível de permissão e filtro de dados

        $nivel = Null;
        $ug = Null;
        $responsavel = Null;

        $usuarios = $this->defineUsuarios();

        if (array_key_exists($matricula, $usuarios)) {
            $nivel = $usuarios[$matricula]['nivel'];
            //$ug = strtoupper ( $usuarios [$matricula] ['ug'] );
            $ug = ($usuarios[$matricula]['ug'] != 'todas' ? strtoupper($usuarios[$matricula]['ug']) : 'todas');
            $responsavel = $usuarios[$matricula]['responsavel'];
        }

        // $erro [ 'titulo' ] = 'Permissão de acesso';
        $erro['perfil'] = $nivel;
        $erro['ug'] = $ug;
        $erro['responsavel'] = $responsavel;

        // Cria sessão para definição do perfil do usuário
        $sessao->definePerfil($erro);

        // Se o usuário não tem nível...
        if (!$nivel) {
            $mensagem = '';
            $mensagem .= 'Não foram encontradas as informações de permissões ';
            $mensagem .= 'de acesso ao e-Orçamento para este usuário. ';
            $mensagem .= 'Favor entrar em contato com o gestor do sistema.';

            throw new Orcamento_Business_Exception($mensagem, 1);
        }

        // Se o usuário não possui privilégio para o recurso em questão...
        if (!$this->isAllowed($nivel, $recurso)) {
            $mensagem = '';
            $mensagem .= 'Você não possui privilégios suficientes para ';
            $mensagem .= 'acessar essa funcionalidade.';
            $mensagem .= 'Favor entrar em contato com o gestor do sistema.';

            throw new Orcamento_Business_Exception($mensagem, 1);
        }

        // ...finalmente, cria a restrição de registros, se necessário!
        $condicao = '';

        switch ($nivel) {
        case 'seccional':
        case 'consulta':
        case 'planejamento':
            if ($ug != 'todas') {
                $condicao = " AND RHCL.LOTA_SIGLA_SECAO = '$ug' ";
            }

            if ($responsavel != 'todos') {
                $condicao .= " AND ";
                $condicao .= " RH_SIGLAS_FAMILIA_CENTR_LOTA ( ";
                $condicao .= " RHCL.LOTA_SIGLA_SECAO, ";
                $condicao .= " RHCL.LOTA_COD_LOTACAO ";
                $condicao .= " ) Like '%$responsavel' " . PHP_EOL;
            }

            break;

        case 'diefi':
            // Apenas Precatórios, RPV e TRF1
            $condicao = " AND DESP.DESP_CD_UG In (90027, 90049) " . PHP_EOL;
            break;

        case 'secretaria':
            $condicao = " AND DESP.DESP_CD_UG In (90027, 90049) " . PHP_EOL;

            if ($responsavel != 'todos') {
                $condicao .= " AND ";
                $condicao .= " RH_SIGLAS_FAMILIA_CENTR_LOTA ( ";
                $condicao .= " RHCL.LOTA_SIGLA_SECAO, ";
                $condicao .= " RHCL.LOTA_COD_LOTACAO ";
                $condicao .= " ) Like '%$responsavel' " . PHP_EOL;
            }

            break;

        case 'secretaria_reserva':
            $condicao = " AND ";
            $condicao .= " DESP.DESP_CD_UG In (";
            $condicao .= " 90027, 90032, 90049 ";
            $condicao .= " ) " . PHP_EOL;

            if ($responsavel != 'todos') {
                $condicao .= " AND ";
                $condicao .= " RH_SIGLAS_FAMILIA_CENTR_LOTA ( ";
                $condicao .= " RHCL.LOTA_SIGLA_SECAO, ";
                $condicao .= " RHCL.LOTA_COD_LOTACAO ";
                $condicao .= " ) Like '%$responsavel' " . PHP_EOL;
            }

            break;

        case 'dipor':
        case 'desenvolvedor':
            break;
        }

        // Define a variável global
        define('CEO_PERMISSAO_RESPONSAVEIS', $condicao);
    }

    /**
     * Define os perfils previstos para o sistema
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function definePerfis() {
        // Define os perfils e suas heranças
        $this->addRole(new Zend_Acl_Role(null));
        $this->addRole(new Zend_Acl_Role('consulta', null));
        $this->addRole(new Zend_Acl_Role('planejamento'), 'consulta');
        $this->addRole(new Zend_Acl_Role('diefi'), 'consulta');
        $this->addRole(new Zend_Acl_Role('secretaria'), 'diefi');
        $this->addRole(new Zend_Acl_Role('secretaria_reserva'), 'secretaria');
        $this->addRole(new Zend_Acl_Role('seccional'), 'secretaria');
        $this->addRole(new Zend_Acl_Role('dipor'), 'seccional');
        $this->addRole(new Zend_Acl_Role('desenvolvedor'), 'dipor');
    }

    /**
     * Define os recursos disponíveis para o sistema
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function defineRecursos() {
        // ************************************************************
        // Controles genéricos
        // ************************************************************
        // Início
        $this->add(new Zend_Acl_Resource('index'));
        $this->add(new Zend_Acl_Resource('index:erro'), 'index');
        $this->add(new Zend_Acl_Resource('index:index'), 'index');
        // Ajuda
        $this->add(new Zend_Acl_Resource('ajuda'));
        $this->add(new Zend_Acl_Resource('ajuda:grid'), 'ajuda');
        $this->add(new Zend_Acl_Resource('ajuda:index'), 'ajuda');
        $this->add(new Zend_Acl_Resource('ajuda:permissao'), 'ajuda');
        // Dashboard
        $this->add(new Zend_Acl_Resource('dashboard'));
        $this->add(new Zend_Acl_Resource('dashboard:index'), 'dashboard');
        // Erro
        $this->add(new Zend_Acl_Resource('error'));
        $this->add(new Zend_Acl_Resource('error:error'), 'error');
        $this->add(new Zend_Acl_Resource('error:permissao'), 'error');
        // Log
        $this->add(new Zend_Acl_Resource('log'));
        $this->add(new Zend_Acl_Resource('log:detalhe'), 'log');
        $this->add(new Zend_Acl_Resource('log:index'), 'log');
        $this->add(new Zend_Acl_Resource('log:listagem'), 'log');
        // Projeto
        $this->add(new Zend_Acl_Resource('projeto'));
        $this->add(new Zend_Acl_Resource('projeto:cache'), 'projeto');
        $this->add(new Zend_Acl_Resource('projeto:index'), 'projeto');
        $this->add(new Zend_Acl_Resource('projeto:info'), 'projeto');

        // ************************************************************
        // menu Despesas
        // ************************************************************
        // Listagens
        $this->add(new Zend_Acl_Resource('despesa'));
        $this->add(new Zend_Acl_Resource('despesa:ajaxdespesa'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:ajaxfonte'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:compl'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:contr'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:dashboard'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:detalhe'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:distr'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:editar'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:editarcontrato'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:empen'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:excluir'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:finan'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:incluir'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:index'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:orcam'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:planj'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:valor'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:trocaptres'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:trocafonte'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:ajaxptres'), 'despesa');
        $this->add(new Zend_Acl_Resource('despesa:teste'), 'despesa');  
        // RDO
        $this->add(new Zend_Acl_Resource('rdo'));
        $this->add(new Zend_Acl_Resource('rdo:detalhe'), 'rdo');
        $this->add(new Zend_Acl_Resource('rdo:editar'), 'rdo');
        $this->add(new Zend_Acl_Resource('rdo:excluir'), 'rdo');
        $this->add(new Zend_Acl_Resource('rdo:incluir'), 'rdo');
        $this->add(new Zend_Acl_Resource('rdo:index'), 'rdo');        
        $this->add(new Zend_Acl_Resource('rdo:rdone'), 'rdo');        
        $this->add(new Zend_Acl_Resource('rdo:requisicoessemempenho'), 'rdo');
        // Saldo
        $this->add(new Zend_Acl_Resource('saldo'));
        $this->add(new Zend_Acl_Resource('saldo:detalhe'), 'saldo');
        $this->add(new Zend_Acl_Resource('saldo:index'), 'saldo');
        $this->add(new Zend_Acl_Resource('saldo:listagem'), 'saldo');
        $this->add(new Zend_Acl_Resource('saldo:planejamento'), 'saldo');
        // Extrato
        $this->add(new Zend_Acl_Resource('extrato'));
        $this->add(new Zend_Acl_Resource('extrato:detalhe'), 'extrato');
        $this->add(new Zend_Acl_Resource('extrato:index'), 'extrato');
        // Projeção orçamentária
        $this->add(new Zend_Acl_Resource('projecao'));
        $this->add(new Zend_Acl_Resource('projecao:detalhe'), 'projecao');
        $this->add(new Zend_Acl_Resource('projecao:editar'), 'projecao');
        $this->add(new Zend_Acl_Resource('projecao:execucao'), 'projecao');
        $this->add(new Zend_Acl_Resource('projecao:index'), 'projecao');
        $this->add(new Zend_Acl_Resource('projecao:listagem'), 'projecao');
        $this->add(new Zend_Acl_Resource('projecao:execucaomensal'), 'projecao');
        // Justificativa da projeção
        $this->add(new Zend_Acl_Resource('justificativa'));
        $this->add(new Zend_Acl_Resource('justificativa:restaurar'), 'justificativa');
        $this->add(new Zend_Acl_Resource('justificativa:detalhe'), 'justificativa');
        $this->add(new Zend_Acl_Resource('justificativa:editar'), 'justificativa');
        $this->add(new Zend_Acl_Resource('justificativa:excluir'), 'justificativa');
        $this->add(new Zend_Acl_Resource('justificativa:incluir'), 'justificativa');
        $this->add(new Zend_Acl_Resource('justificativa:index'), 'justificativa');
        // ProjecaoJustificativa da projeção
        $this->add(new Zend_Acl_Resource('projecaojustificativa'));
        $this->add(new Zend_Acl_Resource('projecaojustificativa:detalhe'), 'projecaojustificativa');
        $this->add(new Zend_Acl_Resource('projecaojustificativa:editar'), 'projecaojustificativa');
        $this->add(new Zend_Acl_Resource('projecaojustificativa:excluir'), 'projecaojustificativa');
        $this->add(new Zend_Acl_Resource('projecaojustificativa:incluir'), 'projecaojustificativa');
        $this->add(new Zend_Acl_Resource('projecaojustificativa:index'), 'projecaojustificativa');

        // ************************************************************
        // menu Captação
        // ************************************************************
        // Exercicio
        $this->add(new Zend_Acl_Resource('exercicio'));
        $this->add(new Zend_Acl_Resource('exercicio:detalhe'), 'exercicio');
        $this->add(new Zend_Acl_Resource('exercicio:editar'), 'exercicio');
        $this->add(new Zend_Acl_Resource('exercicio:excluir'), 'exercicio');
        $this->add(new Zend_Acl_Resource('exercicio:incluir'), 'exercicio');
        $this->add(new Zend_Acl_Resource('exercicio:index'), 'exercicio');
        $this->add(new Zend_Acl_Resource('exercicio:restaurar'), 'exercicio');
        $this->add(new Zend_Acl_Resource('exercicio:copiardespesas'), 'exercicio');
        // Informativo
        $this->add(new Zend_Acl_Resource('informativo'));
        $this->add(new Zend_Acl_Resource('informativo:detalhe'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:editar'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:excluir'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:excluidos'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:incluir'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:index'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:listagem'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:restaurar'), 'informativo');
        $this->add(new Zend_Acl_Resource('informativo:leitura'), 'informativo');
        // Regra
        $this->add(new Zend_Acl_Resource('regra'));
        $this->add(new Zend_Acl_Resource('regra:aplicar'), 'regra');
        $this->add(new Zend_Acl_Resource('regra:detalhe'), 'regra');
        $this->add(new Zend_Acl_Resource('regra:editar'), 'regra');
        $this->add(new Zend_Acl_Resource('regra:excluir'), 'regra');
        $this->add(new Zend_Acl_Resource('regra:incluir'), 'regra');
        $this->add(new Zend_Acl_Resource('regra:index'), 'regra');
        $this->add(new Zend_Acl_Resource('regra:restaurar'), 'regra');
        // Empenho
        $this->add(new Zend_Acl_Resource('empenho'));
        $this->add(new Zend_Acl_Resource('empenho:detalhe'), 'empenho');
        $this->add(new Zend_Acl_Resource('empenho:index'), 'empenho');

        // Perspectiva
        $this->add(new Zend_Acl_Resource('perspectiva'));
        $this->add(new Zend_Acl_Resource('perspectiva:detalhe'), 'perspectiva');
        $this->add(new Zend_Acl_Resource('perspectiva:editar'), 'perspectiva');
        $this->add(new Zend_Acl_Resource('perspectiva:excluir'), 'perspectiva');
        $this->add(new Zend_Acl_Resource('perspectiva:excluidos'), 'perspectiva');
        $this->add(new Zend_Acl_Resource('perspectiva:incluir'), 'perspectiva');
        $this->add(new Zend_Acl_Resource('perspectiva:index'), 'perspectiva');
        $this->add(new Zend_Acl_Resource('perspectiva:restaurar'), 'perspectiva');

        // Macrodesafio
        $this->add(new Zend_Acl_Resource('macrodesafio'));
        $this->add(new Zend_Acl_Resource('macrodesafio:detalhe'), 'macrodesafio');
        $this->add(new Zend_Acl_Resource('macrodesafio:editar'), 'macrodesafio');
        $this->add(new Zend_Acl_Resource('macrodesafio:excluir'), 'macrodesafio');
        $this->add(new Zend_Acl_Resource('macrodesafio:excluidos'), 'macrodesafio');
        $this->add(new Zend_Acl_Resource('macrodesafio:incluir'), 'macrodesafio');
        $this->add(new Zend_Acl_Resource('macrodesafio:index'), 'macrodesafio');
        $this->add(new Zend_Acl_Resource('macrodesafio:restaurar'), 'macrodesafio');

        // ************************************************************
        // menu Solicitações
        // ************************************************************
        // Solicitação de nova despesa
        $this->add(new Zend_Acl_Resource('novadespesa'));
        $this->add(new Zend_Acl_Resource('novadespesa:ajaxnovadespesa'), 'novadespesa');
        $this->add(new Zend_Acl_Resource('novadespesa:detalhe'), 'novadespesa');
        $this->add(new Zend_Acl_Resource('novadespesa:editar'), 'novadespesa');
        $this->add(new Zend_Acl_Resource('novadespesa:excluir'), 'novadespesa');
        $this->add(new Zend_Acl_Resource('novadespesa:incluir'), 'novadespesa');
        $this->add(new Zend_Acl_Resource('novadespesa:index'), 'novadespesa');
        // Solicitação de nova movimentação de crédito
        $this->add(new Zend_Acl_Resource('movimentacaocrednova'));
        $this->add(new Zend_Acl_Resource('movimentacaocrednova:detalhe'), 'movimentacaocrednova');
        $this->add(new Zend_Acl_Resource('movimentacaocrednova:editar'), 'movimentacaocrednova');
        $this->add(new Zend_Acl_Resource('movimentacaocrednova:excluir'), 'movimentacaocrednova');
        $this->add(new Zend_Acl_Resource('movimentacaocrednova:incluir'), 'movimentacaocrednova');
        $this->add(new Zend_Acl_Resource('movimentacaocrednova:index'), 'movimentacaocrednova');
        $this->add(new Zend_Acl_Resource('movimentacaocrednova:restaurar'), 'movimentacaocrednova');

        // Novas solicitacoes
        $this->add(new Zend_Acl_Resource('solicitacaoajuste'));
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:detalhe'), 'solicitacaoajuste');
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:editar'), 'solicitacaoajuste');
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:excluir'), 'solicitacaoajuste');
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:excluidos'), 'solicitacaoajuste');
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:incluir'), 'solicitacaoajuste');
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:index'), 'solicitacaoajuste');
        $this->add(new Zend_Acl_Resource('solicitacaoajuste:restaurar'), 'solicitacaoajuste');
        // Importar NC
        $this->add(new Zend_Acl_Resource('importarnc'));
        $this->add(new Zend_Acl_Resource('importarnc:detalhe'), 'importarnc');
        $this->add(new Zend_Acl_Resource('importarnc:editar'), 'importarnc');
        $this->add(new Zend_Acl_Resource('importarnc:excluir'), 'importarnc');
        $this->add(new Zend_Acl_Resource('importarnc:incluir'), 'importarnc');
        $this->add(new Zend_Acl_Resource('importarnc:index'), 'importarnc');
        $this->add(new Zend_Acl_Resource('importarnc:restaurar'), 'importarnc');

        // Importar NE
        $this->add(new Zend_Acl_Resource('importarne'));
        $this->add(new Zend_Acl_Resource('importarne:detalhe'), 'importarne');
        $this->add(new Zend_Acl_Resource('importarne:editar'), 'importarne');
        $this->add(new Zend_Acl_Resource('importarne:excluir'), 'importarne');
        $this->add(new Zend_Acl_Resource('importarne:incluir'), 'importarne');
        $this->add(new Zend_Acl_Resource('importarne:index'), 'importarne');
        $this->add(new Zend_Acl_Resource('importarne:restaurar'), 'importarne');

        // Importar EF
        $this->add(new Zend_Acl_Resource('importaref'));
        $this->add(new Zend_Acl_Resource('importaref:detalhe'), 'importaref');
        $this->add(new Zend_Acl_Resource('importaref:editar'), 'importaref');
        $this->add(new Zend_Acl_Resource('importaref:excluir'), 'importaref');
        $this->add(new Zend_Acl_Resource('importaref:incluir'), 'importaref');
        $this->add(new Zend_Acl_Resource('importaref:index'), 'importaref');
        $this->add(new Zend_Acl_Resource('importaref:restaurar'), 'importaref');

        // Importar Pré empenho
        $this->add(new Zend_Acl_Resource('preempenho'));
        $this->add(new Zend_Acl_Resource('preempenho:detalhe'), 'preempenho');
        $this->add(new Zend_Acl_Resource('preempenho:editar'), 'preempenho');
        $this->add(new Zend_Acl_Resource('preempenho:excluir'), 'preempenho');
        $this->add(new Zend_Acl_Resource('preempenho:incluir'), 'preempenho');
        $this->add(new Zend_Acl_Resource('preempenho:index'), 'preempenho');
        $this->add(new Zend_Acl_Resource('preempenho:restaurar'), 'preempenho');

        // ************************************************************
        // menu Programação Orçamentária
        // ************************************************************
        // Pendências
        $this->add(new Zend_Acl_Resource('pendencia'));
        $this->add(new Zend_Acl_Resource('pendencia:index'), 'pendencia');
        // Movimentação de crédito
        $this->add(new Zend_Acl_Resource('movimentacaocred'));
        $this->add(new Zend_Acl_Resource('movimentacaocred:ajaxretornadespesa'), 'movimentacaocred');
        $this->add(new Zend_Acl_Resource('movimentacaocred:detalhe'), 'movimentacaocred');
        $this->add(new Zend_Acl_Resource('movimentacaocred:editar'), 'movimentacaocred');
        $this->add(new Zend_Acl_Resource('movimentacaocred:excluir'), 'movimentacaocred');
        $this->add(new Zend_Acl_Resource('movimentacaocred:incluir'), 'movimentacaocred');
        $this->add(new Zend_Acl_Resource('movimentacaocred:index'), 'movimentacaocred');
        // Recursos a descentralizar
        $this->add(new Zend_Acl_Resource('recursodesc'));
        $this->add(new Zend_Acl_Resource('recursodesc:detalhe'), 'recursodesc');
        $this->add(new Zend_Acl_Resource('recursodesc:editar'), 'recursodesc');
        $this->add(new Zend_Acl_Resource('recursodesc:excluir'), 'recursodesc');
        $this->add(new Zend_Acl_Resource('recursodesc:incluir'), 'recursodesc');
        $this->add(new Zend_Acl_Resource('recursodesc:index'), 'recursodesc');
        // Fases do recurso a descentralizar
        $this->add(new Zend_Acl_Resource('recursofase'));
        $this->add(new Zend_Acl_Resource('recursofase:detalhe'), 'recursofase');
        $this->add(new Zend_Acl_Resource('recursofase:editar'), 'recursofase');
        $this->add(new Zend_Acl_Resource('recursofase:excluir'), 'recursofase');
        $this->add(new Zend_Acl_Resource('recursofase:incluir'), 'recursofase');
        $this->add(new Zend_Acl_Resource('recursofase:index'), 'recursofase');
        // Bloqueio de movimentações automáticas
        $this->add(new Zend_Acl_Resource('bloqueio'));
        $this->add(new Zend_Acl_Resource('bloqueio:detalhe'), 'bloqueio');
        $this->add(new Zend_Acl_Resource('bloqueio:editar'), 'bloqueio');
        $this->add(new Zend_Acl_Resource('bloqueio:excluir'), 'bloqueio');
        $this->add(new Zend_Acl_Resource('bloqueio:incluir'), 'bloqueio');
        $this->add(new Zend_Acl_Resource('bloqueio:index'), 'bloqueio');
        // Travamentos de períodos de projeção
        $this->add(new Zend_Acl_Resource('travaprojecao'));
        $this->add(new Zend_Acl_Resource('travaprojecao:detalhe'), 'travaprojecao');
        $this->add(new Zend_Acl_Resource('travaprojecao:editar'), 'travaprojecao');
        $this->add(new Zend_Acl_Resource('travaprojecao:excluir'), 'travaprojecao');
        $this->add(new Zend_Acl_Resource('travaprojecao:incluir'), 'travaprojecao');
        $this->add(new Zend_Acl_Resource('travaprojecao:index'), 'travaprojecao');

        // ************************************************************
        // menu SIAFI
        // ************************************************************
        // Créditos
        $this->add(new Zend_Acl_Resource('credito'));
        $this->add(new Zend_Acl_Resource('credito:detalhe'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:editar'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:excluir'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:excluidos'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:incluir'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:inconsistencia'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:index'), 'credito');
        $this->add(new Zend_Acl_Resource('credito:restaurar'), 'credito');
        // NC
        $this->add(new Zend_Acl_Resource('nc'));
        $this->add(new Zend_Acl_Resource('nc:detalhe'), 'nc');
        $this->add(new Zend_Acl_Resource('nc:editar'), 'nc');
        $this->add(new Zend_Acl_Resource('nc:importar'), 'nc');
        $this->add(new Zend_Acl_Resource('nc:inconsistencia'), 'nc');
        $this->add(new Zend_Acl_Resource('nc:inconsistenciareserva'), 'nc');
        $this->add(new Zend_Acl_Resource('nc:index'), 'nc');
        $this->add(new Zend_Acl_Resource('nc:excluir'), 'nc');
        // NE
        $this->add(new Zend_Acl_Resource('ne'));
        $this->add(new Zend_Acl_Resource('ne:detalhe'), 'ne');
        $this->add(new Zend_Acl_Resource('ne:editar'), 'ne');
        $this->add(new Zend_Acl_Resource('ne:importar'), 'ne');
        $this->add(new Zend_Acl_Resource('ne:inconsistencia'), 'ne');
        $this->add(new Zend_Acl_Resource('ne:index'), 'ne');
        $this->add(new Zend_Acl_Resource('ne:excluir'), 'ne');
        // Execução da NE
        $this->add(new Zend_Acl_Resource('neexec'));
        $this->add(new Zend_Acl_Resource('neexec:detalhe'), 'neexec');
        $this->add(new Zend_Acl_Resource('neexec:importar'), 'neexec');
        $this->add(new Zend_Acl_Resource('neexec:index'), 'neexec');

        // ************************************************************
        // menu Importações
        // ************************************************************
        // Importação
        $this->add(new Zend_Acl_Resource('importacao'));
        $this->add(new Zend_Acl_Resource('importacao:erros'), 'importacao');
        $this->add(new Zend_Acl_Resource('importacao:index'), 'importacao');
        // ************************************************************
        // menu Manutenção
        // ************************************************************
        // Permissao de acesso
        $this->add(new Zend_Acl_Resource('permissao'));
        $this->add(new Zend_Acl_Resource('permissao:detalhe'), 'permissao');
        $this->add(new Zend_Acl_Resource('permissao:editar'), 'permissao');
        $this->add(new Zend_Acl_Resource('permissao:excluir'), 'permissao');
        $this->add(new Zend_Acl_Resource('permissao:incluir'), 'permissao');
        $this->add(new Zend_Acl_Resource('permissao:index'), 'permissao');
        $this->add(new Zend_Acl_Resource('permissao:ajaxretornausuario'), 'permissao');
        // Categoria
        $this->add(new Zend_Acl_Resource('categoria'));
        $this->add(new Zend_Acl_Resource('categoria:detalhe'), 'categoria');
        $this->add(new Zend_Acl_Resource('categoria:editar'), 'categoria');
        $this->add(new Zend_Acl_Resource('categoria:excluir'), 'categoria');
        $this->add(new Zend_Acl_Resource('categoria:incluir'), 'categoria');
        $this->add(new Zend_Acl_Resource('categoria:index'), 'categoria');
        // Demandante
        $this->add(new Zend_Acl_Resource('demandante'));
        $this->add(new Zend_Acl_Resource('demandante:detalhe'), 'demandante');
        $this->add(new Zend_Acl_Resource('demandante:editar'), 'demandante');
        $this->add(new Zend_Acl_Resource('demandante:excluir'), 'demandante');
        $this->add(new Zend_Acl_Resource('demandante:incluir'), 'demandante');
        $this->add(new Zend_Acl_Resource('demandante:index'), 'demandante');
        // Natureza
        $this->add(new Zend_Acl_Resource('elemento'));
        $this->add(new Zend_Acl_Resource('elemento:ajaxelemento'), 'elemento');
        $this->add(new Zend_Acl_Resource('elemento:detalhe'), 'elemento');
        $this->add(new Zend_Acl_Resource('elemento:editar'), 'elemento');
        $this->add(new Zend_Acl_Resource('elemento:excluir'), 'elemento');
        $this->add(new Zend_Acl_Resource('elemento:incluir'), 'elemento');
        $this->add(new Zend_Acl_Resource('elemento:index'), 'elemento');
        // Fase licitação
        $this->add(new Zend_Acl_Resource('faselicitacao'));
        $this->add(new Zend_Acl_Resource('faselicitacao:detalhe'), 'faselicitacao');
        $this->add(new Zend_Acl_Resource('faselicitacao:editar'), 'faselicitacao');
        $this->add(new Zend_Acl_Resource('faselicitacao:excluir'), 'faselicitacao');
        $this->add(new Zend_Acl_Resource('faselicitacao:incluir'), 'faselicitacao');
        $this->add(new Zend_Acl_Resource('faselicitacao:index'), 'faselicitacao');
        // Esfera
        $this->add(new Zend_Acl_Resource('esfera'));
        $this->add(new Zend_Acl_Resource('esfera:detalhe'), 'esfera');
        $this->add(new Zend_Acl_Resource('esfera:editar'), 'esfera');
        $this->add(new Zend_Acl_Resource('esfera:excluir'), 'esfera');
        $this->add(new Zend_Acl_Resource('esfera:incluir'), 'esfera');
        $this->add(new Zend_Acl_Resource('esfera:index'), 'esfera');
        $this->add(new Zend_Acl_Resource('esfera:restaurar'), 'esfera');
        // Contrato
        $this->add(new Zend_Acl_Resource('contrato'));
        $this->add(new Zend_Acl_Resource('contrato:detalhe'), 'contrato');
        $this->add(new Zend_Acl_Resource('contrato:editar'), 'contrato');
        $this->add(new Zend_Acl_Resource('contrato:excluir'), 'contrato');
        $this->add(new Zend_Acl_Resource('contrato:incluir'), 'contrato');
        $this->add(new Zend_Acl_Resource('contrato:index'), 'contrato');
        $this->add(new Zend_Acl_Resource('contrato:restaurar'), 'contrato');
        // Evento
        $this->add(new Zend_Acl_Resource('evento'));
        $this->add(new Zend_Acl_Resource('evento:detalhe'), 'evento');
        $this->add(new Zend_Acl_Resource('evento:editar'), 'evento');
        $this->add(new Zend_Acl_Resource('evento:excluir'), 'evento');
        $this->add(new Zend_Acl_Resource('evento:incluir'), 'evento');
        $this->add(new Zend_Acl_Resource('evento:index'), 'evento');
        // Fonte
        $this->add(new Zend_Acl_Resource('fonte'));
        $this->add(new Zend_Acl_Resource('fonte:detalhe'), 'fonte');
        $this->add(new Zend_Acl_Resource('fonte:editar'), 'fonte');
        $this->add(new Zend_Acl_Resource('fonte:excluir'), 'fonte');
        $this->add(new Zend_Acl_Resource('fonte:incluir'), 'fonte');
        $this->add(new Zend_Acl_Resource('fonte:index'), 'fonte');
        // Objetivo
        $this->add(new Zend_Acl_Resource('objetivo'));
        $this->add(new Zend_Acl_Resource('objetivo:detalhe'), 'objetivo');
        $this->add(new Zend_Acl_Resource('objetivo:editar'), 'objetivo');
        $this->add(new Zend_Acl_Resource('objetivo:excluir'), 'objetivo');
        $this->add(new Zend_Acl_Resource('objetivo:incluir'), 'objetivo');
        $this->add(new Zend_Acl_Resource('objetivo:index'), 'objetivo');
        $this->add(new Zend_Acl_Resource('padraoug'));
        $this->add(new Zend_Acl_Resource('padraoug:detalhe'), 'padraoug');
        $this->add(new Zend_Acl_Resource('padraoug:editar'), 'padraoug');
        $this->add(new Zend_Acl_Resource('padraoug:excluir'), 'padraoug');
        $this->add(new Zend_Acl_Resource('padraoug:incluir'), 'padraoug');
        $this->add(new Zend_Acl_Resource('padraoug:index'), 'padraoug');
        // Programa
        $this->add(new Zend_Acl_Resource('programa'));
        $this->add(new Zend_Acl_Resource('programa:detalhe'), 'programa');
        $this->add(new Zend_Acl_Resource('programa:editar'), 'programa');
        $this->add(new Zend_Acl_Resource('programa:excluir'), 'programa');
        $this->add(new Zend_Acl_Resource('programa:incluir'), 'programa');
        $this->add(new Zend_Acl_Resource('programa:index'), 'programa');
        // PTRES
        $this->add(new Zend_Acl_Resource('ptres'));
        $this->add(new Zend_Acl_Resource('ptres:ajaxptres'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:detalhe'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:editar'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:excluir'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:incluir'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:index'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:excluidos'), 'ptres');
        $this->add(new Zend_Acl_Resource('ptres:restaurar'), 'ptres');
        // Responsável
        $this->add(new Zend_Acl_Resource('responsavel'));
        $this->add(new Zend_Acl_Resource('responsavel:ajaxlotacoesfilhas'), 'responsavel');
        $this->add(new Zend_Acl_Resource('responsavel:detalhe'), 'responsavel');
        $this->add(new Zend_Acl_Resource('responsavel:editar'), 'responsavel');
        $this->add(new Zend_Acl_Resource('responsavel:excluir'), 'responsavel');
        $this->add(new Zend_Acl_Resource('responsavel:incluir'), 'responsavel');
        $this->add(new Zend_Acl_Resource('responsavel:index'), 'responsavel');
        // Tipo de despesa
        $this->add(new Zend_Acl_Resource('tipodespesa'));
        $this->add(new Zend_Acl_Resource('tipodespesa:detalhe'), 'tipodespesa');
        $this->add(new Zend_Acl_Resource('tipodespesa:editar'), 'tipodespesa');
        $this->add(new Zend_Acl_Resource('tipodespesa:excluir'), 'tipodespesa');
        $this->add(new Zend_Acl_Resource('tipodespesa:incluir'), 'tipodespesa');
        $this->add(new Zend_Acl_Resource('tipodespesa:index'), 'tipodespesa');
        // Tipo de nota de crédito
        $this->add(new Zend_Acl_Resource('tiponc'));
        $this->add(new Zend_Acl_Resource('tiponc:detalhe'), 'tiponc');
        $this->add(new Zend_Acl_Resource('tiponc:editar'), 'tiponc');
        $this->add(new Zend_Acl_Resource('tiponc:excluir'), 'tiponc');
        $this->add(new Zend_Acl_Resource('tiponc:incluir'), 'tiponc');
        $this->add(new Zend_Acl_Resource('tiponc:index'), 'tiponc');
        // Tipo operacional
        $this->add(new Zend_Acl_Resource('tipooperacional'));
        $this->add(new Zend_Acl_Resource('tipooperacional:detalhe'), 'tipooperacional');
        $this->add(new Zend_Acl_Resource('tipooperacional:editar'), 'tipooperacional');
        $this->add(new Zend_Acl_Resource('tipooperacional:excluir'), 'tipooperacional');
        $this->add(new Zend_Acl_Resource('tipooperacional:incluir'), 'tipooperacional');
        $this->add(new Zend_Acl_Resource('tipooperacional:index'), 'tipooperacional');
        // Tipo orçamento
        $this->add(new Zend_Acl_Resource('tipoorcamento'));
        $this->add(new Zend_Acl_Resource('tipoorcamento:detalhe'), 'tipoorcamento');
        $this->add(new Zend_Acl_Resource('tipoorcamento:editar'), 'tipoorcamento');
        $this->add(new Zend_Acl_Resource('tipoorcamento:excluir'), 'tipoorcamento');
        $this->add(new Zend_Acl_Resource('tipoorcamento:incluir'), 'tipoorcamento');
        $this->add(new Zend_Acl_Resource('tipoorcamento:index'), 'tipoorcamento');
        // Tipo de recurso
        $this->add(new Zend_Acl_Resource('tiporecurso'));
        $this->add(new Zend_Acl_Resource('tiporecurso:detalhe'), 'tiporecurso');
        $this->add(new Zend_Acl_Resource('tiporecurso:editar'), 'tiporecurso');
        $this->add(new Zend_Acl_Resource('tiporecurso:excluir'), 'tiporecurso');
        $this->add(new Zend_Acl_Resource('tiporecurso:incluir'), 'tiporecurso');
        $this->add(new Zend_Acl_Resource('tiporecurso:index'), 'tiporecurso');
        // Tipo de solicitação
        $this->add(new Zend_Acl_Resource('tiposolicitacao'));
        $this->add(new Zend_Acl_Resource('tiposolicitacao:detalhe'), 'tiposolicitacao');
        $this->add(new Zend_Acl_Resource('tiposolicitacao:editar'), 'tiposolicitacao');
        $this->add(new Zend_Acl_Resource('tiposolicitacao:excluir'), 'tiposolicitacao');
        $this->add(new Zend_Acl_Resource('tiposolicitacao:incluir'), 'tiposolicitacao');
        $this->add(new Zend_Acl_Resource('tiposolicitacao:index'), 'tiposolicitacao');
        // UG
        $this->add(new Zend_Acl_Resource('ug'));
        $this->add(new Zend_Acl_Resource('ug:ajaxlotacoes'), 'ug');
        $this->add(new Zend_Acl_Resource('ug:ajaxug'), 'ug');
        $this->add(new Zend_Acl_Resource('ug:detalhe'), 'ug');
        $this->add(new Zend_Acl_Resource('ug:editar'), 'ug');
        $this->add(new Zend_Acl_Resource('ug:excluir'), 'ug');
        $this->add(new Zend_Acl_Resource('ug:incluir'), 'ug');
        $this->add(new Zend_Acl_Resource('ug:index'), 'ug');
        // UO
        $this->add(new Zend_Acl_Resource('uo'));
        $this->add(new Zend_Acl_Resource('uo:detalhe'), 'uo');
        $this->add(new Zend_Acl_Resource('uo:editar'), 'uo');
        $this->add(new Zend_Acl_Resource('uo:excluir'), 'uo');
        $this->add(new Zend_Acl_Resource('uo:incluir'), 'uo');
        $this->add(new Zend_Acl_Resource('uo:index'), 'uo');

        // LogDados
        $this->add(new Zend_Acl_Resource('logdados'));
        $this->add(new Zend_Acl_Resource('logdados:index'), 'logdados');

        // Vinculação
        $this->add(new Zend_Acl_Resource('vinculacao'));
        $this->add(new Zend_Acl_Resource('vinculacao:detalhe'), 'vinculacao');
        $this->add(new Zend_Acl_Resource('vinculacao:editar'), 'vinculacao');
        $this->add(new Zend_Acl_Resource('vinculacao:excluir'), 'vinculacao');
        $this->add(new Zend_Acl_Resource('vinculacao:incluir'), 'vinculacao');
        $this->add(new Zend_Acl_Resource('vinculacao:index'), 'vinculacao');

        // Licitação
        $this->add(new Zend_Acl_Resource('licitacao'));
        $this->add(new Zend_Acl_Resource('licitacao:detalhe'), 'licitacao');
        $this->add(new Zend_Acl_Resource('licitacao:editar'), 'licitacao');
        $this->add(new Zend_Acl_Resource('licitacao:excluir'), 'licitacao');
        $this->add(new Zend_Acl_Resource('licitacao:index'), 'licitacao');

        // ************************************************************
        // ...outros objetos obsoletos ou incorretos
        // ************************************************************
        // $this->add ( new Zend_Acl_Resource ( 'juiz' ) );
        // $this->add ( new Zend_Acl_Resource ( 'juiz:index', 'juiz' ) );
        // ************************************************************
        // Modulo transparencia CNJ - STEFANINI 2015
        // ************************************************************

        Orcamento_Business_Acl_TransparenciaCNJ::criarRecursos($this);
    }

    /**
     * Define os privilégios do sistema
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function definePrivilegios() {
        // Remove todas as permissões
        $this->deny(null);

        $this->allow(null, 'error');
        $this->allow(null, 'index:erro');

        // $this->deny ( 'consulta' );
        // Privilégios do perfil CONSULTA
        $this->allow('consulta', 'index');
        $this->allow('consulta', 'ajuda');
        $this->allow('consulta', 'error');
        $this->allow('consulta', 'projeto');
        $this->deny('consulta', 'projeto:cache');

        $this->allow('consulta', 'despesa');
        $this->deny('consulta', 'despesa:dashboard');
        $this->deny('consulta', 'despesa:editar');
        $this->deny('consulta', 'despesa:editarcontrato');
        $this->deny('consulta', 'despesa:excluir');
        $this->deny('consulta', 'despesa:incluir');
        $this->deny('consulta', 'despesa:trocaptres');
        $this->deny('consulta', 'despesa:trocafonte');
        $this->allow('consulta', 'rdo');
        $this->deny('consulta', 'rdo:editar');
        $this->deny('consulta', 'rdo:excluir');
        $this->deny('consulta', 'rdo:incluir');
        $this->allow('consulta', 'saldo');
        $this->allow('consulta', 'extrato');
        $this->allow('consulta', 'projecao');
        $this->deny('consulta', 'projecao:editar');

        $this->allow('consulta', 'perspectiva');

        $this->allow('consulta', 'macrodesafio');

        // ************************************************************
        // menu Captação
        // ************************************************************
        $this->allow('consulta', 'exercicio');
        $this->deny('consulta', 'exercicio:editar');
        $this->deny('consulta', 'exercicio:excluir');
        $this->deny('consulta', 'exercicio:incluir');
        $this->deny('consulta', 'exercicio:restaurar');
        $this->deny('consulta', 'exercicio:copiardespesas');
        $this->allow('consulta', 'informativo');
        $this->deny('consulta', 'informativo:editar');
        $this->deny('consulta', 'informativo:excluir');
        $this->deny('consulta', 'informativo:incluir');
        $this->deny('consulta', 'informativo:restaurar');
        $this->allow('consulta', 'regra');
        $this->deny('consulta', 'regra:aplicar');
        $this->deny('consulta', 'regra:editar');
        $this->deny('consulta', 'regra:excluir');
        $this->deny('consulta', 'regra:incluir');
        $this->deny('consulta', 'regra:restaurar');
        $this->allow('consulta', 'empenho');
        $this->deny('consulta', 'empenho:detalhe');

        $this->allow('consulta', 'importarnc');
        $this->allow('consulta', 'importarnc:editar');
        $this->allow('consulta', 'importarnc:excluir');
        $this->allow('consulta', 'importarnc:incluir');
        $this->allow('consulta', 'importarnc:restaurar');

        $this->allow('consulta', 'importarne');
        $this->allow('consulta', 'importarne:editar');
        $this->allow('consulta', 'importarne:excluir');
        $this->allow('consulta', 'importarne:incluir');
        $this->allow('consulta', 'importarne:restaurar');

        $this->allow('consulta', 'importaref');
        $this->allow('consulta', 'importaref:editar');
        $this->allow('consulta', 'importaref:excluir');
        $this->allow('consulta', 'importaref:incluir');
        $this->allow('consulta', 'importaref:restaurar');

        $this->allow('consulta', 'preempenho:index');
        
        $this->allow('desenvolvedor', 'preempenho');
        $this->allow('desenvolvedor', 'preempenho:index');
        $this->allow('desenvolvedor', 'preempenho:editar');
        $this->allow('desenvolvedor', 'preempenho:excluir');
        $this->allow('desenvolvedor', 'preempenho:incluir');
        $this->allow('desenvolvedor', 'preempenho:restaurar');

        $this->allow('dipor', 'preempenho');
        $this->allow('dipor', 'preempenho:index');
        $this->allow('dipor', 'preempenho:editar');
        $this->allow('dipor', 'preempenho:excluir');
        $this->allow('dipor', 'preempenho:incluir');
        $this->allow('dipor', 'preempenho:restaurar');        

        // ************************************************************
        // menu Solicitação
        // ************************************************************
        $this->allow('consulta', 'novadespesa');
        $this->deny('consulta', 'novadespesa:editar');
        $this->deny('consulta', 'novadespesa:excluir');
        $this->deny('consulta', 'novadespesa:incluir');

        $this->allow('consulta', 'movimentacaocrednova');
        $this->deny('consulta', 'movimentacaocrednova:editar');
        $this->deny('consulta', 'movimentacaocrednova:excluir');
        $this->deny('consulta', 'movimentacaocrednova:incluir');
        $this->deny('consulta', 'movimentacaocrednova:restaurar');

        // ************************************************************
        // menu Programação
        // ************************************************************
        $this->allow('consulta', 'pendencia');
        $this->allow('consulta', 'movimentacaocred');
        $this->deny('consulta', 'movimentacaocred:editar');
        $this->deny('consulta', 'movimentacaocred:excluir');
        $this->deny('consulta', 'movimentacaocred:incluir');
        $this->allow('consulta', 'recursodesc');
        $this->deny('consulta', 'recursodesc:editar');
        $this->deny('consulta', 'recursodesc:excluir');
        $this->deny('consulta', 'recursodesc:incluir');
        $this->allow('consulta', 'recursofase');
        $this->deny('consulta', 'recursofase:editar');
        $this->deny('consulta', 'recursofase:excluir');
        $this->deny('consulta', 'recursofase:incluir');
        $this->allow('consulta', 'bloqueio');
        $this->deny('consulta', 'bloqueio:editar');
        $this->deny('consulta', 'bloqueio:excluir');
        $this->deny('consulta', 'bloqueio:incluir');
        $this->allow('consulta', 'travaprojecao');
        $this->deny('consulta', 'travaprojecao:editar');
        $this->deny('consulta', 'travaprojecao:excluir');
        $this->deny('consulta', 'travaprojecao:incluir');

        // ************************************************************
        // menu SIAFI
        // ************************************************************
        $this->allow('consulta', 'credito');
        $this->deny('consulta', 'credito:editar');
        $this->deny('consulta', 'credito:excluir');
        $this->deny('consulta', 'credito:incluir');
        $this->deny('consulta', 'credito:restaurar');
        $this->allow('consulta', 'nc');
        $this->deny('consulta', 'nc:editar');
        $this->deny('consulta', 'nc:importar');
        $this->deny('consulta', 'nc:excluir');
        $this->deny('consulta', 'ne:excluir');
        $this->allow('consulta', 'ne');
        $this->deny('consulta', 'ne:editar');
        $this->deny('consulta', 'ne:importar');
        $this->allow('consulta', 'neexec');
        //$this->deny ( 'consulta', 'neexec:importar' );
        $this->deny('consulta', 'importacao');

        // ************************************************************
        // menu Manutenção
        // ************************************************************
        $this->allow('consulta', 'categoria');
        $this->deny('consulta', 'categoria:editar');
        $this->deny('consulta', 'categoria:excluir');
        $this->deny('consulta', 'categoria:incluir');
        $this->allow('consulta', 'demandante');
        $this->deny('consulta', 'demandante:editar');
        $this->deny('consulta', 'demandante:excluir');
        $this->deny('consulta', 'demandante:incluir');
        $this->allow('consulta', 'elemento');
        $this->deny('consulta', 'elemento:editar');
        $this->deny('consulta', 'elemento:excluir');
        $this->deny('consulta', 'elemento:incluir');
        $this->allow('consulta', 'esfera');
        $this->deny('consulta', 'esfera:editar');
        $this->deny('consulta', 'esfera:excluir');
        $this->deny('consulta', 'esfera:incluir');
        $this->allow('consulta', 'evento');
        $this->deny('consulta', 'evento:editar');
        $this->deny('consulta', 'evento:excluir');
        $this->deny('consulta', 'evento:incluir');
        $this->deny('consulta', 'faselicitacao:editar');
        $this->deny('consulta', 'faselicitacao:excluir');
        $this->deny('consulta', 'faselicitacao:incluir');
        $this->allow('consulta', 'licitacao');
        $this->deny('consulta', 'licitacao:editar');
        $this->deny('consulta', 'licitacao:excluir');
        $this->allow('consulta', 'fonte');
        $this->deny('consulta', 'fonte:editar');
        $this->deny('consulta', 'fonte:excluir');
        $this->deny('consulta', 'fonte:incluir');
        $this->allow('consulta', 'objetivo');
        $this->deny('consulta', 'objetivo:editar');
        $this->deny('consulta', 'objetivo:excluir');
        $this->deny('consulta', 'objetivo:incluir');
        $this->allow('consulta', 'padraoug');
        $this->deny('consulta', 'padraoug:editar');
        $this->deny('consulta', 'padraoug:excluir');
        $this->deny('consulta', 'padraoug:incluir');
        $this->allow('consulta', 'permissao');
        $this->deny('consulta', 'permissao:editar');
        $this->deny('consulta', 'permissao:excluir');
        $this->deny('consulta', 'permissao:incluir');
        $this->allow('consulta', 'programa');
        $this->deny('consulta', 'programa:editar');
        $this->deny('consulta', 'programa:excluir');
        $this->deny('consulta', 'programa:incluir');
        $this->allow('consulta', 'ptres');
        $this->deny('consulta', 'ptres:editar');
        $this->deny('consulta', 'ptres:excluir');
        $this->deny('consulta', 'ptres:incluir');
        $this->allow('consulta', 'responsavel');
        $this->deny('consulta', 'responsavel:editar');
        $this->deny('consulta', 'responsavel:excluir');
        $this->deny('consulta', 'responsavel:incluir');
        $this->allow('consulta', 'tipodespesa');
        $this->deny('consulta', 'tipodespesa:editar');
        $this->deny('consulta', 'tipodespesa:excluir');
        $this->deny('consulta', 'tipodespesa:incluir');
        $this->allow('consulta', 'tiponc');
        $this->deny('consulta', 'tiponc:editar');
        $this->deny('consulta', 'tiponc:excluir');
        $this->deny('consulta', 'tiponc:incluir');
        $this->allow('consulta', 'tipooperacional');
        $this->deny('consulta', 'tipooperacional:editar');
        $this->deny('consulta', 'tipooperacional:excluir');
        $this->deny('consulta', 'tipooperacional:incluir');
        $this->allow('consulta', 'tipoorcamento');
        $this->deny('consulta', 'tipoorcamento:editar');
        $this->deny('consulta', 'tipoorcamento:excluir');
        $this->deny('consulta', 'tipoorcamento:incluir');
        $this->allow('consulta', 'tiporecurso');
        $this->deny('consulta', 'tiporecurso:editar');
        $this->deny('consulta', 'tiporecurso:excluir');
        $this->deny('consulta', 'tiporecurso:incluir');
        $this->allow('consulta', 'tiposolicitacao');
        $this->deny('consulta', 'tiposolicitacao:editar');
        $this->deny('consulta', 'tiposolicitacao:excluir');
        $this->deny('consulta', 'tiposolicitacao:incluir');
        $this->allow('consulta', 'ug');
        $this->allow('consulta', 'ug:ajaxug');
        $this->deny('consulta', 'ug:editar');
        $this->deny('consulta', 'ug:excluir');
        $this->deny('consulta', 'ug:incluir');
        $this->allow('consulta', 'uo');
        $this->deny('consulta', 'uo:editar');
        $this->deny('consulta', 'uo:excluir');
        $this->deny('consulta', 'uo:incluir');
        $this->allow('consulta', 'logdados');
        $this->allow('consulta', 'vinculacao');
        $this->deny('consulta', 'vinculacao:editar');
        $this->deny('consulta', 'vinculacao:excluir');
        $this->deny('consulta', 'vinculacao:incluir');

        $this->allow('consulta', 'justificativa');
        $this->deny('consulta', 'justificativa:editar');
        $this->deny('consulta', 'justificativa:excluir');
        $this->deny('consulta', 'justificativa:incluir');
        $this->deny('consulta', 'justificativa:restaurar');

        // $this->deny ( 'consulta', 'juiz' );
        // Privilégios do perfil DIEFI
        $this->allow('diefi', 'ne:editar');

        // Privilégios do perfil Planejamento
        $this->allow('planejamento', 'despesa:editar');
        $this->allow('planejamento', 'despesa:excluir');
        $this->allow('planejamento', 'despesa:incluir');
        $this->allow('planejamento', 'despesa:trocaptres');
        $this->allow('planejamento', 'despesa:trocafonte');

        $this->allow('planejamento', 'exercicio:editar');
        $this->allow('planejamento', 'exercicio:excluir');
        $this->allow('planejamento', 'exercicio:incluir');
        $this->allow('planejamento', 'exercicio:restaurar');
        $this->allow('planejamento', 'exercicio:copiardespesas');

        $this->allow('planejamento', 'informativo:editar');
        $this->allow('planejamento', 'informativo:excluir');
        $this->allow('planejamento', 'informativo:incluir');
        $this->allow('planejamento', 'informativo:restaurar');

        $this->allow('planejamento', 'empenho:detalhe');

        $this->allow('seccional', 'novadespesa');
        $this->allow('seccional', 'novadespesa:index');
        $this->allow('seccional', 'novadespesa:detalhe');
        $this->allow('seccional', 'novadespesa:editar');
        $this->allow('seccional', 'novadespesa:excluir');
        $this->allow('seccional', 'novadespesa:incluir');
        $this->allow('seccional', 'novadespesa:ajaxnovadespesa');

        $this->allow('planejamento', 'novadespesa');
        $this->allow('planejamento', 'novadespesa:index');
        $this->allow('planejamento', 'novadespesa:detalhe');
        $this->allow('planejamento', 'novadespesa:editar');
        $this->allow('planejamento', 'novadespesa:excluir');
        $this->allow('planejamento', 'novadespesa:incluir');
        $this->allow('planejamento', 'novadespesa:ajaxnovadespesa');

        $this->allow('planejamento', 'regra:aplicar');
        $this->allow('planejamento', 'regra:editar');
        $this->allow('planejamento', 'regra:excluir');
        $this->allow('planejamento', 'regra:incluir');
        $this->allow('planejamento', 'regra:restaurar');

        $this->allow('planejamento', 'justificativa');
        $this->allow('planejamento', 'justificativa:editar');
        $this->allow('planejamento', 'justificativa:excluir');
        $this->allow('planejamento', 'justificativa:incluir');
        $this->allow('planejamento', 'justificativa:restaurar');

        $this->allow('planejamento', 'projecao:execucaomensal');

        $this->allow('planejamento', 'solicitacaoajuste');
        $this->allow('planejamento', 'solicitacaoajuste:index');
        $this->allow('planejamento', 'solicitacaoajuste:detalhe');
        $this->allow('planejamento', 'solicitacaoajuste:editar');
        $this->allow('planejamento', 'solicitacaoajuste:excluir');
        $this->allow('planejamento', 'solicitacaoajuste:incluir');

        // Privilégios do perfil Secretaria
        $this->allow('secretaria', 'despesa:editarcontrato');
        $this->deny('secretaria', 'despesa:editar');
        $this->allow('secretaria', 'projecao:editar');
        $this->allow('secretaria', 'projecaojustificativa');
        $this->deny('secretaria', 'ne:editar');

        $this->allow('dipor', 'novadespesa');
        $this->allow('dipor', 'novadespesa:index');
        $this->allow('dipor', 'novadespesa:detalhe');
        $this->allow('dipor', 'novadespesa:editar');
        $this->allow('dipor', 'novadespesa:excluir');
        $this->allow('dipor', 'novadespesa:incluir');
        $this->allow('dipor', 'novadespesa:ajaxnovadespesa');
        
        $this->allow('desenvolvedor', 'novadespesa');
        $this->allow('desenvolvedor', 'novadespesa:index');
        $this->allow('desenvolvedor', 'novadespesa:detalhe');
        $this->allow('desenvolvedor', 'novadespesa:editar');
        $this->allow('desenvolvedor', 'novadespesa:excluir');
        $this->allow('desenvolvedor', 'novadespesa:incluir');
        $this->allow('desenvolvedor', 'novadespesa:ajaxnovadespesa');

        $this->allow('secretaria', 'movimentacaocrednova:editar');
        $this->allow('secretaria', 'movimentacaocrednova:excluir');
        $this->allow('secretaria', 'movimentacaocrednova:incluir');
        $this->allow('secretaria', 'movimentacaocrednova:restaurar');
        $this->allow('secretaria', 'projecao:execucaomensal');

        $this->allow('secretaria', 'justificativa');
        $this->allow('secretaria', 'justificativa:editar');
        $this->allow('secretaria', 'justificativa:excluir');
        $this->allow('secretaria', 'justificativa:incluir');
        $this->allow('secretaria', 'justificativa:restaurar');

        $this->allow('secretaria', 'rdo:editar');
        $this->allow('secretaria', 'rdo:excluir');
        $this->allow('secretaria', 'rdo:incluir');
        $this->allow('secretaria', 'rdo:detalhe');

        $this->allow('secretaria', 'contrato:index');
        $this->allow('secretaria', 'contrato:incluir');
        $this->allow('secretaria', 'contrato:editar');
        $this->allow('secretaria', 'contrato:excluir');
        $this->allow('secretaria', 'contrato:detalhe');
        $this->allow('secretaria', 'contrato:restaurar');

        // Privilégios do perfil Secretaria (Reserva)
        // nenhuma diferença
        // Privilégios do perfil Seccional
        $this->allow('seccional', 'rdo:editar');
        $this->allow('seccional', 'rdo:excluir');
        $this->allow('seccional', 'rdo:incluir');
        $this->allow('seccional', 'ne:editar');
        $this->allow('seccional', 'despesa:editar');
        $this->allow('seccional', 'novadespesa');
        $this->allow('seccional', 'novadespesa:index');
        $this->allow('seccional', 'novadespesa:detalhe');
        $this->allow('seccional', 'novadespesa:editar');
        $this->deny('seccional', 'novadespesa:excluir');
        $this->allow('seccional', 'novadespesa:incluir');
        $this->allow('seccional', 'novadespesa:ajaxnovadespesa');

        $this->allow('seccional', 'recursodesc:editar');

        $this->allow('seccional', 'solicitacaoajuste');
        $this->allow('seccional', 'solicitacaoajuste:index');
        $this->allow('seccional', 'solicitacaoajuste:detalhe');
        $this->allow('seccional', 'solicitacaoajuste:editar');
        $this->allow('seccional', 'solicitacaoajuste:excluir');
        $this->allow('seccional', 'solicitacaoajuste:incluir');

        $this->allow('seccional', 'licitacao');
        $this->allow('seccional', 'licitacao:editar');
        $this->allow('seccional', 'licitacao:excluir');

        // Privilégios do perfil DIPOR
        // CNJ
        $this->allow('dipor', 'alinea:index');
        $this->allow('dipor', 'alinea:editar');
        $this->allow('dipor', 'alinea:excluir');
        $this->allow('dipor', 'alinea:incluir');
        $this->allow('dipor', 'alinea:importar');

        $this->allow('dipor', 'inciso:index');
        $this->allow('dipor', 'inciso:editar');
        $this->allow('dipor', 'inciso:excluir');
        $this->allow('dipor', 'inciso:incluir');
        $this->allow('dipor', 'inciso:importar');

        $this->allow('dipor', 'regracnj:index');
        $this->allow('dipor', 'regracnj:editar');
        $this->allow('dipor', 'regracnj:excluir');
        $this->allow('dipor', 'regracnj:incluir');
        $this->allow('dipor', 'regracnj:importar');

        $this->allow('dipor', 'importarfinanceiro:index');
        $this->allow('dipor', 'importarfinanceiro:editar');
        $this->allow('dipor', 'importarfinanceiro:excluir');
        $this->allow('dipor', 'importarfinanceiro:incluir');
        $this->allow('dipor', 'importarfinanceiro:importar');

        $this->allow('dipor', 'importarsuplementacao:index');
        $this->allow('dipor', 'importarsuplementacao:editar');
        $this->allow('dipor', 'importarsuplementacao:excluir');
        $this->allow('dipor', 'importarsuplementacao:incluir');
        $this->allow('dipor', 'importarsuplementacao:importar');

        $this->allow('dipor', 'importarliquidado:index');
        $this->allow('dipor', 'importarliquidado:editar');
        $this->allow('dipor', 'importarliquidado:excluir');
        $this->allow('dipor', 'importarliquidado:incluir');
        $this->allow('dipor', 'importarliquidado:importar');

        $this->allow('dipor', 'importardotacao:index');
        $this->allow('dipor', 'importardotacao:editar');
        $this->allow('dipor', 'importardotacao:excluir');
        $this->allow('dipor', 'importardotacao:incluir');
        $this->allow('dipor', 'importardotacao:importar');

        $this->allow('dipor', 'importarcancelamento:index');
        $this->allow('dipor', 'importarcancelamento:editar');
        $this->allow('dipor', 'importarcancelamento:excluir');
        $this->allow('dipor', 'importarcancelamento:incluir');
        $this->allow('dipor', 'importarcancelamento:importar');

        $this->allow('dipor', 'importarcontingenciamento:index');
        $this->allow('dipor', 'importarcontingenciamento:editar');
        $this->allow('dipor', 'importarcontingenciamento:excluir');
        $this->allow('dipor', 'importarcontingenciamento:incluir');
        $this->allow('dipor', 'importarcontingenciamento:importar');

        $this->allow('dipor', 'importarprovisao:index');
        $this->allow('dipor', 'importarprovisao:editar');
        $this->allow('dipor', 'importarprovisao:excluir');
        $this->allow('dipor', 'importarprovisao:incluir');
        $this->allow('dipor', 'importarprovisao:importar');

        $this->allow('dipor', 'importardestaque:index');
        $this->allow('dipor', 'importardestaque:editar');
        $this->allow('dipor', 'importardestaque:excluir');
        $this->allow('dipor', 'importardestaque:incluir');
        $this->allow('dipor', 'importardestaque:importar');

        $this->allow('dipor', 'importarempenhado:index');
        $this->allow('dipor', 'importarempenhado:editar');
        $this->allow('dipor', 'importarempenhado:excluir');
        $this->allow('dipor', 'importarempenhado:incluir');
        $this->allow('dipor', 'importarempenhado:importar');

        $this->allow('dipor', 'importarpago:index');
        $this->allow('dipor', 'importarpago:editar');
        $this->allow('dipor', 'importarpago:excluir');
        $this->allow('dipor', 'importarpago:incluir');
        $this->allow('dipor', 'importarpago:importar');

        $this->allow('dipor', 'importarrestosapagar:index');
        $this->allow('dipor', 'importarrestosapagar:editar');
        $this->allow('dipor', 'importarrestosapagar:excluir');
        $this->allow('dipor', 'importarrestosapagar:incluir');
        $this->allow('dipor', 'importarrestosapagar:importar');

        $this->allow('dipor', 'gerarrelatoriocnj:index');
        $this->allow('dipor', 'gerarrelatoriocnj:relatorio');

        $this->allow('dipor', 'importarverificarcnj:index');
        $this->allow('dipor', 'importarverificarcnj:ajaxverificarimportado');
        $this->allow('dipor', 'despesa:editar');
        $this->allow('dipor', 'despesa:excluir');
        $this->allow('dipor', 'despesa:incluir');
        $this->allow('dipor', 'despesa:trocaptres');
        $this->allow('dipor', 'despesa:trocafonte');

        $this->allow('dipor', 'informativo:index');
        $this->allow('dipor', 'informativo:listagem');
        $this->allow('dipor', 'informativo:incluir');
        $this->allow('dipor', 'informativo:detalhe');
        $this->allow('dipor', 'informativo:editar');
        $this->allow('dipor', 'informativo:excluir');
        $this->allow('dipor', 'informativo:restaurar');
        $this->allow('dipor', 'informativo:leitura');

        $this->allow('dipor', 'empenho:detalhe');

        $this->allow('dipor', 'exercicio:incluir');
        $this->allow('dipor', 'exercicio:editar');

        $this->allow('dipor', 'movimentacaocred:editar');
        $this->allow('dipor', 'movimentacaocred:excluir');
        $this->allow('dipor', 'movimentacaocred:incluir');
        $this->allow('dipor', 'recursodesc:editar');
        $this->allow('dipor', 'recursodesc:excluir');
        $this->allow('dipor', 'recursodesc:incluir');
        $this->allow('dipor', 'recursofase:editar');
        $this->allow('dipor', 'recursofase:excluir');
        $this->allow('dipor', 'recursofase:incluir');
        $this->allow('dipor', 'bloqueio:editar');
        $this->allow('dipor', 'bloqueio:excluir');
        $this->allow('dipor', 'bloqueio:incluir');
        $this->allow('dipor', 'travaprojecao');
        $this->allow('dipor', 'travaprojecao:index');
        $this->allow('dipor', 'travaprojecao:editar');
        $this->allow('dipor', 'travaprojecao:excluir');
        $this->allow('dipor', 'travaprojecao:incluir');

        $this->allow('dipor', 'credito:editar');
        $this->allow('dipor', 'credito:excluir');
        $this->allow('dipor', 'credito:incluir');
        $this->allow('dipor', 'credito:restaurar');
        $this->allow('dipor', 'nc:editar');
        $this->allow('dipor', 'nc:importar');
        $this->allow('dipor', 'nc:detalhe');
        $this->allow('dipor', 'nc:excluir');
        $this->allow('dipor', 'ne:excluir');
        $this->allow('dipor', 'ne:importar');
        $this->allow('dipor', 'importacao');
        $this->allow('dipor', 'importacao:index');
        $this->allow('dipor', 'importacao:erros');

        $this->allow('dipor', 'categoria:editar');
        $this->allow('dipor', 'categoria:excluir');
        $this->allow('dipor', 'categoria:incluir');
        $this->allow('dipor', 'demandante:editar');
        $this->allow('dipor', 'demandante:excluir');
        $this->allow('dipor', 'demandante:incluir');
        $this->allow('dipor', 'elemento:editar');
        $this->allow('dipor', 'elemento:excluir');
        $this->allow('dipor', 'elemento:incluir');
        $this->allow('dipor', 'esfera:editar');
        $this->allow('dipor', 'esfera:excluir');
        $this->allow('dipor', 'esfera:incluir');
        $this->allow('dipor', 'evento:editar');
        $this->allow('dipor', 'evento:excluir');
        $this->allow('dipor', 'evento:incluir');
        $this->allow('dipor', 'faselicitacao:editar');
        $this->allow('dipor', 'faselicitacao:excluir');
        $this->allow('dipor', 'faselicitacao:incluir');
        $this->allow('dipor', 'licitacao');
        $this->allow('dipor', 'licitacao:editar');
        $this->allow('dipor', 'licitacao:excluir');
        $this->allow('dipor', 'fonte:editar');
        $this->allow('dipor', 'fonte:excluir');
        $this->allow('dipor', 'fonte:incluir');
        $this->allow('dipor', 'objetivo:editar');
        $this->allow('dipor', 'objetivo:excluir');
        $this->allow('dipor', 'objetivo:incluir');
        $this->allow('dipor', 'padraoug:editar');
        $this->allow('dipor', 'padraoug:excluir');
        $this->allow('dipor', 'padraoug:incluir');
        $this->allow('dipor', 'permissao:editar');
        $this->allow('dipor', 'permissao:excluir');
        $this->allow('dipor', 'permissao:incluir');
        $this->allow('dipor', 'permissao:ajaxretornausuario');
        $this->allow('dipor', 'perspectiva');
        $this->allow('dipor', 'programa:editar');
        $this->allow('dipor', 'programa:excluir');
        $this->allow('dipor', 'programa:incluir');
        $this->allow('dipor', 'ptres:editar');
        $this->allow('dipor', 'ptres:excluir');
        $this->allow('dipor', 'ptres:incluir');
        $this->allow('dipor', 'ptres:excluidos');
        $this->allow('dipor', 'responsavel:editar');
        $this->allow('dipor', 'responsavel:excluir');
        $this->allow('dipor', 'responsavel:incluir');
        $this->allow('dipor', 'tipodespesa:editar');
        $this->allow('dipor', 'tipodespesa:excluir');
        $this->allow('dipor', 'tipodespesa:incluir');
        $this->allow('dipor', 'tiponc:editar');
        $this->allow('dipor', 'tiponc:excluir');
        $this->allow('dipor', 'tiponc:incluir');
        $this->allow('dipor', 'tipooperacional:editar');
        $this->allow('dipor', 'tipooperacional:excluir');
        $this->allow('dipor', 'tipooperacional:incluir');
        $this->allow('dipor', 'tipoorcamento:editar');
        $this->allow('dipor', 'tipoorcamento:excluir');
        $this->allow('dipor', 'tipoorcamento:incluir');
        $this->allow('dipor', 'tiporecurso:editar');
        $this->allow('dipor', 'tiporecurso:excluir');
        $this->allow('dipor', 'tiporecurso:incluir');
        $this->allow('dipor', 'tiposolicitacao:editar');
        $this->allow('dipor', 'tiposolicitacao:excluir');
        $this->allow('dipor', 'tiposolicitacao:incluir');
        $this->allow('dipor', 'ug:editar');
        $this->allow('dipor', 'ug:excluir');
        $this->allow('dipor', 'ug:incluir');
        $this->allow('dipor', 'uo:editar');
        $this->allow('dipor', 'uo:excluir');
        $this->allow('dipor', 'uo:incluir');
        $this->allow('dipor', 'vinculacao:editar');
        $this->allow('dipor', 'vinculacao:excluir');
        $this->allow('dipor', 'vinculacao:incluir');

        $this->allow('dipor', 'justificativa');
        $this->allow('dipor', 'justificativa:editar');
        $this->allow('dipor', 'justificativa:excluir');
        $this->allow('dipor', 'justificativa:incluir');
        $this->allow('dipor', 'justificativa:restaurar');

        // ---------------------------------------------------------------------
        // Privilégios do perfil Desenvolvedor
        $this->allow('desenvolvedor');
        $this->allow('desenvolvedor', 'projeto:cache');

        $this->allow('desenvolvedor', 'exercicio:excluir');
        $this->allow('desenvolvedor', 'exercicio:incluir');
        $this->allow('desenvolvedor', 'exercicio:restaurar');
        $this->allow('desenvolvedor', 'exercicio:copiardespesas');

        $this->allow('desenvolvedor', 'movimentacaocrednova:index');
        $this->allow('desenvolvedor', 'movimentacaocrednova:editar');
        $this->allow('desenvolvedor', 'movimentacaocrednova:excluir');
        $this->allow('desenvolvedor', 'movimentacaocrednova:incluir');
        $this->allow('desenvolvedor', 'movimentacaocrednova:restaurar');

        $this->allow('desenvolvedor', 'regra:aplicar');
        $this->allow('desenvolvedor', 'regra:editar');
        $this->allow('desenvolvedor', 'regra:excluir');
        $this->allow('desenvolvedor', 'regra:incluir');
        $this->allow('desenvolvedor', 'regra:restaurar');

        $this->allow('desenvolvedor', 'informativo:incluir');
        $this->allow('desenvolvedor', 'informativo:editar');
        $this->allow('desenvolvedor', 'informativo:leitura');

        $this->allow('desenvolvedor', 'empenho:detalhe');
        $this->allow('desenvolvedor', 'projecao:execucaomensal');

        $this->allow('desenvolvedor', 'justificativa');
        $this->allow('desenvolvedor', 'justificativa:editar');
        $this->allow('desenvolvedor', 'justificativa:excluir');
        $this->allow('desenvolvedor', 'justificativa:incluir');
        $this->allow('desenvolvedor', 'justificativa:restaurar');

        // ************************************************************
        // Modulo transparencia CNJ - STEFANINI 2015
        // ************************************************************

        Orcamento_Business_Acl_TransparenciaCNJ::criarPrivilegios($this);

        // ************************************************************
    }

    /**
     * Define todos os usuários que tem acesso ao sistema, bem como define o
     * perfil de cada um e a lotação (UGs) que estes podem observar
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function defineUsuarios() {

        $negocioPermissao = new Orcamento_Business_Negocio_Permissao();
        $permUsuarios = $negocioPermissao->retornaUsuarios();

        $permissaoCeo = array();
        foreach ($permUsuarios as $user) {
            $negocioUnge = new Trf1_Orcamento_Negocio_Ug();
            if ($user['PERM_CD_UNIDADE_GESTORA'] == 99999) {
                $ug = 'todas';
            } else {
                $ug = $negocioUnge->retornaRegistro($user["PERM_CD_UNIDADE_GESTORA"]);
                $ug = $ug["UNGE_SG_SECAO"];
            }

            $permissaoCeo[strtolower($user['PERM_CD_MATRICULA'])] = array(
                'nivel' => $user['PERM_DS_PERFIL'],
                'ug' => $ug,
                'responsavel' => $user['PERM_DS_RESPONSABILIDADE'],
            );
        }

        // Define, manualmente, as permissões do e-Orçamento ( OBSOLETO )

        // Devolve o array de permissões
        return $permissaoCeo;
    }

}
//        $permissaoCeo = array(
//            // Desenvolvedores e-Admin
//            'tr17496ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* ANDERSON SATHLER - Líder do projeto */
//            'tr18940ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Gesley Batista Rodrigues */
//            'tr17358ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Sérgio Paiva Leitão */
//            'tr18757ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Carlos Alexandre Parma Queiroz */
//            'tr18921ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Danilo da Silva */
//            'tr17958ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Marcelo Caixeta Rocha */
//            'tr370ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Juliana Queiroz de Castro */
//            'tr17528ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Robson Pereira */
//            'tr19209ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Victor Eduardo Barreto */
//            'tr19228ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Sandro */
//            'tr19220ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Gilberto */
//            'tr19223ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Ricardo */
//
//            // Analistas de Requisitos e/ou Equipe de testes
//            'tr18739ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Jhonathan Abreu de Sousa */
//            'tr19005ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Karina Neves Fortuna - Requisitos */
//            'tr19009ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Rúbia Piassi Dalvi Meriguete - Requisitos */
//            'tr19032ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Jorge Eduardo O. Improissi - Requisitos */
//            'tr19048ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Rodrigo Albuquerque Lôbo - Requisitos */
//            'tr19085ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Douglas Nicolini Bezerra - Requisitos */
//            'tr19006ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Felipe Gustavo França Barbosa - Requisitos */
//            'tr18848ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Fabrício - Testes */
//            'am0001' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Matricula de Teste - Testes */
//
//            // Servidores e Gestores
//            'tr300876' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Alex Pitacci Simões */
//            'tr300818' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Gilmar Nonato dos Santos */
//
//            // Permissões revogadas e/ou Profissionais desligados
//            'FORA_DE_PRAZO_tr227ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Usuário de homologação - Concedido, excepcionalmente, em 12/08 para uso da Ceily. DATA DE REVOGAÇÃO: 30/09/2013! */
//            'NULO_tr17539ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Luiz Mendes de Moraes Junior */
//            'NULO_tr18077ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Dayane Oliveira Freire */
//            'NULO_tr18092ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Diego Pablo Alves Rodrigues */
//            'NULO_tr18157ps' => array('nivel' => 'desenvolvedor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Robson dos Reis da Siva */
//            'NULO_tr18482ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Leidison Siqueira Barbosa */
//            'NULO_tr18483ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Rodrigo Mariano Rodrigues  */
//            'NULO_tr18484ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Daniel Rodrigues Fernandes */
//            'NULO_tr18522ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Ceily Cristina Alves dos Santos */
//            'NULO_tr18692ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Claudia Regina dos Santos */
//            'NULO_tr18856ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Wellington - Teste */
//            'NULO_tr18891ps' => array('nivel' => 'consulta', 'ug' => 'mg', 'responsavel' => 'todos'), /* Bárbara - Teste */
//            'NULO_tr18899ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Danielle Alves Medeiros */
//            'NULO_tr61203' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/COJEF/TRF1'), /* Sergio Nunes Guedes */
//
//            // DIPOR
//            'tr58203' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Adelson Vieira Torres */
//            'tr300135' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Silvânia Renata Almeida Sereno de Sousa */
//            'tr172503' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Marília Geremias de Freitas */
//            'tr15303' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Dalva Gomes Franco */
//            'tr19011ps' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Gustavo de Araújo Pereira Dias - Atualizado matricula tr23302es para tr19011ps 02/05/2014  */
//            'tr2603' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Silvio Rogério da Silva Gomes */
//            'tr281ps' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Welber Lopes de Oliveira */
//            'tr300360' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Barcelônea de Fátima Feitosa */
//            'tr300758' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Marilu Silva de Oliveira Pinheiro */
//            'tr300792' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Gilberto Gonçalves Santos */
//            'tr48003' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Maria Reis Silveira Braga Costa */
//            'tr135903' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* Mariano Pereira dos Santos Junior */
//            'tr300177' => array('nivel' => 'dipor', 'ug' => 'todas', 'responsavel' => 'todos'), /* WOLFGANG DE OLIVEIRA MATIAS PEREIRA */
//
//            // DIEFI
//            'tr110003' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* João Barbosa Lopes */
//            'tr13003' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* José Galébio de Aguiar Rocha */
//            'tr300267' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Nilton Fagundes Viriato */
//            'tr300832' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Fabrício de Lucca Jardim */
//            'tr301007' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Neide Barbosa da Silva */
//            'tr37403' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Geovania Carneiro de Lima */
//            'tr37503' => array('nivel' => 'diefi', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Linalice Fontenele Pereira */
//
//            // Secretarias do TRF - SECAD
//            'tr123803' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Denise Mindello de Andrade */
//            'tr124703' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Maria Aparecida de Sousa Mendes */
//            'tr136203' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Luiz Alberto Lima da Costa */
//            'tr300040' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Nilcelio Jose Estrela Rodrigues */
//            'tr300288' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Maria Cristina Turnes */
//            'tr300377' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Waleska Ribeiro Penna Pereira */
//            'tr300413' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Regina Célia Costa da Cunha */
//            'tr300765' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Luiz Maurício Penna da Costa */
//            'tr300827' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Leonardo Peter da Silva */
//            'tr72503' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Nilda Aparecida Alves */
//            'tr78303' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Rene Soares da Silva */
//            'tr90503' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Luiz Otavio Campello Montezuma */
//            'tr63903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Janderson Casado de Vasconcelos */
//            'tr179603' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/TRF1'), /* Renia Alves Machado Carlini */
//
//            // Secretarias do TRF - /ASCOM/SEGEP/TRF1
//            'tr72403' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/ASCOM/SEGEP/TRF1'), /* Ivani Luiz de Morais */
//            'tr194303' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/ASCOM/SEGEP/TRF1'), /* Mara Lucia Martins de Araujo Bessa */
//            'tr84803' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/ASCOM/SEGEP/TRF1'), /* Ramon da Silva Pereira */
//            'tr10805ps' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/ASCOM/SEGEP/TRF1'), /* José Miguel Pereira dos Reis */
//
//            // Secretarias do TRF - /DIGRA/CENAG/DIGES/TRF1
//            // Secretarias do TRF - /NUGRA/DIEDI/SECGE/TRF1
//            'tr164003' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/NUGRA/DIEDI/SECGE/TRF1'), /* Hernani Dutra Vilela */
//
//            // Secretarias do TRF - /DIEDI/CENAG/DIGES/TRF1
//            'tr3903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIEDI/CENAG/DIGES/TRF1'), /* Josiane Santos Batista */
//            'tr197103' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIEDI/SECGE/TRF1'), /* Samuel Nunes dos Santos */
//
//            // Secretarias do TRF - /DIAMI/COJUD/DIGES/TRF1
//            'tr300234' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIAMI/COJUD/DIGES/TRF1'), /* Sibonei Soares Ferreira */
//            'tr91503' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIAMI/COJUD/DIGES/TRF1'), /* Ana Claudia Cordeiro Lima */
//
//            // Secretarias do TRF - /SECBE/TRF1
//            'tr300370' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/SECBE/TRF1'), /* Larissa Craveiro e Silva Abad */
//            'tr49303' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/SECBE/TRF1'), /* Conceição de Maria Pereira de Carvalho */
//            'tr94103' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/SECBE/TRF1'), /* Rosane Carvalho Trevisan */
//
//            // Secretarias do TRF - /DIBIB/COJUD/DIGES/TRF1
//            'tr300710' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIBIB/COJUD/DIGES/TRF1'), /* Marília de Souza de Mello */
//            'tr99403' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIBIB/COJUD/DIGES/TRF1'), /* Márcia Mazo Santos */
//
//            // Secretarias do TRF - /DICOM/SECAD/TRF1
//            'tr300721' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DICOM/SECAD/TRF1'), /* Marco Antônio França */
//            'tr180903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DICOM/SECAD/TRF1'), /* Roberta Araujo de Mello Bezerra */
//
//            // Secretarias do TRF - /DIENG/SECAD/TRF1
//            'tr300558' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Euzébio Sá Cavaignac Neto */
//            'tr300791' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Paloma Leal Coutinho Boros */
//            'tr300586' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Rodrigo Pinto de Menezes */
//            'tr87503' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* José Arnaldo Martins Costa */
//            'tr187603' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Antonio Jorge Leitão */
//            'tr190003' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Regis Oliveira Sales */
//            'tr153903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Rosana de Jesus Braga Severino */
//            'tr172103' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Francisco de Assis L. FIlho */
//            'tr111003' => array('nivel' => 'seccional', 'ug' => 'tr', 'responsavel' => '/DIENG/SECAD/TRF1'), /* Carlos de Braga e Queiroz  */
//
//            // Secretarias do TRF - SELET/DIENG/SECAD/TRF1
//            'tr4703' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => 'SELET/DIENG/SECAD/TRF1'), /* Matuzalém Braga dos Santos */
//
//            // Secretarias do TRF - /DIMAP/SECAD/TRF1
//            'tr126203' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIMAP/SECAD/TRF1'), /* Adriana Pinho Rocha - Alterado da Direh para Dimap --sosti 2014010001108011080160000068 */
//            'tr54403' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIMAP/SECAD/TRF1'), /* José Maria de Andrade */
//            'tr63203' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIMAP/SECAD/TRF1'), /* Paulo Cesar Machado Sena */
//
//            // Secretarias do TRF - /DISEG/SECAD/TRF1
//            'tr181103' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Adelmo dos Santos Lombardi Balbo */
//            'tr194903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Gláucio Braga Assis */
//            'tr25003' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Fabiano Costa Lucindo */
//            'tr25303' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Antônio Felicíssimo Neto */
//            'tr26903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* João Maria de Medeiros */
//            'tr300079' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Márcio Antônio Oliveira Fonseca */
//            'tr300266' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Adilson Pinto Araújo */
//            'tr31903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Cláudio Chagas Barreira */
//            'tr43803' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DISEG/SECAD/TRF1'), /* Diógenes Cristiano dos Santos */
//
//            // Secretarias do TRF - /DIREH/SECRE/TRF1
//            'tr152203' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Patrícia Helen Fielding Lóssio */
//            'tr23695es' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Daniel Carlos Carlheiros do Nascimento */
//            'tr300932' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Rafael Canhete Lopes Filho */
//            'tr300654' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Vanessa Rodrigues Barbosa Siqueira */
//            'tr62403' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Vera Lúcia Costa Rabello Mendes */
//            'tr62503' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Márcio da Silva Albuquerque */
//            'tr300889' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/DIREH/SECRE/TRF1'), /* Monica Valéria Avila Gomes */
//
//            // Secretarias do TRF - /COJEF/TRF1
//            'tr153203' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/COJEF/TRF1'), /* Claúdio Faustino Alves de Castro */
//            'tr300521' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/COJEF/TRF1'), /* Elaine Cristina Danzmann */
//            'tr300775' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/COJEF/TRF1'), /* Wânia Marítiça Araújo Vieira */
//            'tr86003' => array('nivel' => 'secretaria_reserva', 'ug' => 'tr', 'responsavel' => '/COJEF/TRF1'), /* Sandra Maria Alves Borges da Costa */
//
//            // Secretarias do TRF - /CORIP/SECJU/TRF1
//            'tr146203' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/CORIP/SECJU/TRF1'), /* Nelsilia Maria Ladeira Luniêre de Sousa */
//
//            // Secretarias do TRF - /COTAQ/SECJU/TRF1
//            'tr85303' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/COTAQ/SECJU/TRF1'), /* Denivaldo Francico da Silva */
//            'tr61603' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/COTAQ/SECJU/TRF1'), /* Eliezita Borges Camimura */
//            'tr147703' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/COTAQ/SECJU/TRF1'), /* Júlia Beckman Meirelles */
//            'tr40603' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/COTAQ/SECJU/TRF1'), /* Maria Auxiliadora */
//            'tr68103' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/COTAQ/SECJU/TRF1'), /* Maristela Resende Costa */
//
//            // Secretarias do TRF - ...mas sem despesas ou com identificação incorreta do campo SG_FAMILIA_RESPONSAVEL */
//            'tr300029' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/CPL/TRF1'), /* Elizete Ferreira Costa */
//            'tr119903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/CPL/TRF1'), /* Maria Aparecida Lima da Silva */
//            'tr110903' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIACO/TRF1'), /* Webes Ribeiro da Silva */
//            'tr300781' => array('nivel' => 'secretaria', 'ug' => 'tr', 'responsavel' => '/DIVAO/TRF1'), /* Frederico Augusto de Almeida Santos Vellenich */
//
//            // SEPLO
//            'ma46403' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* JANETTE MIA KATO YOKOKURA [2015010001108011080160000030] */
//
//            // Mg
//            'mg1011111' => array('nivel' => 'secretaria', 'ug' => 'mg', 'responsavel' => '/SJMG'), /* VANISE MARIA REZENDE [2015010001108011080160000047] */
//            'mg1011111' => array('nivel' => 'secretaria', 'ug' => 'mg', 'responsavel' => '/SJMG'), /* VANISE MARIA REZENDE [2015010001108011080160000047] */
//            'ap20192' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /*  PAULO NAZARENO LAGOIA FONSECA JUNIOR  */
//            // Am
//            'am19203' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Mário César de Queiroz Albuquerque  */
//            'am200275' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Lúcio Ribeiro Gomes   */
//            'am200194' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Rômulo Rodrigues Ferreira   */
//            'am24403' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Adelson Alves Silva  */
//            'am200236' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Cláudio Fabiano Valente Mortágua   */
//            'am200151' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Daniel Rodrigues de Oliveira  */
//            'am200009' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Marly Guimarães Gomes */
//            'am200150' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Ivaney Ferreira Pereira   */
//            'am200253' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => '/SJAM'), /*  Thiago Dias Carneiro  */
//            'am26503' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => 'todos'), /*  DORA MENDONÇA GRANJA  */
//
//            // Go
//            // 'go14703' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => 'todos'), /*   RACHEL BARBO DE SIQUEIRA ANDRADE */
//            // Seccionais
//            'ac11003' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Sâmia Milena Araújo de Souza */
//            'ac18903' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Edivaldo Venancio da Silva */
//            'ac5803' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Josimar Antonia Mourão do Nascimenot */
//            'ac7803' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Ernedite Gadelha Cavalcante dos Santos */
//            'ac6103' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Ernedite Gadelha Cavalcante dos Santos */
//            'ac30011' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Ana Marcia da Costa Santiago */
//            'am200113' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => 'todos'), /* Paulo Guillermo Fernandez Piedade */
//            'am21603' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => 'todos'), /* Renan de Barros Alves */
//            'ap20037' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Francisco Clednei Alves Carneiro */
//            'ap20038' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Domingos Campos Ribeiro */
//            'ap20107' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Marcelo Tomé de Lima */
//            'ap20156' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Josue Moraes Estrela */
//            'ap20181' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Denilson Leite Gomes */
//            'ap6803' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Robson Cardoso Borges */
//            'ap168es' => array('nivel' => 'seccional', 'ug' => 'ap', 'responsavel' => 'todos'), /* Marilia Belo Torres */
//            'ba2000053' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Cristina Maria Dantas Lessa Cortes */
//            'ba200262' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Izauro de Souza Ferreira Jr. Matricula antiga -> ba2000262 Matricula informada no sosti -> ba200262 */
//            'ba2000439' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Fabiana Souza Araújo de Lima */
//            'ba2000573' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Victor Emmanuel Guimarães da Silva */
//            'ba359503' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Selma Silva Santos */
//            'ba368603' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Maria Eugênia Ribeiro Lage */
//            'ba375403' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Emílio Paim Otero */
//            'ba612103' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Maria das Graças Caires Santos */
//            'df1289803' => array('nivel' => 'seccional', 'ug' => 'df', 'responsavel' => 'todos'), /* José Maria Lopes Mota */
//            'df1400001' => array('nivel' => 'seccional', 'ug' => 'df', 'responsavel' => 'todos'), /* Horst Wessel Von Daudt Mohn */
//            'df1400057' => array('nivel' => 'seccional', 'ug' => 'df', 'responsavel' => 'todos'), /* Nelson Carvalho da Silva */
//            'go1024es' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => 'todos'), /* Josiane Galdino da Silva */
//            'go1173es' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => 'todos'), /* Sheila Batista Lima */
//            'go27503' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => 'todos'), /* Maura Alves Pinto */
//            'go75803' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => 'todos'), /* Marzia Muro Monteiro de Castro */
//            'ma33403' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* Braitner Izaias Cunho do Nascimento */
//            'ma430es' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* Renato Miranda Feitosa */
//            'ma51307' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* Ricardo Luís da Silva */
//            'ma52124' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* Luis Mendes de Castro Filho */
//            'ma528es' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* Camila Maria */
//            'mg1010099' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Glaúcia Maria Machado Rocha Ribeiro */
//            'mg162203 ' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Orlando Pedro Souto Ferreira */
//            'mg1010460' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Sílvio Nascimento de Abreu Bueno */
//            'mg1010879' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Luciana Kroehling de Moura */
//            'mg107103' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Ângela Maria da Silva Coelho de Rezende */
//            'mt19203' => array('nivel' => 'seccional', 'ug' => 'mt', 'responsavel' => 'todos'), /* Ivaldo Bernardes Júnior */
//            'mt27903' => array('nivel' => 'seccional', 'ug' => 'mt', 'responsavel' => 'todos'), /* Diana Gonçalina Rondon Marques */
//            'pa11103' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Marcos Antonio Marçal de Lima */
//            'pa3203' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* José Rubens dos Prazares Maia */
//            'pa698es' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Antonia Adriele Rabelo Nascimento de Sousa */
//            'pa763es' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Nicolas Augusto Andre Nazareth */
//            'pa816es' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Acácio Silva de Souza */
//            'pa898es' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Maria Monteiro Pereira */
//            'pa979es' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Josiane Zacarias da Penha */
//            'pi21503' => array('nivel' => 'seccional', 'ug' => 'pi', 'responsavel' => 'todos'), /* Carmem Dolores Floriano Siqueira Silveira */
//            'pi6103' => array('nivel' => 'seccional', 'ug' => 'pi', 'responsavel' => 'todos'), /* Maria Rosângela Cunha Leite Costa */
//            'ro10603' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Márcio Pontes Moura */
//            'ro380077' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Ozivaldo Gomes Velozo */
//            'ro9703' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Jaqueline Menezes */
//            // 'ro18703' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Elke Reni Carvalho de Sa */
//            'ro380152' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Vanessa Monteiro Rocha */
//            'ro380213' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Rosilene Miranda Costa 2015010001108011080160000101 */
//            'rr20028' => array('nivel' => 'seccional', 'ug' => 'rr', 'responsavel' => 'todos'), /* Nílton Dall'agnol */
//            'rr20034' => array('nivel' => 'seccional', 'ug' => 'rr', 'responsavel' => 'todos'), /* Paulo Esdras Costa Gonçalves */
//            'rr5103' => array('nivel' => 'seccional', 'ug' => 'rr', 'responsavel' => 'todos'), /* Ana Lúcia de Oliveira */
//            // 'to20073' => array ( 'nivel' => 'seccional', 'ug' => 'to', 'responsavel' => 'todos' ), /* Luciana Kroehling de Moura */
//            'to20155' => array('nivel' => 'seccional', 'ug' => 'to', 'responsavel' => 'todos'), /* Artur Vilchez */
//            'to403' => array('nivel' => 'seccional', 'ug' => 'to', 'responsavel' => 'todos'), /* Keila Aguiar Costa */
//            'ba200439' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Fabiana Souza Araújo De Lima 2015010001101011010160000049 */
//            'ba200573' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Victor Emmanuel Guimarãe da Silva 2015010001101011010160000049 */
//            'go41203' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => 'todos'), /* Clecio Bezerra Nunes Jr. 2015010001101011010160000049 */
//            'mg10108790' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Luciana Kroehling de Moura 2015010001101011010160000049 */
//            'to20140' => array('nivel' => 'seccional', 'ug' => 'to', 'responsavel' => 'todos'), /* Carmelúce Freitas da Cruz 2015010001101011010160000049 */
//
//            // Consulta - SECOR e DIPOF
//            'tr103303' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Ruy Meneses Graça Júnior */
//            'tr113503' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* André Luis Silva da Cunha */
//            'tr173003' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Tiarajú Paulo Souza */
//            'tr17642ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Wendell Reis Degaut Pontes */
//            'tr18873ps' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Leon Rocha Melo */
//            'tr23316es' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Rodolfo Oliveira da Silva */
//            'tr23653es' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Rodrigo Gomes de Paula Nogueira */
//            'tr300026' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Eduardo Vieira de Oliveira */
//            'tr300644' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* José Augusto Mochel Matos Pereira Lima */
//            'tr300759' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Roberto Elias Cavalcante */
//            'tr45103' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Carlos Frederico Maia Bezerra */
//            'tr65003' => array('nivel' => 'consulta', 'ug' => 'todas', 'responsavel' => 'todos'), /* Luciene de Sousa Marques */
//
//            // Planejamento - DIPLA
//            'tr17723ps' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* ANA CLARICE DE OLIVEIRA SILVA  2015010001108011080160000086 */
//            'tr102503' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Marisa Alves dos Santos Brandão */
//            'tr161703' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Nádia Barbosa da Cruz Santana */
//            'tr163403' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* José Carlos Viana */
//            'tr166203' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Fernanda de Carvalho Dias Salazar */
//            'tr186303' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Rosariana Maria de Oliveira */
//            'tr24029es' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Emanoelle Estrela Feitosa */
//            'tr300695' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Antonino dos Santos Mourão Filho */
//            'tr58503' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* José Andrade Filho */
//            // 'ANTIGA_tr70203' => array ('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos' ), /* Kátia Regina Ribeiro de Santa Ana */
//            'tr300908' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Kátia Regina Ribeiro de Santa Ana - mudou de matricula de TR70203 para a matrícula TR300908, pediu a solicitação via e-mail com ciência do Agros, em 12/09/2013 */
//            'tr18990ps' => array('nivel' => 'planejamento', 'ug' => 'todas', 'responsavel' => 'todos'), /* Williani Tomaz Rocha Vicenzo */
//
//            // Consulta - Demais diretores e/ou interessados
//            'ac10303' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Gilmar Palú */
//            'ac6103' => array('nivel' => 'seccional', 'ug' => 'ac', 'responsavel' => 'todos'), /* Josoé Alves de Albuquerque */
//            'am9503' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => 'todos'), /* Auxiliadora Maria Negreiros do Couto Alves */
//            'am16603' => array('nivel' => 'consulta', 'ug' => 'am', 'responsavel' => 'todos'), /* Edson Souza e Silva */
//            'am200221' => array('nivel' => 'consulta', 'ug' => 'am', 'responsavel' => 'todos'), /* Suelen de Souza */
//            'ap20041' => array('nivel' => 'consulta', 'ug' => 'ap', 'responsavel' => 'todos'), /* José James Dias Coelho */
//            'ba326403' => array('nivel' => 'seccional', 'ug' => 'ba', 'responsavel' => 'todos'), /* Katia Vasconcelos Arnold */
//            'ba399903' => array('nivel' => 'consulta', 'ug' => 'ba', 'responsavel' => 'todos'), /* Elizabete Regina Campelo Dias */
//            'go80200' => array('nivel' => 'consulta', 'ug' => 'go', 'responsavel' => 'todos'), /* Denison Rocha Montoro */
//            'ma4403' => array('nivel' => 'seccional', 'ug' => 'ma', 'responsavel' => 'todos'), /* Kalina Valéria Bastos Pedroza */
//            'mg56503' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Eliene de Fátima Jaques Coutinho */
//            'ju235' => array('nivel' => 'consulta', 'ug' => 'mt', 'responsavel' => 'todos'), /* Paulo Cezar Alves Sodré */
//            'mt29903' => array('nivel' => 'consulta', 'ug' => 'mt', 'responsavel' => 'todos'), /* Maria Cecília da Silva da Costa */
//            'mt36225' => array('nivel' => 'consulta', 'ug' => 'mt', 'responsavel' => 'todos'), /* Brenda Sanches Suli */
//            'pa43303' => array('nivel' => 'consulta', 'ug' => 'pa', 'responsavel' => 'todos'), /* Keila Viviane Vilar de Paiva */
//            'pa7803' => array('nivel' => 'consulta', 'ug' => 'pa', 'responsavel' => 'todos'), /* José Luiz Miranda Rodrigues */
//            'pi7303' => array('nivel' => 'consulta', 'ug' => 'pi', 'responsavel' => 'todos'), /* Marcia Regina dos Santos Costa Viana */
//            'pi16103' => array('nivel' => 'consulta', 'ug' => 'pi', 'responsavel' => 'todos'), /* Edvaldo Rodrigues da Silva */
//            'pi5703' => array('nivel' => 'consulta', 'ug' => 'pi', 'responsavel' => 'todos'), /* José Ribamar Rodrigues do Monte */
//            'ro14103' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Igor Silva */
//            'ro15403' => array('nivel' => 'consulta', 'ug' => 'ro', 'responsavel' => 'todos'), /* Newton Matos Filho */
//            'ro5803' => array('nivel' => 'consulta', 'ug' => 'ro', 'responsavel' => 'todos'), /* Marcos Aurélio Barreto de Paula */
//            'ro7303' => array('nivel' => 'seccional', 'ug' => 'ro', 'responsavel' => 'todos'), /* Luzival Correia Ferreira */
//            'ro8403' => array('nivel' => 'consulta', 'ug' => 'ro', 'responsavel' => 'todos'), /* Waldirney Guimarães de Resende */
//            'rr20007' => array('nivel' => 'consulta', 'ug' => 'rr', 'responsavel' => 'todos'), /* Antônio Santana de Sousa Júnior */
//            'rr20012' => array('nivel' => 'consulta', 'ug' => 'rr', 'responsavel' => 'todos'), /* Luiza Cristina Firmino de Freitas */
//            'rr20075' => array('nivel' => 'consulta', 'ug' => 'rr', 'responsavel' => 'todos'), /* Geraldo Ronismar Ribeiro Ferreira */
//            'rr3203' => array('nivel' => 'consulta', 'ug' => 'rr', 'responsavel' => 'todos'), /* Ana Gardene Costa Golçalves */
//            'to10103' => array('nivel' => 'consulta', 'ug' => 'to', 'responsavel' => 'todos'), /* Ricardo Antonio Nogueira Pereira */
//            'tr136603' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => 'todos'), /* Aldenes Almeida Machado */
//            'tr124103' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Yuri Oliveira de Andrade Freitas */
//            'tr153303' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Jonatas Izídio dos Santos */
//            'tr300641' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Humberto José Xavier */
//            'tr300764' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Selma Maria Costa Póvoa Araújo */
//            'tr300777' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Ricardo Guimarães de Almeida */
//            'tr300780' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Roberto Petruff */
//            'tr47403' => array('nivel' => 'consulta', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /* Mario de Sena Braga Júnior */
//            'tr47303' => array('nivel' => 'seccional', 'ug' => 'tr', 'responsavel' => '/SECIN/TRF1'), /*  GERALDO AFONSO DOS SANTOS SILVA */
//
//            //SEPOF
//            'ro380260' => array('nivel' => 'dipor', 'ug' => 'ro', 'responsavel' => 'todos'), /* Cinara Salvi de Oliveira */
//            'pa44103' => array('nivel' => 'consulta', 'ug' => 'pa', 'responsavel' => 'todos'), /* Crispiniano Ribeiro de Almeida */
//
//            //SEPLO
//            //AM
//            'am23903' => array('nivel' => 'seccional', 'ug' => 'am', 'responsavel' => 'todos'), /* Osvaldo Catunda de Borba troca de perfil 2015010001108011080160000093 */
//            //GO
//            'go14703' => array('nivel' => 'seccional', 'ug' => 'go', 'responsavel' => '/SJGO'), /*   RACHEL BARBO DE SIQUEIRA ANDRADE */
//            //MG
//            'mg97403' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => '/SJMG'), /* Wanderlene Maria Santos Brandão */
//            'mg1010055' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => '/SJMG'), /* CLESIO PEREIRA NEVES */
//
//            //PA
//            'pa1132es' => array('nivel' => 'seccional', 'ug' => 'todas', 'responsavel' => 'todos'), /*  EMILLY IASMIN DA SILVA CHAVES */
//            /* Solicitado responsavel = todos para os perfis abaixo: SOSTI 2015010001218012180160000008 */
//            'pa1000731' => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* ADRIANA DE SOUSA DOS SANTOS [2015010001108011080160000036] */
//            'pa22003'   => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Francisco Tadeu Olivera Santos SOSTI 2015010001108011080160000062 */
//            'pa1058es'  => array('nivel' => 'seccional', 'ug' => 'pa', 'responsavel' => 'todos'), /* Autorizado perfil secretaria para estagiária  EMMANUELLE DA SILVA GATO [2015010001108011080160000033] */
//            'pa9703'    => array('nivel' => 'seccional', 'ug' => 'todas', 'responsavel' => 'todos'), /* Tania Luna Serruya Maia Jauffret */
//            //'pa16703 '  => array('nivel' => 'consulta', '  ug' => 'pa', 'responsavel' => '/SJPA'), /* Removido pelo sosti 2015010001218012180160000008 * Edivaldo de Souza Paes Barreto  */
//            // SEOFI
//            // MG
//            'mg158703' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /* Jozelina Maria de Araújo  */
//            'mg1010207' => array('nivel' => 'seccional', 'ug' => 'mg', 'responsavel' => 'todos'), /*  Lilian Ribeiro de Oliveira  */
//        );
