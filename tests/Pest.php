<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| This file configures global Pest behavior.
|
| By using pest()->extend(), all test files in tests/Feature will
| automatically have access to HTTP helpers (get, post, actingAs, etc.)
| without needing to call uses(TestCase::class) in each individual file.
|
*/

pest()->extend(TestCase::class)->in('Feature');
