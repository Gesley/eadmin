<?php
/*
 * Forma que chama todos os campos do add, poar 
 */

class Sisad_Form_ProtocoloAddFaixaPostagem extends Zend_Form
{
    public function init()
    {
        $tppoTipoPostagem = new Application_Model_DbTable_SadTbTppoTipoPostagem();
        $getTipoPostagem = $tppoTipoPostagem->getTipoPostagem();
        
        $this->setAction('add')
             ->setMethod('post')
             ->setName('AddProtocolo');
        
        /*
         * Forms Faixa Postagem
         */
        $tipo = new Zend_Form_Element_Hidden('TIPO');
        $tipo->setValue('addFaixaPostagem');
        
        $idPjur = new Zend_Form_Element_Hidden('PJUR_ID_PESSOA');
        
        $tpPostagem = new Zend_Form_Element_Select('SELECT_ID_TIPO_POSTAGEM');
        $tpPostagem->setRequired(true)
                 ->setLabel('Preferência de Postagem:')
                 ->addMultiOptions(array(0 => 'Selecione o Tipo de Postagem'));
        foreach ($getTipoPostagem as $tiposPostagem):
            $tpPostagem->addMultiOptions(array($tiposPostagem["TPPO_ID_TIPO_POSTAGEM"] => $tiposPostagem["TPPO_DS_TIPO_POSTAGEM"]));
        endforeach;
        
        $idTpPostagem = new Zend_Form_Element_Hidden('FPDP_ID_TIPO_POSTAGEM');
        
        $nomeTpPostagem = new Zend_Form_Element_Text('FPDP_NO_TIPO_POSTAGEM');
        $nomeTpPostagem->setRequired(true)
                 ->setLabel('*Preferência de Postagem:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width:165px', 'disabled' => 'disabled'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $numeroInicial = new Zend_Form_Element_Text('FPDP_NR_NUMERO_INICIAL');
        $numeroInicial->setRequired(true)
                 ->setLabel('*Número Inicial:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('maxLength' => 9))
                 ->addValidator('Alnum', false, true)
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $numeroFinal = new Zend_Form_Element_Text('FPDP_NR_NUMERO_FINAL');
        $numeroFinal ->setRequired(true)
                 ->setLabel('*Número Final:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('maxLength' => 9))
                 ->addValidator('Alnum', false, true)
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $letraInicial = new Zend_Form_Element_Text('FPDP_DS_LETRA_INICIAL');
        $letraInicial ->setRequired(true)
                 ->setLabel('*Letra Inicial:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('maxLength' => 2))
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $letraFinal = new Zend_Form_Element_Text('FPDP_DS_LETRA_FINAL');
        $letraFinal ->setRequired(true)
                 ->setLabel('*Letra Final:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('maxLength' => 2))
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $matriculaCadastrante = new Zend_Form_Element_Text('FPDP_CD_MATRICULA_INCLUSAO');
        $matriculaCadastrante ->setRequired(true)
                 ->setLabel('*Reponsável Pela Inclusão:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px', 'disabled' => 'disabled'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $postagemAtivo = new Zend_Form_Element_Checkbox('FPDP_IC_POSTAGEM_ATIVO');
        $postagemAtivo ->setRequired(true)
                 ->setLabel('*Ativo:');
        
        $DHInclusao = new Zend_Form_Element_Text('FPDP_DH_INCLUSAO_POSTAGEM');
        $DHInclusao ->setRequired(true)
                 ->setLabel('*Data / Hora Inclusão:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $ultimoNumero = new Zend_Form_Element_Text('FPDP_NR_ULTIMO_NUMERO');
        $ultimoNumero ->setRequired(true)
                 ->setLabel('*Ultimo Número Utilizado:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('maxLength' => 9))
                 ->addValidator('Alnum', false, true)
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $numeroSegurancaPostagem = new Zend_Form_Element_Text('FPDP_NR_SEGURANCA_POSTAGEM');
        $numeroSegurancaPostagem ->setRequired(true)
                 ->setLabel('*Número de Segurança:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setOptions(array('maxLength' => 9))
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 100px'))
                 ->addValidator('StringLength', false, array(5, 200));
        
        $matriculaGestor = new Zend_Form_Element_Text('FPDP_CD_MATRICULA_GESTOR');
        $matriculaGestor ->setRequired(true)
                 ->setLabel('*Gestor:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 400px'))
                 ->addValidator('StringLength', false, array(5, 200))
                 ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        
        $consultar = new Zend_Form_Element_Submit('Consultar');
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tipo,
                                $tpPostagem,
                                $nomeTpPostagem,
                                $idTpPostagem,
                                $numeroInicial,
                                $numeroFinal,
                                $letraInicial,
                                $letraFinal,
                                $matriculaCadastrante,
                                $postagemAtivo,
                                $DHInclusao,
                                $ultimoNumero,
                                $numeroSegurancaPostagem,
                                $matriculaGestor,
                                $consultar,
                                $submit));
    }
}