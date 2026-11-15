<?php

it('reports api and database health', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson(['status' => 'ok', 'database' => 'ok']);
});
