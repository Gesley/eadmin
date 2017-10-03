<?php
class App_Utilidades_Strings_LimitadorString {
    
    /**
     * @abstract Método para limitar a quantidade de caracteres em uma String
     * @author Daniel 
     * @uses Uso em views e controllers
     * @param String string - Valor a ser tratado
     * @param Int start - Valor inicial da String
     * @param Int limite - Define o limite de caracteres  
     * @param String prefixo - Define o prefixo da string
     * @return string 
     */
    
    /**
     * Especifica o valor padrao do sufixo
     * @var String
     */
    public $sufixo = '...';
    
    
    public function limiteString($string, $limite, $start = 0, $prefixo = null){
        
        $novoTexto = $string;
        
        //Validacao
        if($prefixo != ''){
            $novoTexto = $prefixo.$novoTexto;
        }
        
        //Verifica se o texto excede o limite de caracteres
        if (strlen($novoTexto) > $limite) {
            //Limita o texto de acordo com o limite passado por parametro
            $novoTexto = substr($novoTexto, $start, $limite).$this->sufixo;
        } else {
            $novoTexto = $string;
        }
        return $novoTexto;
        
    }
}
?>