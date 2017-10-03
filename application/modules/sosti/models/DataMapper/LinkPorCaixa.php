<?php
/**
 * Retorna os links de acesso por id da caixa de entrada
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_LinkPorCaixa extends Zend_Db_Table_Abstract
{
    public static function enderecoPorId()
    {
        return array(
            '1.1'  => 'helpdesk/primeironivel',
            '1.2'  => 'atendimentotecnico/segundonivel',
            '1.3'  => 'suporteespecializado/terceironivel',
            '1.4'  => 'servicoexterno/quartonivel',
            '11.33' => 'atendimentosecoes/atendimentousuario',
            2      => 'desenvolvimentosustentacao/index',
            3      => 'bancodadosrede/index',
            4      => 'noc/index',
            5      => 'atendimentosecoes/atendimentousuario',
            6      => 'atendimentosecoes/caixaunidadecentral',
            7      => 'atendimentosecoes/atendimentousuario',
            8      => 'atendimentosecoes/atendimentousuario',
            9      => 'atendimentosecoes/atendimentousuario',
            10     => 'atendimentosecoes/atendimentousuario',
            11     => 'atendimentosecoes/atendimentousuario',
            12     => 'atendimentosecoes/atendimentousuario',
            13     => 'atendimentosecoes/atendimentousuario',
            14     => 'atendimentosecoes/caixaunidadecentral',
            15     => 'atendimentosecoes/atendimentousuario',
            16     => 'atendimentosecoes/atendimentousuario',
            17     => 'atendimentosecoes/atendimentousuario',
            18     => 'atendimentosecoes/atendimentousuario',
            19     => 'gestaodedemandasti/index',
            20     => 'gestaodemandasinfraestrutura/index',
            21     => 'gestaodedemandasdoatendimentoaosusuariossecoes/index',
            22     => 'atendimentosecoes/atendimentousuario',
            23     => 'atendimentosecoes/atendimentousuario',
            24     => 'atendimentosecoes/atendimentousuario',
            25     => 'atendimentosecoes/atendimentousuario',
            26     => 'atendimentosecoes/atendimentousuario',
            27     => 'atendimentosecoes/atendimentousuario',
            28     => 'atendimentosecoes/atendimentousuario',
            29     => 'atendimentosecoes/atendimentousuario',
            30     => 'atendimentosecoes/atendimentousuario',
            31     => 'atendimentosecoes/atendimentousuario',
            32     => 'atendimentosecoes/atendimentousuario',
            33     => 'atendimentosecoes/atendimentousuario',
            34     => 'atendimentosecoes/atendimentousuario',
            35     => 'atendimentosecoes/atendimentousuario',
            36     => 'gestaodedemandasdonoc/index'
        );
    }
}