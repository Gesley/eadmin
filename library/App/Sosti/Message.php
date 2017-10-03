<?php
/**
 * Documento de Mensagens do módulo sosti.
 */
class App_Sosti_Message 
{
    
    public static function msg($msg, $value = false) 
    {
        $obj = new ArrayObject();
        $obj->msg001 = "é menor que 5 (tamanho mínimo desse campo)";
        $obj->msg002 = "Preenchimento Obrigatório";
        $obj->msg003 = "ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) "
            . "são codificados por questões de segurança, por isso, os caracteres "
            . "informados podem corresponder a uma quantidade real maior de caracteres.";
        $obj->msg004 = "Ordem de Serviço nº: $value cadastrada!";
        $obj->msg005 = "Os arquivos anexados podem ter no Maximo 50 Megas.";
        $obj->msg006 = "Deseja realmente excluir a $value?";
        $obj->msg007 = "$value excluída com sucesso!";
        $obj->msg008 = "$value cadastrada com sucesso!";
        $obj->msg009 = "$value editada com sucesso!";
        $obj->msg010 = "Esse Tipo de Tarefa já foi usado. Deseja realmente excluí-lo?";
        $obj->msg011 = "Escolha uma solicitação!";
        $obj->msg012 = "Email invalido!";
        $obj->msg013 = "Essa solicitação já possui uma Ordem de Serviço Aberta!";
        $obj->msg014 = "Não foram encontrados registros para o Caixa de atendimento no período informado.";
        $obj->msg015 = "Não existem registros";
        $obj->msg016 = "Você não possui acesso à caixa onde se encontra esta solicitação.";
        $obj->msg017 = "Selecione apenas uma solicitação!";
        $obj->msg018 = "O preenchimento de um dos campos de pesquisa é necessário.";
        $obj->msg019 = "Já existe uma escala de plantão cadastrada para o dia/período informado.";
        $obj->msg020 = "Escolha a solicitação principal";
        $obj->msg021 = "Não é possível realizar VINCULAÇÃO com menos de duas solicitações";
        $obj->msg022 = "Solicitações Vinculadas com Sucesso";
        $obj->msg023 = "A pesquisa retornou 142670 registros ultrapassou o maximo "
            . "de 500 registros. Informe mais parâmetros de pesquisa. Por Exemplo, "
            . "limite um período de tempo.";
        $obj->msg024 = "A solicitação filtrada não está disponível para esse tipo de vinculação.";
        $obj->msg025 = "Preenchimento obrigatório.";
        $obj->msg026 = '“DDMMAAAA” Não é uma data válida.';
        $obj->msg027 = "Não é possível retirar relatório do prazo entre Outubro e Novembro, "
            . "pois o sistema de calculo foi modificado.";
        $obj->msg028 = "Informe pelo menos um serviço.";
        $obj->msg029 = "O serviço informado não possui nenhum Documento disponível para análise.";
        $obj->msg030 = "A data da videoconferência deve ser um dia útil para o TRF1.";
        $obj->msg031 = "A(s) solicitação(ões) nº: [NÚMERO DAS SOLICITAÇÕES] Já foi(ram) fechada(s). "
            . "Retire-a(s) da planilha para que seja possível gerar o Relatório.";
        $obj->msg032 = "Não é possível solicitar extensão de prazo para Documento de Abertura aprovado.";
        $obj->msg033 = "A solicitação foi vinculada a Ordem de Serviço!";
        return $obj->$msg;
    }
}