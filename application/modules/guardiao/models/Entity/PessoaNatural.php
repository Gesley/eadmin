<?php
/**
 * O Model Entity é a Classe responsável pela definição dos atributos e métodos 
 * get e set de cada atributo.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_Entity_PessoaNatural
{
    private $_id;
    private $_numeroCpf;
    private $_nome;
    private $_numeroCnh;
    private $_ufCnh;
    private $_dataEmissaoCnh;
    private $_dataValidadeCnh;
    private $_categoriaCnh;
    private $_dataNascimento;
    private $_localNascimento;
    private $_estadoCivil;
    private $_numeroIdentidade;
    private $_orgaoEmissorIdentidade;
    private $_dataEmissaoIdentidade;
    private $_ufEmissorIdentidade;
    private $_identificadorPessoa;

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getNumeroCpf() {
        return $this->_numeroCpf;
    }

    public function setNumeroCpf($numeroCpf) {
        $this->_numeroCpf = $numeroCpf;
        return $this;
    }

    public function getNome() {
        return $this->_nome;
    }

    public function setNome($nome) {
        $this->_nome = $nome;
        return $this;
    }

    public function getNumeroCnh() {
        return $this->_numeroCnh;
    }

    public function setNumeroCnh($numeroCnh) {
        $this->_numeroCnh = $numeroCnh;
        return $this;
    }

    public function getUfCnh() {
        return $this->_ufCnh;
    }

    public function setUfCnh($ufCnh) {
        $this->_ufCnh = $ufCnh;
        return $this;
    }

    public function getDataEmissaoCnh() {
        return $this->_dataEmissaoCnh;
    }

    public function setDataEmissaoCnh($dataEmissaoCnh) {
        $this->_dataEmissaoCnh = $dataEmissaoCnh;
        return $this;
    }

    public function getDataValidadeCnh() {
        return $this->_dataValidadeCnh;
    }

    public function setDataValidadeCnh($dataValidadeCnh) {
        $this->_dataValidadeCnh = $dataValidadeCnh;
        return $this;
    }

    public function getCategoriaCnh() {
        return $this->_categoriaCnh;
    }

    public function setCategoriaCnh($categoriaCnh) {
        $this->_categoriaCnh = $categoriaCnh;
        return $this;
    }

    public function getDataNascimento() {
        return $this->_dataNascimento;
    }

    public function setDataNascimento($dataNascimento) {
        $this->_dataNascimento = $dataNascimento;
        return $this;
    }

    public function getLocalNascimento() {
        return $this->_localNascimento;
    }

    public function setLocalNascimento($localNascimento) {
        $this->_localNascimento = $localNascimento;
        return $this;
    }

    public function getEstadoCivil() {
        return $this->_estadoCivil;
    }

    public function setEstadoCivil($estadoCivil) {
        $this->_estadoCivil = $estadoCivil;
        return $this;
    }

    public function getNumeroIdentidade() {
        return $this->_numeroIdentidade;
    }

    public function setNumeroIdentidade($numeroIdentidade) {
        $this->_numeroIdentidade = $numeroIdentidade;
        return $this;
    }

    public function getOrgaoEmissorIdentidade() {
        return $this->_orgaoEmissorIdentidade;
    }

    public function setOrgaoEmissorIdentidade($orgaoEmissorIdentidade) {
        $this->_orgaoEmissorIdentidade = $orgaoEmissorIdentidade;
        return $this;
    }

    public function getDataEmissaoIdentidade() {
        return $this->_dataEmissaoIdentidade;
    }

    public function setDataEmissaoIdentidade($dataEmissaoIdentidade) {
        $this->_dataEmissaoIdentidade = $dataEmissaoIdentidade;
        return $this;
    }

    public function getUfEmissorIdentidade() {
        return $this->_ufEmissorIdentidade;
    }

    public function setUfEmissorIdentidade($ufEmissorIdentidade) {
        $this->_ufEmissorIdentidade = $ufEmissorIdentidade;
        return $this;
    }

    public function getIdentificadorPessoa() {
        return $this->_identificadorPessoa;
    }

    public function setIdentificadorPessoa($identificadorPessoa) {
        $this->_identificadorPessoa = $identificadorPessoa;
        return $this;
    }

}
