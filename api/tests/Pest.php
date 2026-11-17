<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| PHPUnit test case class. By default, that class is "PHPUnit\Framework\TestCase".
| Feature tests need the Laravel application, so they use our TestCase.
|
*/

uses(TestCase::class)->in('Feature', 'Unit');
