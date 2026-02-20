<?php

declare(strict_types=1);

pest()->extend(Tests\TestCase::class)
    ->in('Arch');

pest()->extend(Tests\FeatureTestCase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');
