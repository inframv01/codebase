<?php

test('a customer cannot access operator endpoints', function () {
    $this->markTestSkipped('Operator access control not yet fully implemented');
});

test('an operator can create an atoll', function () {
    $this->markTestSkipped('Operator atoll creation not yet fully implemented');
});

test('operator index only shows unassigned and own delivery requests', function () {
    $this->markTestSkipped('Operator delivery request filtering not yet fully implemented');
});

test('operator cannot stage delivery before acceptance', function () {
    $this->markTestSkipped('Operator staging validation not yet fully implemented');
});
