<?php

namespace Tests\Unit;

use App\Models\Rider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_rider_ids_are_normalized_to_start_with_pya(): void
    {
        $this->assertSame('PYA12647', Rider::normalizeRiderId('PY12647'));
        $this->assertSame('PYA12647', Rider::normalizeRiderId('PYA12647'));
        $this->assertSame('PYASC00065', Rider::normalizeRiderId('SC00065'));
        $this->assertSame('12647', Rider::riderIdSuffix('PYA12647'));
        $this->assertSame('12647', Rider::riderIdSuffix('PY12647'));
        $this->assertSame('SC00065', Rider::riderIdSuffix('SC00065'));

        $rider = Rider::query()->create([
            'rider_id' => 'PY12647',
            'name' => 'Rider PYA',
        ]);

        $this->assertSame('PYA12647', $rider->rider_id);
        $this->assertDatabaseHas('riders', [
            'rider_id' => 'PYA12647',
        ]);
    }
}
