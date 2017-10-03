<?php

/**
 * Criação do forms base para importação.
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Criação do forms base para importação.
 *
 * @category Orcamento
 * @package Orcamento_Form_ImportacaoBase
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_ImportacaoBase extends Orcamento_Form_Base {

    /**
     * Construtor da classe.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
   public function init() {
        
        // mes e ano para preenchimento da tela
        $mesDefault = date("n",  strtotime("last month"));
        $anoDefault = ($mesDefault == 12) ?
                date("Y", strtotime("last year")) :date("Y");

        // Definições iniciais do formulário
        $this->retornaFormulario('importar', true);

        $fileArquivo = new Zend_Form_Element_File('IMPA_DS_ARQUIVO');
        $fileArquivo->setRequired(true)
                ->setLabel('Importar Arquivo:')
                ->setDestination(APPLICATION_PATH . '/data/ceo/import');
//                ->addValidator(new Zend_Validate_File_Extension('txt'));

        // Cria o campo IMPA_AA_IMPORTACAO
        $txtCodigo = new Zend_Form_Element_Text('IMPA_AA_IMPORTACAO');

        $txtCodigo->setLabel('Ano:')
                ->setAttrib('size', '5')
                ->setAttrib('maxlength', 5)
                ->addFilter('StringTrim')
                ->addValidator('Digits')
                ->setRequired(true)
                ->setValue($anoDefault);

        // Cria o campo IMPA_IC_MES
        $slctMes = new Zend_Form_Element_Select('IMPA_IC_MES');

        // Define opções o controle $slctMes
        $slctMes->setLabel('Mês:')
                ->setRequired(true)
                ->addValidator('NotEmpty', true)
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setValue($mesDefault);
        
        // Retorna meses em pt-br para preenchimento do select
        $meses = Zend_Locale::getTranslationList('Months', 'pt_br');
        $mesesFormat = $meses['format']['wide'];
        array_walk($mesesFormat, 'primeiraMaiuscula');
        
        // adiciona os meses
        $slctMes->addMultiOptions($mesesFormat);

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Importar');

        // Define opções do controle $cmdEnviar
        $classeSubmit = Orcamento_Business_Dados::CLASSE_IMPORTAR;
        
        $cmdEnviar->setLabel('Importar')
            ->setAttrib('type', 'submit')
            ->setAttrib('class', $classeSubmit);
        

        // Adiciona os controles no formulário
        $this->addElement($fileArquivo)
                ->addElement($txtCodigo)
                ->addElement($slctMes)
                ->addElement($cmdEnviar);
    }

}

/**
 * 
 * Função para deixar a primeira letra em maiusculo em um array.
 * Usado no array_walk.
 * 
 * @param String $item
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */
function primeiraMaiuscula(&$item) {
    $item = ucfirst($item);
}