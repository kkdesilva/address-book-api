<?php

it('forcibly sets the response to json', function (): void {
    $response = $this->get(route('api.v1.contacts.index'));
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/json');
});
