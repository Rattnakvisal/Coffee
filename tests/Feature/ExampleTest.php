<?php

test('the application redirects from the welcome page', function () {
    $response = $this->get('/');

    $response->assertRedirect();
});
