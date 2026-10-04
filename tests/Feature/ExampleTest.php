<?php

test('la raíz envía a quien no ha iniciado sesión al login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
