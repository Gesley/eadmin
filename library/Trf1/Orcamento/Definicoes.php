<?php

/**
 * Classe genérica de definições, padrões e formatos
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Definicoes
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
final class Trf1_Orcamento_Definicoes
{
    /*     * ***********************************************************
     * FORMATOS
     * *********************************************************** */

    /**
     * Formato de dinheiro para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DINHEIRO . "') AS ...
     * 
     * @var		FORMATO_DINHEIRO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DINHEIRO = 'FML9G999G999G999G999G999G990D00';

    /**
     * Formato de dinheiro para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "') AS ...
     * 
     * @var		FORMATO_NUMERO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_NUMERO = 'FM9G999G999G999G999G999G990D00';

    /**
     * Formato de data para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS ...
     * 
     * @var		FORMATO_DATA string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA = 'DD/MM/YYYY';

    /**
     * Formato de hora para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS ...
     * 
     * @var		FORMATO_HORA string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_HORA = 'HH24-MI-SS';

    /**
     * Formato de data e hora para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS ...
     * 
     * @var		FORMATO_DATA string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA_HORA = 'DD/MM/YYYY HH24:MI:SS';

    /**
     * Formato de data para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_INVERTIDA_COM_TRACO . "') AS ...
     * 
     * @var		FORMATO_DATA_INVERTIDA_COM_TRACO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA_INVERTIDA_COM_TRACO = 'YYYY-MM-DD';

    /**
     * Formato de data para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_INVERTIDA . "') AS ...
     * 
     * @var		FORMATO_DATA_HORA_INVERTIDA	string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA_HORA_INVERTIDA = 'YYYY-MM-DD HH24:MI:SS';

    /**
     * Formato de data para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_INVERTIDA . "') AS ...
     * 
     * @var     FORMATO_DATA_HORA_INVERTIDA string (constant)
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA_HORA_TRACO = 'DD-MM-YYYY HH24:MI:SS';

    /**
     * Formato de data para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_SEM_SEPARADORES . "') AS ...
     * 
     * @var		FORMATO_DATA_SEM_SEPARADORES string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA_INVERTIDA_SEM_SEPARADORES = 'YYYYMMDD';

    /**
     * Formato de data e hora (sem separadores) para função ORACLE
     * @example TO_CHAR(CAMPO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS ...
     * 
     * @var		FORMATO_DATA string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const FORMATO_DATA_HORA_SEM_SEPARADORES = 'YYYYMMDDHH24MISS';

    /*     * ***********************************************************
     * NOMES DO MÓDULO
     * *********************************************************** */

    /**
     * Nome do módulo específico do sistema
     * 
     * @var		NOME_MODULO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NOME_MODULO = 'orcamento';

    /*     * ***********************************************************
     * NOMES PADRÃOS DAS ACTIONS
     * *********************************************************** */

    /**
     * Nome padrão da Action inicial (ou quando não informada)
     * 
     * @var		ACTION_INDEX string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const ACTION_INDEX = 'index';

    /**
     * Nome padrão da Action de inclusão de registros
     * 
     * @var		ACTION_INCLUIR string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const ACTION_INCLUIR = 'incluir';

    /**
     * Nome padrão da Action de alteração de um registro
     * 
     * @var		ACTION_EDITAR string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const ACTION_EDITAR = 'editar';

    /**
     * Nome padrão da Action de exclusão de registros
     * 
     * @var		ACTION_EXCLUIR string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const ACTION_EXCLUIR = 'excluir';

    /**
     * Nome padrão da Action para exibição de detalhes do registro
     * 
     * @var		ACTION_DETALHE string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const ACTION_DETALHE = 'detalhe';

    /*     * ***********************************************************
     * NOMES PADRÃOS DAS CLASSES UTILIZADAS NOS BOTOES
     * *********************************************************** */

    /**
     * Nome padrão da Classe (class) dos botões de inclusão de dados
     * 
     * @var		CLASSE_INCLUIR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_INCLUIR = 'ceo_novo';

    /**
     * Nome padrão da Classe (class) dos botões de edição de dados
     * 
     * @var		CLASSE_EDITAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_EDITAR = 'ceo_editar';

    /**
     * Nome padrão da Classe (class) dos botões de exclusão de dados
     * 
     * @var		CLASSE_EXCLUIR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_EXCLUIR = 'ceo_excluir';

    /**
     * Nome padrão da Classe (class) dos botões para visualização detalhada de dados
     * 
     * @var		CLASSE_DETALHAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_DETALHAR = 'ceo_detalhar';

    /**
     * Nome padrão da Classe (class) dos botões para salvamento de dados
     * 
     * @var		CLASSE_SALVAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_SALVAR = 'ceo_salvar';

    /**
     * Nome padrão da Classe (class) dos botões para importação de dados
     * 
     * @var		CLASSE_IMPORTAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_IMPORTAR = 'ceo_importar';

    /**
     * Nome padrão da Classe (class) dos botões de confirmação de operações
     * 
     * @var		CLASSE_CONFIRMAR		string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_CONFIRMAR = 'ceo_confirmar';

    /**
     * Nome padrão da Classe (class) dos botões de não confirmação de operações
     * 
     * @var		CLASSE_NEGAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_NEGAR = 'ceo_negar';

    /**
     * Nome padrão da Classe (class) dos botões para consulta de dados
     * 
     * @var		CLASSE_CONSULTAR		string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_CONSULTAR = 'ceo_consultar';

    /**
     * Nome padrão da Classe (class) dos botões para pesquisa de dados
     * 
     * @var		CLASSE_PESQUISAR		string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_PESQUISAR = 'ceo_pesquisar';

    /**
     * Nome padrão da Classe (class) dos botões para cancelamento de operações
     * 
     * @var		CLASSE_CANCELAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_CANCELAR = 'ceo_cancelar';

    /**
     * Nome padrão da Classe (class) para não permitir edição de dados no controle
     * 
     * @var		CLASSE_APENAS_LEITURA	string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_APENAS_LEITURA = 'ceo_readonly';

    /**
     * Nome padrão da Classe (class) para voltar a uma determinada página
     * 
     * @var		CLASSE_VOLTAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_VOLTAR = 'ceo_voltar';

    /**
     * Nome padrão da Classe (class) para voltar a uma determinada página
     * 
     * @var		CLASSE_VOLTAR			string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const CLASSE_RELATORIO = 'ceo_relatorio';

    /*     * ***********************************************************
     * RESPOSTAS PADRÃO PARA AÇÕES DE BANCO
     * *********************************************************** */

    /**
     * Resposta padrão após sucesso na alteração de registro no banco.
     * 
     * @var		MENSAGEM_ALTERAR_SUCESSO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_ALTERAR_SUCESSO = 'Registro alterado com sucesso.';

    /**
     * Resposta padrão após erro na alteração de registro no banco.
     * 
     * @var		MENSAGEM_ALTERAR_ERRO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_ALTERAR_ERRO = 'Alteração de registro.';

    /**
     * Resposta padrão após sucesso na importação de dados externos no banco.
     * 
     * @var		MENSAGEM_IMPORTAR_SUCESSO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_IMPORTAR_SUCESSO = 'Importação realizada com sucesso.';

    /**
     * Resposta padrão após erro na importação de dados externos no banco.
     * 
     * @var		MENSAGEM_IMPORTAR_ERRO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_IMPORTAR_ERRO = 'Importação de dados externos.';

    /**
     * Resposta padrão após sucesso na inclusão de registro no banco.
     * 
     * @var		MENSAGEM_INCLUIR_SUCESSO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_INCLUIR_SUCESSO = 'Registro incluído com sucesso.';

    /**
     * Resposta padrão após erro na inclusão de registro no banco.
     * 
     * @var		MENSAGEM_INCLUIR_ERRO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_INCLUIR_EXISTENTE = 'Alteração cancelada, registro já existe!';

    /**
     * Resposta padrão após erro na inclusão de registro no banco.
     * 
     * @var		MENSAGEM_INCLUIR_ERRO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_INCLUIR_ERRO = 'Inclusão de registro.';

    /**
     * Pergunta padrão para confirmação de um ou mais registro selecionado - Utilizado para ação em lote
     * 
     * @var		MENSAGEM_EXCLUIR_PERGUNTA string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_EXCLUIR_PERGUNTA = 'Confirma exclusão dos registros selecionados?';

    /**
     * Resposta padrão após sucesso na exclusão de registro no banco.
     * 
     * @var		MENSAGEM_EXCLUIR_SUCESSO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_EXCLUIR_SUCESSO = 'Registro(s) excluído(s) com sucesso.';

    /**
     * Resposta padrão após sucesso em varias ocasioes do caso Manter Exercicio.
     * 
     * @var		MENSAGEM_EXCLUIR_SUCESSO string (constant)
     * @author	Gesley Rodrigues [rodrigues.gesley@gmail.com]
     */
    const MENSAGEM_SUCESSO_EXERCICIO = 'Operação realizada com sucesso!';

    /**
     * Resposta padrão após erro na exclusão de registro no banco.
     * 
     * @var		MENSAGEM_EXCLUIR_ERRO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_EXCLUIR_ERRO = 'Exclusão de registro(s).';

    /**
     * Resposta padrão após cancelamento na operação de exclusão.
     * 
     * @var		MENSAGEM_EXCLUIR_CANCELAR string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_EXCLUIR_CANCELAR = 'Exclusão cancelada.';

    /**
     * Resposta padrão após banco não retornar o registro (ou conjunto de) esperado.
     * 
     * @var		MENSAGEM_REGISTRO_NAO_ENCONTRADO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_REGISTRO_NAO_ENCONTRADO = 'Registro não encontrado ou você não possui privilégios para acessá-lo.';

    /**
     * Resposta padrão antes de ida ao banco por ausência de código (chave-primária ou composta).
     * 
     * @var		MENSAGEM_CODIGO_NAO_INFORMADO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const MENSAGEM_CODIGO_NAO_INFORMADO = 'Código não informado.';

    /*     * ***********************************************************
     * PADRÃO DO E-ORÇAMENTO COMPARTILHADOS POR OUTROS MÓDULOS / APLICAÇÕES
     * *********************************************************** */

    /**
     * Texto inicial padrão de qualquer perfil para o e-Orçamento utilizado no e-Guardião
     * 
     * @var		TEXTO_INICIAL_PADRAO_PERFIL_ORCAMENTO string (constant)
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    /* const TEXTO_INICIAL_PADRAO_PERFIL_ORCAMENTO		= 'CEO-'; */
    const TEXTO_INICIAL_PADRAO_PERFIL_ORCAMENTO = 'Orçamento - ';

    /*     * ***********************************************************
     * NÍVEIS DE PERMISSÃO
     * *********************************************************** */

    /**
     * Sem nível de permissão! Deve apresentar acesso negado ou voltar para alguma tela básica
     *
     * @var		NIVEL_PERMISSAO_SEM_ACESSO		int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NIVEL_PERMISSAO_SEM_ACESSO = 0;

    /**
     * Nível de permissão DIPOR, acesso total ao e-Orçamento
     *
     * @var		NIVEL_PERMISSAO_DIPOR			int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NIVEL_PERMISSAO_DIPOR = 1;

    /**
     * Nível de permissão apenas para consulta. Necessário informar lotação.
     *
     * @var		NIVEL_PERMISSAO_CONSULTA		int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NIVEL_PERMISSAO_CONSULTA = 2;

    /**
     * Nível de permissão de uma dada seccional. Necessário informar lotação.
     *
     * @var		NIVEL_PERMISSAO_SECCIONAL		int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NIVEL_PERMISSAO_SECCIONAL = 3;

    /**
     * Nível de permissão de uma dada secretaria do TRF. Necessário informar lotação.
     *
     * @var		NIVEL_PERMISSAO_TRF_SECRETARIA	int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NIVEL_PERMISSAO_TRF_SECRETARIA = 4;

    /**
     * Nível de permissão específica para a DIEFI do TRF. Necessário informar lotação.
     *
     * @var		NIVEL_PERMISSAO_TRF_DIEFI		int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const NIVEL_PERMISSAO_TRF_DIEFI = 5;

    /**
     * Informar nível de permissão conforme tipo.
     *
     * @var		NIVEL_PERMISSAO_DIPOR,NIVEL_PERMISSAO_DESENVOLVEDOR	int
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const PERMISSAO_DIPOR = 'dipor';
    const PERMISSAO_DESENVOLVEDOR = 'desenvolvedor';
    const PERMISSAO_DIEFI = 'diefi';
    const PERMISSAO_SECRETARIA = 'secretaria';
    const PERMISSAO_SECCIONAL = 'seccional';
    const PERMISSAO_CONSULTA = 'consulta';
    const PERMISSAO_PROJECAO_JUSTIFICATIVA_ERRO = 'Você não tem permissão para alterar essa justificativa';
    const FASE_EXERCICIO = 1;
    const MENSAGEM_ERRO_QTD_REGISTROS = 'Não é possível manipular mais que um registro por vez, selecione apenas um!';
    const MENSAGEM_LEITURA_SUCESSO = 'Informativo aceito com sucesso.';
    const MENSAGEM_LEITURA_ERRO = 'Ocorreu um erro e não foi possivel aceitar o informativo.';
    const MENSAGEM_ERRO_INESPERADO = 'Um erro inesperado aconteceu, entre novamente no sistema e tente novamente. Caso o erro se repita reporte o erro à informática.';

    /*
     * Configuração das fases do exercicio
     */
    const FASE_EXERCICIO_EM_DEFINICAO = 1;
    const FASE_EXERCICIO_LIBERADOS_PARA_RESPONSAVEIS = 2;
    const FASE_EXERCICIO_BLOQUEADO_PARA_COSOLIDACAO = 3;
    const FASE_EXERCICIO_PROPOSTA_LIBERADA = 4;
    const FASE_EXERCICIO_EM_EXECUCAO = 5;
    const FASE_EXERCICIO_ENCERRADO = 6;
    const FASE_EXERCICIO_RETORNADA_AO_PLANEJAMENTO = 8;
    const FASE_EXERCICIO_EM_APROVACAO = 8;



}
