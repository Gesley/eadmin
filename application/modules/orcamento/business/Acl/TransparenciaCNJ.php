<?php

/**
 * Retorna todos os dados para serem inclusos nos recursos e nos privilégios
 * dentro da estrutura do ACL.
 * 
 * Dentro de $usuario deverá incluir todos os que deverão receber privilégios.
 * 
 * Exemplo:
 * parent::$usuario = array("dipor", "desenvolvedor", "consulta");
 * 
 * Dentro de $controller a chave do array é o nome do controller e o valor
 * é um array com todas as ações utilizadas. Por padrão a index não é
 * necessário ser colocado devido a toda paǵina ter uma index.
 * 
 * Exemplo:
 * parent::$controller = array("alinea" => array("incluir", "editar", "excluir"));
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */
class Orcamento_Business_Acl_TransparenciaCNJ extends Orcamento_Business_Acl_Base {

    public static function inicializar() {

        parent::$usuario = array("dipor", "desenvolvedor", "consulta");

        $arrayAcaoBase = array("incluir", "editar", "excluir", "detalhe", "importar");
        $arrayAcaoImport = array("incluir", "editar", "excluir", "detalhe", "importar", "updateteste");

        $arrayAcaoRegra = $arrayAcaoBase;
        $arrayAcaoRegra[] = "ajaxmontacomboregracnj";
        $arrayAcaoBase[] = "ajaxcomboalinea";
        $arrayAcaoBase[] = "ajaxretornaalinea";

        parent::$controller = array(
            "alinea" => $arrayAcaoBase,
            "inciso" => $arrayAcaoBase,
            "regracnj" => $arrayAcaoRegra,
            "importarfinanceiro" => $arrayAcaoImport,
            "importarsuplementacao" => $arrayAcaoImport,
            "importarliquidado" => $arrayAcaoImport,
            "importardotacao" => $arrayAcaoImport,
            "importarcancelamento" => $arrayAcaoImport,
            "importarcontingenciamento" => $arrayAcaoImport,
            "importarprovisao" => $arrayAcaoImport,
            "importardestaque" => $arrayAcaoImport,
            "importarempenhado" => $arrayAcaoImport,
            "importarpago" => $arrayAcaoImport,
            "importarrestosapagar" => $arrayAcaoImport,
            "gerarrelatoriocnj" => array("relatorio"),
            "importarverificarcnj" => array("ajaxverificarimportado")
        );
    }
}