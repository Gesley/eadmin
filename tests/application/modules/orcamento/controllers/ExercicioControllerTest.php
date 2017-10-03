<?php

/**
 * Classe responsável pelos testes do controller exercício do e-orcamento.
 * @author Victor Eduardo Barreto <vesilva1@stefanini.com>
 * @copyright (c) 2015 TRF1.
 * @version 1.1
 */
class ExercicioControllerTest extends ControllerTestCase {

    /**
     * Função que inicia bootstrap e autentica no e-admin.
     * @author Victor Eduardo Barreto <vesilva1@stefanini.com>
     * @version 1.1
     */
    protected function setUp () {

        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();

        $this->autenticar('matricula', 'senha', 'TRF1');
    }

    /**
     * Função para testar o método indexAction.
     * @test
     * @author Victor Eduardo Barreto <vesilva1@stefanini.com>
     * @version 1.1
     */
    public function testIndex () {

        // Prepara dados para ser enviado na requisição.
        $this->request->setMethod('POST')
            ->setPost(array(
                'teste' => '1'
        ));

        // Dispara a requisição para o método desejado.
        $this->dispatch('orcamento/exercicio/index');

        // Recupera os dados de saída do método.
        $resultado = $this->getResponse()->outputBody();

        // Testa se o resultado da saída está de acordo com o esperado.
        $this->assertEquals($resultado, 3);
    }

    /**
     * Função para testar o método incluirAction.
     * @test
     * @author Victor Eduardo Barreto <vesilva1@stefanini.com>
     * @version 1.1
     */
//    public function _testIncluir () {
//
//        $this->dispatch('orcamento/exercicio/incluir');
//        $this->assertModule('orcamento');
//        $this->assertController('exercicio');
//        $this->assertAction('incluir');
//
//        $request = $this->getRequest();
//
//        $request->setMethod('POST');
//
//        $request->setParams(array(
//            'ANOE_AA_ANO' => '2017',
//            'ANOE_DS_OBSERVACAO' => 'testenovo',
//            'ANOE_CD_MATRICULA_INCLUSAO' => 'TR19209PS',
//            'Enviar' => 'Enviar'
//        ));
//
//        $this->dispatch('orcamento/exercicio/incluir');
//        $this->assertModule('orcamento');
//        $this->assertController('exercicio');
//        $this->assertAction('incluir');
//        $this->assertRedirect();
//    }
}
