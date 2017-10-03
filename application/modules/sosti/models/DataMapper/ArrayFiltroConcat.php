<?php

/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados
 * DbTable e o criar o objeto Model.
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_ArrayFiltroConcat extends Zend_Db_Table_Abstract
{
    public static function caixaConcat($rows = array(), $arrayNotInFase = array(), $caixaSolictInfo = false)
    {
        foreach ($rows as $cc) {
            $arrayRows[$cc["SSOL_ID_DOCUMENTO"]] = $cc;
            $ultimaFase = $cc["HISTORICO"];
            $fase = explode(',', $ultimaFase);
            foreach ($fase as $f) {
                $faseDate = explode('|', $f);
                /**
                 * Remover as fases que não são relevantes para serem tratadas 
                 * como sendo a última
                 */
                if (!in_array($faseDate[1], $arrayNotInFase)) {
                    $arrayDateFase[$cc["SSOL_ID_DOCUMENTO"]][$faseDate[0]] = $faseDate[1];
                }
            }
            /**
             * Retira do array as solicitações que não tem a última fase sendo 
             * 1024 para listar as solicitações da caixa de solicitações com 
             * pedido de informação do desenvolvimento e sustentação
             */
            if ($caixaSolictInfo) {
                if (end($arrayDateFase[$cc["SSOL_ID_DOCUMENTO"]]) != "1024") {
                    unset($arrayDateFase[$cc["SSOL_ID_DOCUMENTO"]]);
                }
            }
        }
        foreach (array_keys($arrayDateFase) as $df) {
            $arrayFinal[] = $arrayRows[$df];
        }
        return $arrayFinal;
    }
}