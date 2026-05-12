<?php

test('a customer can create a male address request and upload a payment slip', function () {
    $this->markTestSkipped('Delivery request endpoints not yet fully implemented');
});

test('an operator can quote an inspection request and verify the payment', function () {
    $this->markTestSkipped('Full payment verification flow not yet fully implemented');
});

test('a different operator cannot modify another operators accepted request', function () {
    $this->markTestSkipped('Operator access control not yet fully implemented');
});
