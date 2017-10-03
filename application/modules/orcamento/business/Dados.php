<?php

/**
 * Contém constantes e valores padrão dos dados do banco
 * 
 * e-Admin
 * e-Orçamento
 * Core
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe genérica para atribuição de constantes e outros valores, conforme os
 * dados dispostos no banco
 *
 * @category Orcamento
 * @package Orcamento_Business_Tela_Base
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
final class Orcamento_Business_Dados {
    // ************************************************************
    // Genéricos
    // ************************************************************

    /**
     * Identificação do owner (schema) das estruturas do sistema. O uso do mesmo
     * traz benefícios de perfomance no banco!
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const BANCO_OWNER_SCHEMA = 'CEO';

    // ************************************************************
    // Actions do sistema
    // ************************************************************

    /**
     * Nome padrão da action inicial (ou quando não informada)
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACTION_INDEX = 'index';

    /**
     * Nome padrão da action de inclusão de registros
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACTION_INCLUIR = 'incluir';

    /**
     * Nome padrão da action de alteração de um registro
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACTION_EDITAR = 'editar';

    /**
     * Nome padrão da action de exclusão de registros
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACTION_EXCLUIR = 'excluir';

    /**
     * Nome padrão da action para exibição de detalhes do registro
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACTION_DETALHE = 'detalhe';

    /**
     * Nome padrão da action para restauração de registros logicamente excluídos
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACTION_RESTAURAR = 'restaurar';

    // ************************************************************
    // Status de mensagens
    // ************************************************************

    /**
     * Status padrão para mensagem de erro.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_STATUS_ERRO = 'error';

    /**
     * Status padrão para mensagem de alerta.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_STATUS_ALERTA = 'notice';

    /**
     * Status padrão para mensagem de sucesso.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_STATUS_SUCESSO = 'success';

    // ************************************************************
    // Mensagens padronizadas
    // ************************************************************

    /**
     * Resposta padrão após sucesso na alteração de registro no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_ALTERAR_SUCESSO = 'Registro alterado com sucesso.';

    /**
     * Resposta padrão após erro na alteração de registro no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_ALTERAR_ERRO = 'Alteração de registro.';

    /**
     * Resposta padrão após sucesso na importação de dados externos no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_IMPORTAR_SUCESSO = 'Importação realizada com sucesso.';

    /**
     * Resposta padrão após erro na importação de dados externos no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_IMPORTAR_ERRO = 'Importação de dados externos.';

    /**
     * Resposta padrão após sucesso na inclusão de registro no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_INCLUIR_SUCESSO = 'Registro incluído com sucesso.';

    /**
     * Resposta padrão após sucesso na inclusão de registro no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_SUCESSO_EXERCICIO = 'Operação realizada com sucesso!';

    /**
     * Resposta padrão após erro na inclusão de registro no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_INCLUIR_ERRO = 'Inclusão de registro.';

    /**
     * Pergunta padrão para confirmação de um ou mais registro selecionado -
     * Utilizado para ação em lote
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_EXCLUIR_PERGUNTA = 'Confirma exclusão dos registros selecionados?';

    /**
     * Resposta padrão após sucesso na exclusão de registros no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_EXCLUIR_SUCESSO = 'Registro(s) excluído(s) com sucesso.';

    /**
     * Resposta padrão após erro na exclusão de registros no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_EXCLUIR_ERRO = 'Exclusão de registro(s).';

    /**
     * Resposta padrão após cancelamento na operação de exclusão.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_EXCLUIR_CANCELAR = 'Exclusão cancelada.';

    /**
     * Pergunta padrão para confirmação de um ou mais registro selecionado -
     * Utilizado para ação em lote
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_RESTAURAR_PERGUNTA = 'Confirma restauração dos registros selecionados?';

    /**
     * Resposta padrão após sucesso na restauração de registros no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_RESTAURAR_SUCESSO = 'Registro(s) restaurado(s) com sucesso.';

    /**
     * Resposta padrão após erro na restauração de registros no banco.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_RESTAURAR_ERRO = 'Restauração de registro(s).';

    /**
     * Resposta padrão após cancelamento na operação de restauração de registros
     * logicamente excluídos.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_RESTAURAR_CANCELAR = 'Restauração cancelada.';

    /**
     * Resposta padrão após sucesso na cópia de despesas entre exercícios no
     * banco.
     *
     * @var string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    const MSG_COPIARDESPESA_SUCESSO = 'Despesa(s) copiada(s) com sucesso.';

    /**
     * Resposta padrão após erro na cópia de despesas entre exercícios no banco.
     *
     * @var string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    const MSG_COPIARDESPESA_ERRO = 'Não foi possivel copiar a(s) despesa(s).';

    /**
     * Codigo padrão para Nova despesa situação = Atendida.
     *
     * @var string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    const MSG_NOVADESPESA_ATENDIDA = 2;

    /**
     * Resposta padrão após sucesso na aplicação de regras para reajuste dos
     * valores da despesa.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_APLICARREGRA_SUCESSO = 'Regra de ajuste aplicada com sucesso.';

    /**
     * Resposta padrão após erro na aplicação de regras para reajuste dos
     * valores da despesa.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_APLICARREGRA_ERRO = 'Não foi possivel aplicar regra de ajuste.';

    /**
     * Resposta padrão após erro inserção de regras com o mesmo valor percentual
     * e exercicio.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    // const MSG_DUPLICIDADEREGRA_ERRO = 'Operação cancelada, já existe este
    // percentual para o exercício informado!.';
    const MSG_DUPLICIDADEREGRA_ERRO = 'Já existe este percentual para o exercício informado.';

    /**
     * Resposta padrão após banco não retornar o registro (ou conjunto de)
     * esperado.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_REGISTRO_NAO_ENCONTRADO = 'Registro não encontrado ou você não possui privilégios para acessá-lo.';

    /**
     * Resposta padrão antes de ida ao banco por ausência de código
     * (chave-primária ou composta).
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_CODIGO_NAO_INFORMADO = 'Código não informado.';

    /**
     * Resposta padrão após banco não retornar o registro (ou conjunto de)
     * esperado.
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MSG_REGISTRO_DUPLICADO = 'Registro não encontrado ou você não possui privilégios para acessá-lo.';

    /**
     * Resposta padrão para tentativa de edição de vários registros não
     * permitida
     *
     * @var string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    const MSG_EXCESSO_REGISTROS = 'Favor selecionar apenas 1 registro para essa ação!';

    // ************************************************************
    // Classes definidas na folha de estilos (.css)
    // ************************************************************

    /**
     * Nome padrão da Classe (class) dos botões de inclusão de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_INCLUIR = 'ceo_novo';

    /**
     * Nome padrão da Classe (class) dos botões de edição de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_EDITAR = 'ceo_editar';

    /**
     * Nome padrão da Classe (class) dos botões de exclusão de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_EXCLUIR = 'ceo_excluir';

    /**
     * Nome padrão da Classe (class) dos botões para visualização detalhada de
     * dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_DETALHAR = 'ceo_detalhar';

    /**
     * Nome padrão da Classe (class) dos botões para salvamento de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_SALVAR = 'ceo_salvar';

    /**
     * Nome padrão da Classe (class) dos botões para importação de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_IMPORTAR = 'ceo_importar';

    /**
     * Nome padrão da Classe (class) dos botões de confirmação de operações
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_CONFIRMAR = 'ceo_confirmar';

    /**
     * Nome padrão da Classe (class) dos botões de não confirmação de operações
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_NEGAR = 'ceo_negar';

    /**
     * Nome padrão da Classe (class) dos botões para consulta de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_CONSULTAR = 'ceo_consultar';

    /**
     * Nome padrão da Classe (class) dos botões para pesquisa de dados
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_PESQUISAR = 'ceo_pesquisar';

    /**
     * Nome padrão da Classe (class) dos botões para cancelamento de operações
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_CANCELAR = 'ceo_cancelar';

    /**
     * Nome padrão da Classe (class) para não permitir edição de dados no
     * controle
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_APENAS_LEITURA = 'ceo_readonly';

    /**
     * Nome padrão da Classe (class) para voltar a uma determinada página
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_VOLTAR = 'ceo_voltar';

    /**
     * Nome padrão da Classe (class) para voltar a uma determinada página
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const CLASSE_RELATORIO = 'ceo_relatorio';

    // ************************************************************
    // Níveis de permissão
    // ************************************************************

    /**
     * Perfil setorial da DIPOR - Programação orçamentária
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_DIPOR = 'dipor';

    /**
     * Perfil do(s) diretores setoriais da DIPLA - Planejamento
     *
     * @deprecated NÃO UTILIZAR MAIS ESSA CONSTANTE!
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_PLANEJAMENTO_DIRETOR = 'planejamento_diretor';

    /**
     * Perfil setorial da DIPLA - Planejamento
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_PLANEJAMENTO = 'planejamento';

    /**
     * Perfil dos desenvolvedores do sistema
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_DESENVOLVEDOR = 'desenvolvedor';

    /**
     * Perfil DIEFI - Apenas programação financeira
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_DIEFI = 'diefi';

    /**
     * Perfil das secretarias do TRF
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_SECRETARIA = 'secretaria';

    /**
     * Perfil das secretarias do TRF com permissão de ver despesas das reservas
     * orçamentárias
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_SECRETARIA_RESERVA = 'secretaria_reserva';

    /**
     * Perfil das diversas seccionais (estados)
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_SECCIONAL = 'seccional';

    /**
     * Perfil para apenas para consulta
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PERMISSAO_CONSULTA = 'consulta';
    // ************************************************************
    // Acerto manual de dados / CEO_TB_NOCR... e CEO_TB_NOEM
    // ************************************************************
    /**
     * Informa se o registro ainda não foi acertado manualmente
     *
     * @var boolean
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACERTO_MANUAL_NAO = 0;

    /**
     * Informa se o registro já foi acertado manualmente
     *
     * @var boolean
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const ACERTO_MANUAL_SIM = 1;

    // ************************************************************
    // Tipos de solicitação / CEO_TB_TSOL_TIPO_SOLICITACAO
    // ************************************************************

    /**
     * Tipo de solicitação - Solicitada
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const TIPO_SOLICITACAO_SOLICITADA = 1;

    /**
     * Tipo de solicitação - Atendida
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const TIPO_SOLICITACAO_ATENDIDA = 2;

    /**
     * Tipo de solicitação - Recusada
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const TIPO_SOLICITACAO_RECUSADA = 3;

    /**
     * Tipo de solicitação - Pendente
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const TIPO_SOLICITACAO_PENDENTE = 4;

    // ************************************************************
    // Demandantes / CEO_TB_VLDE_VALOR_DESPESA
    // ************************************************************

    /**
     * Demandante responsável pelo valor 1; proposta inicial
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_PROPOSTA_INICIAL = 1;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_DIPLA_AJUSTE_POS_RESPONSAVEL = 2;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_AJUSTE_LIMITE = 3;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_DIPOR_APROVADO = 4;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_BASE_ANO_ANTERIOR = 5;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_BASE_ANO_ATUAL = 6;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_DIPLA_AJUSTE_POS_BASE = 7;

    /**
     * Demandante
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const DEMANDANTE_SOLIC_RESPONSAVEL = 8;

    /**
     * Demandante
     *
     * @var integer
     * @author Victor Eduardo Barreto
     */
    const DEMANDANTE_REAJUSTE_PROPOSTA_ATUAL = 9;

    /**
     * Demandante
     *
     * @var integer
     * @author Victor Eduardo Barreto
     */
    const DEMANDANTE_REAJUSTE_APLICADO_LIMITE = 10;

    /**
     * Demandante
     *
     * @var integer
     * @author Victor Eduardo Barreto
     */
    const DEMANDANTE_REAJUSTE_COMPOSICAO_BASE = 11;

    /**
     * Demandante
     *
     * @var integer
     * @author Victor Eduardo Barreto
     */
    const DEMANDANTE_REAJUSTE_BASE_PREPROPOSTA = 12;

            /**
     * Demandante
     *
     * @var integer
     * @author Victor Eduardo Barreto
     */
    const DEMANDANTE_REAJUSTE_PREPROPOSTA = 13;


    // ************************************************************
    // Fases do exercício / CEO_TB_FANE_FASE_ANO_EXERCICIO
    // ************************************************************

    /**
     * Fases do exercício - em definição
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const FASE_EXERCICIO_DEFINICAO = 1;

    /**
     * Fases do exercício - liberada para responsáveis
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const FASE_EXERCICIO_RESPONSAVEL = 2;

    /**
     * Fases do exercício - bloqueado para consolidação
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const FASE_EXERCICIO_CONSOLIDACAO = 3;

    /**
     * Fases do exercício - proposta liberada
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const FASE_EXERCICIO_LIBERADA = 4;

    /**
     * Fases do exercício - em execução
     *
     * @var integer
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const FASE_EXERCICIO_EXECUCAO = 5;

    // ***********************************************************
    // Tipos de notas de crédito / CEO_TB_TPNC_TIPO_NOTA_CREDITO
    // ***********************************************************

    /**
     * Tipo de nota de crédito - Proposta
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_PROPOSTA = 'P';

    /**
     * Tipo de nota de crédito - Adicional
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_ADICIONAL = 'A';

    /**
     * Tipo de nota de crédito - Contigência
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_CONTINGENCIA = 'C';

    /**
     * Tipo de nota de crédito - Extra
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_EXTRA = 'E';

    /**
     * Tipo de nota de crédito - Alteração de QDD
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_ALTERACAO_QDD = 'Q';

    /**
     * Tipo de nota de crédito - Saída
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_SAIDA = 'S';

    /**
     * Tipo de nota de crédito - Destaque
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    const TIPO_NOTA_CREDITO_DESTAQUE = 'T';

}
