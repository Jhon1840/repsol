<?php

namespace Tests\Unit;

use App\Models\Rider;
use App\Models\User;
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

    public function test_creation_source_label_treats_null_creator_as_system(): void
    {
        $rider = Rider::query()->create([
            'rider_id' => 'PYA12702330',
            'name' => 'Luis Fernando Llancu Vera',
            'creation_source' => 'manual',
            'created_by' => null,
        ]);

        $this->assertSame('Sistema', $rider->creationSourceLabel());
    }

    public function test_creation_source_label_keeps_manual_when_created_by_user(): void
    {
        $user = User::factory()->create();

        $rider = Rider::query()->create([
            'rider_id' => 'PYA9713129',
            'name' => 'Franklin Lijeron Contreras',
            'creation_source' => 'manual',
            'created_by' => $user->getKey(),
        ]);

        $this->assertSame('Manual', $rider->creationSourceLabel());
    }

    public function test_creation_source_label_keeps_excel_origin(): void
    {
        $rider = Rider::query()->create([
            'rider_id' => 'PYA8306521',
            'name' => 'Ricardo Gabriel Quito Ramos',
            'creation_source' => 'excel',
            'created_by' => null,
        ]);

        $this->assertSame('Excel', $rider->creationSourceLabel());
    }
}
