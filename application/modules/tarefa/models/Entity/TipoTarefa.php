<?php
/**
 * O Model Entity é a Classe responsável pela definição dos atributos e métodos 
 * get e set de cada atributo.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_Entity_TipoTarefa 
{

    private $_id;
    private $_nome;
    private $_descricao;

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }
    
    public function getNome() {
        return $this->_nome;
    }

    public function setNome($nome) {
        $this->_nome = $nome;
        return $this;
    }

    public function getDescricao() {
        return $this->_descricao;
    }

    public function setDescricao($decricao) {
        $this->_descricao = $decricao;
        return $this;
    }

}
