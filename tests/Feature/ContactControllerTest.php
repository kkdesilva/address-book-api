<?php
declare(strict_types=1);

it('can fetch all contacts', function () {
    $response = $this->getJson('/api/v1/contacts');
    $response->assertOk();
});
