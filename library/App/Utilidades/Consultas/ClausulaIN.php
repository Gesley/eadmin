<?php
class App_Utilidades_Consultas_ClausulaIN {
    /**
     * Especifica o valor máximo de itens que o banco de dados aceita em uma cláusula IN
     * @var int 
     */
    public $_maxValores = 1000;
    
    /**
     * @abstract Método para criar uma subquery de unions para cláusulas in que acentam apenas 1000 itens nas listas desta forma o oracle aceita quantas uniões forem necessárias.
     * @author Leonan
     * @uses Uso em querys que utilizam uma clausulá in
     * @param array ou string $valores
     * @param string $nome_tabela
     * @param string $nome_coluna
     * @param string $caractere_separador
     * @return string 
     */
    public function condicaoIN_para_muitos_valores($valores, $nome_tabela, $nome_coluna, $caractere_separador = ','){
        
        $maxIds = $this->_maxValores;
        
        if(is_array($valores)){
            $auxIdsArr = $valores;
            $implodedValues = implode(',',$valores);
        }else if(is_string($valores)){
            $auxIdsArr = explode($caractere_separador, $valores);
            $implodedValues = $valores;
        }

        $auxCount = count($auxIdsArr);
        if($auxCount > $maxIds)
        {
            $auxDivisao = (int)floor ($auxCount/$maxIds);
            $auxMod = $auxCount%$maxIds;
            $countSlice = 0;
            $arrMovis = array();
            $auxOffset = 0;
            for($i=1;$i<=$auxDivisao;$i++){
                $arrMovis[$countSlice] = array_slice($auxIdsArr, $auxOffset,$maxIds);
                $auxOffset += $maxIds;
                $countSlice++;
            }
            $arrMovis[$countSlice] = array_slice($auxIdsArr, $auxDivisao*$maxIds,$auxMod);
            $strClausulaIn = '';
            $countAuxUnion = 0;
            foreach ($arrMovis as $arrIds) {
                 $auxString = implode(',',$arrIds);
                 $strClausulaIn .= "
                                    SELECT DISTINCT $nome_coluna FROM $nome_tabela WHERE $nome_coluna IN($auxString) 
                                   ";
                 if( $countAuxUnion < count($arrMovis)-1){
                     $strClausulaIn.=" UNION "; 
                 }
                 $countAuxUnion++;
            }
            $strClausulaIn = " $nome_coluna IN (
                                                            $strClausulaIn
                                                           ) ";
            return $strClausulaIn;
        }else{
            $strClausulaIn = " $nome_coluna IN($implodedValues) " ;
            return $strClausulaIn;
        }
    }
}
?>