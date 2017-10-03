<?php

/**
 * Classe para manipulação de valores
 *
 * @category	TRF1
 * @package		Trf1_Orcamento_Valor
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
class Trf1_Orcamento_Valor {

    /**
     * Classe construtora
     *
     * @param
     *        none
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct () {
        //
    }

    /**
     * Formata número informado como valor e já inclui as tags para exibição em
     * colorido
     *
     * @param numeric $numero
     * @param boolean $bFormataMoeda
     * @param boolean $bEhVerde
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaNumeroFormatadoValor ($numero, $bFormataMoeda = true, $bEhVerde = false) {
        if ($numero == null || $numero == '') {
            // Devolve string vazia
            $numero = 0;
        }

        // Salva, para testes, o valor original do parâmetro $numero
        $numeroOriginal = $numero;
        $tipo = gettype($numeroOriginal);

        if (!is_numeric($numero)) {
            // Se número vem do banco, trocar vírgula [,] por ponto [.]
            $numero = str_replace(',', '.', $numero);
        }

        // Garante a conversão do número
        $valor = floatval($numero);

        if (!is_float($valor)) {
            // Se após todas as conversões ainda não for float, sai do método
            return $numeroOriginal;
        }

        // Formata o valor
        $numeroFormato = $this->retornaNumeroFormatado($valor);

        // Verifica se utiliza o sinal de moeda
        $sinal = '';
        if ($bFormataMoeda) {
            $sinal = 'R$ ';
        }

        // Define a string resultante
        $sValor = "";
        $sValor .= "<span class='";
        $sValor .= $this->retornaClasseValor($valor, $bEhVerde);
        $sValor .= "'>";
        $sValor .= $sinal;
        $sValor .= $numeroFormato;
        $sValor .= "</span>";

        // Zend_Debug::dump ( $numeroOriginal );
        // Zend_Debug::dump ( $numero );
        // Zend_Debug::dump ( $tipo );
        // Zend_Debug::dump ( $valor );
        // Zend_Debug::dump ( $numeroFormato );
        // Zend_Debug::dump ( $sinal );
        // Zend_Debug::dump ( $sValor );
        // exit ();
        // Devolve o valor formatado
        return $sValor;
    }

    /**
     * Formata número informado como percentual e já inclui as tags para
     * exibição
     *
     * @param integer $numero
     *        Número decimal a ser formatado como percentual
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaNumeroFormatadoPercentual ($numero) {

        if (!$numero) {
            // Devolve string vazia
            $numero = 0;
        }

        if (!is_numeric($numero)) {
            // Se número vem do banco, trocar vírgula [,] por ponto [.]
            $numero = str_replace(',', '.', $numero);
        }

        // Define o valor percentual, exibindo apenas 2 casas decimais
        $percentual = ( round(($numero * 10000), 0) / 100 );

        $sPercentual .= "<span class=\"";
        $sPercentual .= $this->retornaClasseValor($percentual, null);
        $sPercentual .= "\">";
        $sPercentual .= "$percentual %";
        $sPercentual .= "</span>";

        // Devolve o percentual
        return $sPercentual;
    }

    /**
     * Formata número informado como valor
     *
     * @param integer $numero
     *        Número decimal a ser formatado como percentual
     * @param numeric $numCasaDecimais
     *        Número de casas decimais a exibir
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaNumeroConvertido ($numero, $numCasaDecimais = 2) {
        $americano = $this->retornaNumeroFormatoAmericano($numero);
        $valor = $this->retornaNumeroFormatado($americano, $numCasaDecimais);

        // Devolve número formatado
        return $valor;
    }

    /**
     * Converte número para padrão Brasileiro
     *
     * @param numeric $numero
     *        Número a ser formatado
     * @param numeric $numCasaDecimais
     *        Número de casas decimais a exibir
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaNumeroFormatado ($numero, $numCasaDecimais = 2) {
        return number_format($numero, $numCasaDecimais, ',', '.');

        /* Código obsoleto */
        //
        // $numFormatado = $numero;
        //
        // if ( is_numeric ( $numero ) ) {
        // $numFormatado = number_format ( $numero, 2, ',', '.' );
        // }
        //
        // return $numFormatado;
    }

    /**
     * Retorna a classe que formata a cor do valor, se positivo ou negativo
     * Número deve ser passado em formato americano (0.00)
     *
     * @param string $valor
     * @param boolean $bEhVerde
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaClasseValor ($valor, $bEhVerde = false) {
        $classe = 'valorPositivo';

        if ($valor < 0) {
            $classe = 'valorNegativo';
        }

        // Valores a receber devem ser exibidos em verde!
        if ($bEhVerde) {
            $classe = 'valorNaoRecebido';
        }

        return $classe;

        /* Código obsoleto */
        //
        // if ( !is_numeric ( $valor ) ) {
        // return null;
        // }
        //
        // if ( $valor >= 0 ) {
        // $classe = 'valorPositivo';
        // } else {
        // $classe = 'valorNegativo';
        // }
    }

    /**
     * Converte número para padrão americano
     *
     * @deprecated NÃO UTILIZAR ESSE MÉTODO!
     * @param numeric $numero
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaNumeroFormatoAmericano ($numero) {
        $valor = $numero;

        // Remove separador de milhar
        $valor = str_replace('.', '', $valor);
        // Troca vírgula por ponto
        $valor = str_replace(',', '.', $valor);

        return $valor;
    }

    /**
     * Converte valor (vindo de campo de formulario) para inclusão no banco
     *
     * @deprecated NÃO UTILIZAR ESSE MÉTODO!
     * @param string $valor
     * @return string
     * @see Trf1_Orcamento_Negocio_Despesa
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaValorParaBanco ($valor) {
        $valor = $this->retornaRemovePontoSeparadorDeMilhar($valor);
        $valor = str_replace(',', '.', $valor);
        return $valor;

        return false;
    }

    /**
     *
     * @deprecated NÃO UTILIZAR ESSE MÉTODO!
     * @param unknown $valor
     * @return mixed
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaValorParaBancoRod ($valor) {
        $valor = $this->retornaRemovePontoSeparadorDeMilhar($valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", ".", $valor);

        return $valor;
    }

    /**
     * Remove o caracter ponto [.] utilizado como separador de milhar
     *
     * @deprecated NÃO UTILIZAR ESSE MÉTODO!
     * @param string $valor
     * @return string
     * @see Trf1_Orcamento_Negocio_Despesa
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRemovePontoSeparadorDeMilhar ($valor) {
        $valor = str_replace('.', '', $valor);

        return $valor;
        // return false;
    }

    /**
     * Remove o ponto [.] para que o banco consiga salvar o valor
    */
    public function formataMoedaBanco ( $valor ) {
        $valor = str_replace("R$", "", $valor);
        return (int)str_replace('.', '', $valor);
    }

    public function formataMoedaOrcamento( $valor ) {
        $valor = str_replace("R$", "", $valor);

        $tamanho = strlen( $valor );

        // 8- 2
        $ponto = $tamanho - 2;

        $vl1 = substr($valor, 0, $ponto);

        $vl2 = substr($valor, $ponto, 2);
            
        return (int) $vl1.".".$vl2;

    }

}
