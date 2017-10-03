<?php
class App_UtilArray {
    
    /**
     * @param array $array
     * @param string $key
     * @param string $value
     * @return array
     * @abstract Dada uma array bidimensional com chaves inteiras positivas e sequenciais de crecimento positivo na primeira dimensão e 
     * e chaves mistas e valores mistos na segunda dimenção. Dado uma chave e um valor da segunda dimensão retira a(s) posição(ões) correspondentes da primeira dimensão.
     * @author Leonan
     * @uses Uso junto ao retorno de arrays retornadas pelo zend_db em operações de fetchAll
     * @example
     * Dada uma array como: 
     * $array_operacao = array(
     *       [0]=>array(
     *                  [key1]=>777,
     *                  [key2]=>999
     *                 ),
     *       [1]=>array(
     *                  [key3]=>555,
     *                  [key4]=>888
     *                 )
     *      );
     * dada a chamada
     * retiraposicaoarrayby($array_operacao,'key1','777')
     * array de resposta
     * $array_operacao = array(
     *       [0]=>array(
     *                  [key1]=>555,
     *                  [key2]=>888
     *                 )
     *       );
    */
    public static function retiraposicaoarray2dby(array $array, $key, $value){
        $array_aux = array();
        $count = count($array);
        for($i=0;$i<$count;$i++){
            if(strcmp($array[$i][$key],$value) == 0){
                unset($array[$i]);
            }
        }
        for($i=0;$i<$count;$i++){
            if(isset($array[$i])){
                $array_aux[] = $array[$i];
            }
        }
        return $array_aux;
    }
}
?>