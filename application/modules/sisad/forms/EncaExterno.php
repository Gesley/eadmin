<?php
class Sisad_Form_EncaExterno extends Zend_Form
{
    public function init()
    {
        $this->setAction('enderecar')
             ->setMethod('post')
             ->setName('EncaExterno');

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $tppoTipoPostagem = new Application_Model_DbTable_SadTbTppoTipoPostagem();
        $secao = $rh_central->getSelectSecao();
        $getLotacao = $rh_central->getLotacao();
        $getUf = $rh_central->getCapitalUF();
        $getTipoPostagem = $tppoTipoPostagem->getTipoPostagemUsuario();
        
        $acao = new Zend_Form_Element_Hidden('setAcao');
        $acao->setValue('SetEndereco');
        
        $orgaoDestino = new Zend_Form_Element_Text('POST_CD_PESSOA_DESTINO');
        $orgaoDestino->setRequired(true)
                     ->setLabel('*Orgão Destino:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->setRequired(true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200))
                     ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        
        $destinatario = new Zend_Form_Element_Text('POST_NM_DESTINATARIO_EXTERNO');
        $destinatario->setRequired(true)
                     ->setLabel('*Destinatário:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->setRequired(true)
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200));
        
        $endereco = new Zend_Form_Element_Text('POST_DS_ENDERECO_DESTINO');
        $endereco->setRequired(true)
                 ->setLabel('*Endereço:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 350px'))
                 ->addValidator('StringLength', false, array(5, 200));

        $bairro = new Zend_Form_Element_Text('POST_DS_BAIRRO_DESTINO');
        $bairro->setRequired(true)
                 ->setLabel('*Bairro:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->addValidator('StringLength', false, array(5, 200));

        $cidade = new Zend_Form_Element_Text('POST_DS_CIDADE_DESTINO');
        $cidade->setRequired(true)
                 ->setLabel('*Cidade:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->addValidator('StringLength', false, array(5, 200));

        $estado = new Zend_Form_Element_Select('POST_CD_UF_DESTINO');
        $estado->setRequired(true)
                 ->setLabel('*Estado:')
                 ->addMultiOptions(array('0' => 'Selecione um Estado'));
        foreach ($getUf as $ufs):
            $estado->addMultiOptions(array($ufs["CAP_UF"] => $ufs["UF_NOME"]));
        endforeach;

        $cep = new Zend_Form_Element_Text('POST_CD_CEP_DESTINO');
        $cep->setRequired(true)
                 ->setLabel('*CEP:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->addValidator('StringLength', false, array(8, 200));

        $pais = new Zend_Form_Element_Text('POST_DS_PAIS_DESTINO');
        $pais->setRequired(true)
                 ->setLabel('*País:')
                 ->setValue('Brasil')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->addValidator('StringLength', false, array(5, 200));

        $tratamento = new Zend_Form_Element_Text('POST_DS_TRATAMENTO_EXTERNO');
        $tratamento->setRequired(true)
                 ->setLabel('Tratamento:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->addValidator('StringLength', false, array(1, 200));
        
        $tpPostagem = new Zend_Form_Element_Select('POST_ID_TIPO_POSTAGEM');
        $tpPostagem->setRequired(true)
                 ->setLabel('Preferência de Postagem:')
                 ->addMultiOptions(array(0 => 'Selecione o Tipo de Postagem'));
        foreach ($getTipoPostagem as $tiposPostagem):
            $tpPostagem->addMultiOptions(array($tiposPostagem["TPPO_ID_TIPO_POSTAGEM"] => $tiposPostagem["TPPO_DS_TIPO_POSTAGEM"]));
        endforeach;
        $tpPostagem->setDescription('Atenção: Este campo poderá sofrer alteração pela unidade responsável pelo protocolo.');
        
        $envelope = new Zend_Form_Element_Radio('POST_IC_ENVELOPE_FECHADO');
        $envelope->setLabel('*Envelope Único(Pacote)?')
                 ->setRequired(true)
                 ->setMultiOptions(array('S' => 'Sim', 'N' => 'Não'))
                 ->setDescription('Sim: Documentos encaminhados no mesmo envelope; Não: Documentos encaminhados em envelopes diferentes;');
        
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($acao,
                                $orgaoDestino,
                                $destinatario,
                                $tratamento,
                                $endereco,
                                $bairro,
                                $TempoDias,
                                $cidade,
                                $estado,
                                $pais,
                                $cep,
                                $tpPostagem,
                                $envelope,
                                $submit));
    }
}