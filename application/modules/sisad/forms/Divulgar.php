<?php
class Sisad_Form_Divulgar extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setName('divulgarDocumento');

        $id_lista = new Zend_Form_Element_Select('ID_LISTA');
        $id_lista->setRequired(false)
                ->setLabel('Selecione o Tipo:')
                ->setOptions(array('style' => 'width:200px', 'class' => 'x-form-text'))
                ->addMultioptions(array("G" => "Grupos de Divulgação",
                                        "C" => "Componentes de Grupos de Divulgação"
                                        ));
        
        $list_id_componente = new Zend_Form_Element_Text('LIST_ID_COMPONENTE');
        $list_id_componente->setRequired(false)
                ->setLabel('Adicionar Lista de Divulgação: ')
                ->setAttrib('style', 'width: 540px;')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Ex.: SERVIDORES');

        $list_id_grupo_divulgacao = new Zend_Form_Element_Text('LIST_ID_GRUPO_DIVULGACAO');
        $list_id_grupo_divulgacao->setRequired(false)
                ->setLabel('Adicionar Grupo de Divulgação: ')
                ->setAttrib('style', 'width: 540px;')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um Grupo de Divulgação que a mesmo será adicionado à lista. Ex.: TR22 ou Maria');

        $list_dt_inicio_divulgacao = new Zend_Form_Element_Text('LIST_DT_INICIO_DIVULGACAO');
        $list_dt_inicio_divulgacao->setLabel('*Data inicial:')
                ->setValue("");

        $list_dt_fim_divulgacao = new Zend_Form_Element_Text('LIST_DT_FIM_DIVULGACAO');
        $list_dt_fim_divulgacao->setLabel('*Data final:')
                ->setValue("");


        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array(
//            $id_lista,
            $list_id_componente,
            $list_id_grupo_divulgacao,
            $list_dt_inicio_divulgacao,
            $list_dt_fim_divulgacao,
            $submit
        ));

     }

}