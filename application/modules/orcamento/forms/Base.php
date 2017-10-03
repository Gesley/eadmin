<?php
/**
 * Contém formuçarios da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Form
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza campos e características elementares para os formulários desta
 * aplicação.
 *
 * @category Orcamento
 * @package Orcamento_Form_Base
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Base extends Zend_Form
{

    /**
     * Para uso de campo para upload de arquivo esse objeto deve ser informado.
     *
     * @var object Trf1_Importacao
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected $_regraImportacao = null;

    /**
     * Define as opções aceitáveis como parâmetros dos métodos dessa classe.
     *
     * @deprecated EVITAR USO DESTE VARIÁVEL!
     * @var array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected $_opcoes_OLD = array ( 'nome', 'label', 'obrigatorio', 'tamanho', 
            'qtdCaracter', 'classe', 'readonly' );

    /**
     * Retorna o formulário padronizando as opções para uso
     *
     * @see Zend_Form::init()
     * @param string $funcionalidade
     *        Nome da funcionalidade para determinar o nome do formulário
     * @param boolean $imp
     *        Informa se o formulário servirá ou não para upload de arquivos
     * @return Orcamento_Form_Base
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaFormulario ( $funcionalidade = null, $imp = false )
    {
        try {
            // Redefine nome com prefixo 'frm'
            $nome = 'frm' . ucfirst ( strtolower ( $funcionalidade ) );
            
            // Define valores parametrizados
            $this->setMethod ( 'post' );
            $this->setName ( $nome );
            $this->setAttrib ( 'id', $nome );
            
            if ( $imp == true ) {
                // Permite upload de arquivos
                $this->setAttrib ( 'enctype', 'multipart/form-data' );
            }
            $this->setElementFilters ( array ( 'StripTags', 'StringTrim' ) );
            
            // Devolve o formulário
            return $this;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * ...
     *
     * @param string $classe
     *        Classe .css para formatação do botão
     * @param string $desc
     *        Texto a ser exibido no botão, sendo 'Enviar' o padrão.
     * @return Zend_Form_Element_Button
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaBotaoSubmit ( $classe = null, $desc = 'Enviar' )
    {
        if ( $classe == null ) {
            // Define classe .css padrão
            $classe = Orcamento_Business_Dados::CLASSE_SALVAR;
        }
        
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Button ( 'Enviar' );
        
        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel ( $desc );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', $classe );
        
        // Devolve o botão criado
        return $cmdEnviar;
    }

    /**
     * Criação de campo padronizada para upload de um único arquivo
     *
     * @return Zend_Form_Element_File
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaCampoArquivoUnico ()
    {
        if ( $this->_regraImportacao == null ) {
            // Define mensagem do erro
            $msg = '';
            $msg .= 'Para uso deste campo é obrigatório informar a regra ';
            $msg .= 'negocial de importação: Trf1_Importacao_Business_XYZ ';
            
            // Gera erro
            throw new Zend_Exception ( $msg );
        }
        
        // Define nome da classe negocial de importação
        $regra = $this->_regraImportacao;
        
        if ( !class_exists ( $regra ) ) {
            $msg = "";
            $msg .= "Classe $regra não encontrada.";
            
            // Gera erro
            throw new Zend_Exception ( $msg );
        }
        
        // Instancia a classe negocial
        $importa = new $regra ();
        
        // Define a descrição do campo
        $desc = 'Favor selecionar o arquivo a importar.';
        
        // Definições sobre o(s) arquivo(s) a importar
        $pastaImportacao = $importa->retornaPastaImportacao ();
        
        // Define extensões válidas
        $exts = $importa->defineExtensoesValidas ();
        
        // Define string com extensões válidas para uso na mensagem de erro
        $sErro = $importa->retornaMsgErroExtensaoInvalida ();
        
        $valida = new Zend_Validate_File_Extension ( $exts );
        $valida->setMessage ( $sErro );
        
        // Cria o campo 'Arquivo'
        $txtArquivo = new Zend_Form_Element_File ( 'Arquivo' );
        
        // Define opções o controle $txtArquivo
        $txtArquivo->setLabel ( 'Arquivo a importar:' );
        $txtArquivo->setDescription ( $desc );
        $txtArquivo->setDestination ( $pastaImportacao );
        $txtArquivo->setAttrib ( 'size:', 60 );
        $txtArquivo->addValidator ( $valida );
        
        // Devolve o campo criado
        return $txtArquivo;
    }
    
    // ************************************************************************
    //
    //
    //
    //
    //
    //
    //
    //
    //
    //
    // ------------------------------------------------------------------------
    //                          Métodos depreciados!
    // ------------------------------------------------------------------------
    //
    //
    //
    //
    //
    //
    //
    //
    //
    //
    // ************************************************************************
    
    /**
     * Retorna campo texto conforme parâmetros
     *
     * @deprecated EVITAR USO DESTE MÉTODO!
     * @param array $opcoes        
     * @return Zend_Form_Element_Text
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampoTexto_OLD ( array $opcoes )
    {
        // Valida opções
        $this->_validaOpcao ( $opcoes );
        
        // Instancia o elemento
        $txt = new Zend_Form_Element_Text ( $opcoes [ 'nome' ] );
        
        // Define valores parametrizados
        $txt->setLabel ( $opcoes [ 'label' ] );
        $txt->setAttrib ( 'size', $opcoes [ 'tamanho' ] );
        $txt->addFilter ( 'StringTrim' );
        
        // Se quantidade de caracteres for informado, define-se 'maxlength'
        if ( $opcoes [ 'qtdCaracter' ] > 0 ) {
            $txt->setAttrib ( 'maxlength', $opcoes [ 'qtdCaracter' ] );
        }
        
        // Se campo for obrigatório define-se o 'setRequired'
        if ( $opcoes [ 'obrigatorio' ] == 'true' ) {
            $txt->setRequired ( true );
        }
        
        // Se campo for obrigatório define-se o 'setRequired'
        if ( $opcoes [ 'readonly' ] == 'true' ) {
            $txt->setAttrib ( 'readonly', 'readonly' );
        }
        
        return $txt;
    }

    /**
     * Retorna campo texto conforme parâmetros
     *
     * @deprecated EVITAR USO DESTE MÉTODO!
     * @param array $opcoes        
     * @return Zend_Form_Element_Text
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampoTextArea_OLD ( array $opcoes )
    {
        // Valida opções
        $this->_validaOpcao ( $opcoes );
        
        // Instancia o elemento
        $txtArea = new Zend_Form_Element_Textarea ( $opcoes [ 'nome' ] );
        
        // Define valores parametrizados
        $txtArea->setLabel ( $opcoes [ 'label' ] );
        $txtArea->setAttrib ( 'size', $opcoes [ 'tamanho' ] );
        $txtArea->addFilter ( 'StringTrim' );
        
        // Se quantidade de caracteres for informado, define-se 'maxlength'
        if ( $opcoes [ 'qtdCaracter' ] > 0 ) {
            $txtArea->setAttrib ( 'maxlength', $opcoes [ 'qtdCaracter' ] );
        }
        
        // Se campo for obrigatório define-se o 'setRequired'
        if ( $opcoes [ 'obrigatorio' ] == 'true' ) {
            $txtArea->setRequired ( true );
        }
        
        // Se campo for obrigatório define-se o 'setRequired'
        if ( $opcoes [ 'readonly' ] == 'true' ) {
            $txtArea->setAttrib ( 'readonly', 'readonly' );
        }
        
        return $txtArea;
    }

    /**
     * Retorna botão submit conforme parâmetros
     *
     * @deprecated EVITAR USO DESTE MÉTODO!
     * @todo Trocar para Enviar; revisar todo código que utiliza 'Salvar'
     *       fixamente!
     * @param array $opcoes        
     * @return Zend_Form_Element_Button
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaBotaoEnviar_OLD ( array $opcoes )
    {
        // Valida opções
        $this->_validaOpcao ( $opcoes );
        
        // Instancia o elemento
        // $cmd = new Zend_Form_Element_Button ( $opcoes [ 'nome' ] );
        $cmd = new Zend_Form_Element_Button ( 'Enviar' );
        
        // Define valores parametrizados
        $cmd->setLabel ( $opcoes [ 'label' ] );
        $cmd->setAttrib ( 'type', 'submit' );
        $cmd->setAttrib ( 'class', Trf1_Orcamento_Definicoes::CLASSE_SALVAR );
        
        return $cmd;
    }

    /**
     * Gera erro caso alguma opção informada não esteja entre as aceitas nesta
     * classe.
     *
     * @deprecated EVITAR USO DESTE MÉTODO!
     * @param array $opcoes        
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _validaOpcao_OLD ( array $opcoes )
    {
        // Valida a(s) opção(ões) informada(s)
        foreach ( $opcoes as $opcao => $valor ) {
            if ( !in_array ( $opcao, $this->_opcoes ) ) {
                throw new Zend_Exception ( 
                sprintf ( 'A opção "%s" não é válida nesta classe.', $opcao ) );
            }
        }
    }

}