<?php

test('privacy policy page loads successfully and contains title', function () {
    $response = $this->get('/politica-de-privacidade');

    $response->assertStatus(200);
    $response->assertSee('Política de Privacidade');
    $response->assertSee('Igreja Presbiteriana Renovada de Viamão');
});

test('terms of service page loads successfully and contains title', function () {
    $response = $this->get('/termos-de-servico');

    $response->assertStatus(200);
    $response->assertSee('Termos de Serviço');
    $response->assertSee('Igreja Presbiteriana Renovada de Viamão');
});

test('landing page contains links to legal pages', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee(route('privacy'));
    $response->assertSee(route('terms'));
});

test('landing page contains app name and google oauth purpose explanation', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Projeto Lâmpada');
    $response->assertSee('Finalidade do App &amp; Autenticação Segura', false);
    $response->assertSee('sincronizar seu histórico pessoal de leitura bíblica diária');
});
