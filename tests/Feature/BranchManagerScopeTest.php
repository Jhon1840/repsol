<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Riders\Pages\ListRiders;
use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class BranchManagerScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_manager_only_sees_riders_from_their_branch_scope(): void
    {
        $user = User::query()->create([
            'name' => 'Encargado SCZ',
            'email' => 'scz@example.com',
            'password' => 'password',
            'role' => User::ROLE_BRANCH_MANAGER,
            'branch' => 'SANTA CRUZ',
        ]);

        $santaCruz = Rider::query()->create([
            'rider_id' => 'SCZ001',
            'name' => 'Rider Santa Cruz',
            'branch' => 'SANTA CRUZ',
        ]);

        $laPaz = Rider::query()->create([
            'rider_id' => 'LPZ001',
            'name' => 'Rider La Paz',
            'branch' => 'LA PAZ',
        ]);

        $movementOnly = Rider::query()->create([
            'rider_id' => 'MOV001',
            'name' => 'Rider Movimiento',
            'branch' => null,
        ]);

        RiderMovement::query()->create([
            'rider_id' => $movementOnly->getKey(),
            'branch' => 'SANTA CRUZ',
            'points' => 100,
        ]);

        $visibleIds = Rider::query()
            ->visibleTo($user)
            ->pluck('rider_id')
            ->all();

        $this->assertContains($santaCruz->rider_id, $visibleIds);
        $this->assertNotContains($movementOnly->rider_id, $visibleIds);
        $this->assertNotContains($laPaz->rider_id, $visibleIds);
    }

    public function test_marketing_user_only_sees_riders_from_their_branch_scope(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing SCZ',
            'email' => 'marketing-scz@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        $santaCruz = Rider::query()->create([
            'rider_id' => 'MKTSCZ001',
            'name' => 'Rider Marketing Santa Cruz',
            'branch' => 'SANTA CRUZ',
        ]);

        $laPaz = Rider::query()->create([
            'rider_id' => 'MKTLPZ001',
            'name' => 'Rider Marketing La Paz',
            'branch' => 'LA PAZ',
        ]);

        $visibleIds = Rider::query()
            ->visibleTo($user)
            ->pluck('rider_id')
            ->all();

        $this->assertContains($santaCruz->rider_id, $visibleIds);
        $this->assertNotContains($laPaz->rider_id, $visibleIds);
    }

    public function test_marketing_user_points_balance_is_scoped_to_their_branch(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing LPZ',
            'email' => 'marketing-lpz@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'LA PAZ',
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'MKTBAL001',
            'name' => 'Rider Balance Marketing',
            'branch' => 'LA PAZ',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'LA PAZ',
            'points' => 150,
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'SANTA CRUZ',
            'points' => 900,
        ]);

        $scopedRider = Rider::query()
            ->withPointsBalance($user)
            ->whereKey($rider->getKey())
            ->firstOrFail();

        $this->assertSame(1050, $scopedRider->points_balance);
    }

    public function test_admin_and_marketing_see_same_points_for_rider_branch_balance(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Global',
            'email' => 'admin-global@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'branch' => null,
        ]);

        $marketing = User::query()->create([
            'name' => 'Marketing SCZ',
            'email' => 'marketing-balance-scz@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'SAMEROLE001',
            'name' => 'Rider Mismo Saldo',
            'branch' => 'SANTA CRUZ',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'SANTA CRUZ',
            'points' => 3007,
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'LA PAZ',
            'points' => 10661,
        ]);

        $adminRider = Rider::query()
            ->withPointsBalance($admin)
            ->whereKey($rider->getKey())
            ->firstOrFail();

        $marketingRider = Rider::query()
            ->withPointsBalance($marketing)
            ->whereKey($rider->getKey())
            ->firstOrFail();

        $this->assertSame(13668, $adminRider->points_balance);
        $this->assertSame($adminRider->points_balance, $marketingRider->points_balance);
    }

    public function test_marketing_user_spent_points_chart_is_scoped_to_their_branch(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing Gastos LPZ',
            'email' => 'marketing-spent-lpz@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'LA PAZ',
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'MKTSPENT001',
            'name' => 'Rider Gasto Marketing',
            'branch' => 'LA PAZ',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'LA PAZ',
            'points' => -40,
            'occurred_at' => '2026-04-10 10:00:00',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'SANTA CRUZ',
            'points' => -900,
            'occurred_at' => '2026-04-10 10:00:00',
        ]);

        $this->actingAs($user);

        $dashboard = app(Dashboard::class);
        $dashboard->pointsChartStartDate = '2026-04-01';
        $dashboard->pointsChartEndDate = '2026-04-30';

        $method = new ReflectionMethod($dashboard, 'getPointsChartData');
        $method->setAccessible(true);

        $chart = $method->invoke($dashboard);

        $this->assertSame(940, $chart['spentTotal']);
    }

    public function test_branch_manager_must_have_branch_to_access_panel(): void
    {
        $withBranch = User::query()->make([
            'role' => User::ROLE_BRANCH_MANAGER,
            'branch' => 'SANTA CRUZ',
        ]);

        $withoutBranch = User::query()->make([
            'role' => User::ROLE_BRANCH_MANAGER,
            'branch' => null,
        ]);

        $this->assertTrue($withBranch->canAccessPanel(filament()->getDefaultPanel()));
        $this->assertFalse($withoutBranch->canAccessPanel(filament()->getDefaultPanel()));
    }

    public function test_marketing_user_must_have_branch_to_access_panel(): void
    {
        $withBranch = User::query()->make([
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        $withoutBranch = User::query()->make([
            'role' => User::ROLE_MARKETING,
            'branch' => null,
        ]);

        $this->assertTrue($withBranch->canAccessPanel(filament()->getDefaultPanel()));
        $this->assertFalse($withoutBranch->canAccessPanel(filament()->getDefaultPanel()));
    }

    public function test_non_admin_users_cannot_trigger_excel_upload_from_dashboard_or_riders_list(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing Sin Excel',
            'email' => 'marketing-no-excel@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        $this->actingAs($user);

        app(Dashboard::class)->storeExcel();
        app(ListRiders::class)->storeExcel();

        $this->assertDatabaseCount('uploaded_documents', 0);
        $this->assertDatabaseCount('rider_movements', 0);
    }
}
