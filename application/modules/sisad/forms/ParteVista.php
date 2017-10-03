<?php

class Sisad_Form_ParteVista extends Zend_Form
{

    public function init()
    {
        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setName('parteVista');

        $service_lotacao = new Services_Rh_Lotacao();

        $tipoParte = new Zend_Form_Element_Select('tipo_parte');
        $tipoParte
                ->setLabel('*Tipo:')
                ->addMultiOption('', '')
                ->addMultiOptions(array(
                    'pessoa_fisica_interna' => 'Pessoa TRF/seções'
                    , 'pessoa_fisica_externa' => 'Pessoa física externa'
                    , 'pessoa_juridica' => 'Pessoa jurídica'
                    , 'unidade_administrativa' => 'Unidade administrativa'
        ));

        $pessoaFisicaInterna = new Zend_Form_Element_Text('pessoa_fisica_interna');
        $pessoaFisicaInterna
                ->setLabel('*Pessoa física interna:');

        $pessoaFisicaExterna = new Zend_Form_Element_Text('pessoa_fisica_externa');
        $pessoaFisicaExterna
                ->setLabel('*Pessoa física externa:');

        $pessoaJuridica = new Zend_Form_Element_Text('pessoa_juridica');
        $pessoaJuridica
                ->setLabel('*Pessoa jurídica:');

        $secao = new Zend_Form_Element_Select('secao_parte_vista');
        $secao
                ->setLabel('*TRF1/Seções:')
                ->addMultiOption('', '')
                ->addMultiOptions($service_lotacao->retornaComboTrfSecao());

        $secaoSubsecao = new Zend_Form_Element_Select('subsecao_parte_vista');
        $secaoSubsecao
                ->setLabel('*Seção/Subseção:');

        $unidadeAdministrativa = new Zend_Form_Element_Select('unidade_administrativa');
        $unidadeAdministrativa
                ->setLabel('*Unidade administrativa:');

        $tipoPessoaParte = new Zend_Form_Element_Select('tipo_pessoa_parte');
        $tipoPessoaParte
                ->setLabel('*Adicionar como:')
                ->addMultiOptions(array(
                    'parte_vista' => 'Parte e Vista'
                    , 'parte' => 'Parte'
                    , 'vista' => 'Vista'
        ));

        $adicionarParteVista = new Zend_Form_Element_Button('adicionar_parte_vista');
        $adicionarParteVista->setLabel('Adicionar');

        $partesPessoaTrf = new Zend_Form_Element_Hidden('partes_pessoa_trf');
        $partesPessoaTrf->removeDecorator('label');
        
        $partesUnidade = new Zend_Form_Element_Hidden('partes_unidade');
        $partesUnidade->removeDecorator('label');
        
        $partesPessoaExterna = new Zend_Form_Element_Hidden('partes_pess_ext');
        $partesPessoaExterna->removeDecorator('label');
        
        $partesPessoasJuridicas = new Zend_Form_Element_Hidden('partes_pess_jur');
        $partesPessoasJuridicas->removeDecorator('label');
        
        $partesArray = new Zend_Form_Element_Hidden('partes_array');
        $partesArray->removeDecorator('label');

        $this->addElements(array(
            $tipoParte
            , $pessoaFisicaInterna
            , $pessoaFisicaExterna
            , $pessoaJuridica
            , $secao
            , $secaoSubsecao
            , $unidadeAdministrativa
            , $tipoPessoaParte
            , $adicionarParteVista
            , $partesPessoaTrf
            , $partesUnidade
            , $partesPessoaExterna
            , $partesPessoasJuridicas
            , $partesArray
        ));
    }

}