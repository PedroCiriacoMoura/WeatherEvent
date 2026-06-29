<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait RequiresSpatialDatabase
{
    protected function skipWithoutSpatialSupport(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Spatial columns require a MySQL/PostgreSQL connection.');
        }
    }
}
