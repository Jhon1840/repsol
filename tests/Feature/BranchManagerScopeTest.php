<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Riders\Pages\CreateRider;
use App\Filament\Resources\Riders\Pages\EditRider;
use App\Filament\Resources\Riders\Pages\ListRiders;
use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_marketing_global_user_sees_riders_from_all_branches(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing Global',
            'email' => 'marketing-global@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => User::BRANCH_GLOBAL,
        ]);

        $santaCruz = Rider::query()->create([
            'rider_id' => 'MKTGLOBALSCZ001',
            'name' => 'Rider Marketing Global Santa Cruz',
            'branch' => 'SANTA CRUZ',
        ]);

        $laPaz = Rider::query()->create([
            'rider_id' => 'MKTGLOBALLPZ001',
            'name' => 'Rider Marketing Global La Paz',
            'branch' => 'LA PAZ',
        ]);

        $visibleIds = Rider::query()
            ->visibleTo($user)
            ->pluck('rider_id')
            ->all();

        $this->assertContains($santaCruz->rider_id, $visibleIds);
        $this->assertContains($laPaz->rider_id, $visibleIds);
        $this->assertNull($user->branchScope());
    }

    public function test_marketing_user_can_export_only_riders_from_their_branch_scope(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing Export SCZ',
            'email' => 'marketing-export-scz@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        $santaCruz = Rider::query()->create([
            'rider_id' => 'EXPORTSCZ001',
            'name' => 'Export Rider Santa Cruz',
            'branch' => 'SANTA CRUZ',
        ]);

        $laPaz = Rider::query()->create([
            'rider_id' => 'EXPORTLPZ001',
            'name' => 'Export Rider La Paz',
            'branch' => 'LA PAZ',
        ]);

        $this->actingAs($user);

        $page = app(ListRiders::class);

        $canExportMethod = new ReflectionMethod($page, 'canExportRiders');
        $canExportMethod->setAccessible(true);
        $exportQueryMethod = new ReflectionMethod($page, 'exportRidersQuery');
        $exportQueryMethod->setAccessible(true);

        $exportedIds = $exportQueryMethod->invoke($page)
            ->pluck('rider_id')
            ->all();

        $this->assertTrue($canExportMethod->invoke($page));
        $this->assertContains($santaCruz->rider_id, $exportedIds);
        $this->assertNotContains($laPaz->rider_id, $exportedIds);
    }

    public function test_marketing_global_user_can_export_riders_from_all_branches(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing Export Global',
            'email' => 'marketing-export-global@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => User::BRANCH_GLOBAL,
        ]);

        $santaCruz = Rider::query()->create([
            'rider_id' => 'EXPORTGLOBALSCZ001',
            'name' => 'Export Global Santa Cruz',
            'branch' => 'SANTA CRUZ',
        ]);

        $laPaz = Rider::query()->create([
            'rider_id' => 'EXPORTGLOBALLPZ001',
            'name' => 'Export Global La Paz',
            'branch' => 'LA PAZ',
        ]);

        $this->actingAs($user);

        $page = app(ListRiders::class);

        $canExportMethod = new ReflectionMethod($page, 'canExportRiders');
        $canExportMethod->setAccessible(true);
        $exportQueryMethod = new ReflectionMethod($page, 'exportRidersQuery');
        $exportQueryMethod->setAccessible(true);

        $exportedIds = $exportQueryMethod->invoke($page)
            ->pluck('rider_id')
            ->all();

        $this->assertTrue($canExportMethod->invoke($page));
        $this->assertContains($santaCruz->rider_id, $exportedIds);
        $this->assertContains($laPaz->rider_id, $exportedIds);
    }

    public function test_branch_manager_cannot_export_riders(): void
    {
        $user = User::query()->create([
            'name' => 'Branch Manager Sin Export',
            'email' => 'branch-manager-no-export@example.com',
            'password' => 'password',
            'role' => User::ROLE_BRANCH_MANAGER,
            'branch' => 'SANTA CRUZ',
        ]);

        $this->actingAs($user);

        $page = app(ListRiders::class);

        $canExportMethod = new ReflectionMethod($page, 'canExportRiders');
        $canExportMethod->setAccessible(true);

        $this->assertFalse($canExportMethod->invoke($page));
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

        $global = User::query()->make([
            'role' => User::ROLE_MARKETING,
            'branch' => User::BRANCH_GLOBAL,
        ]);

        $withoutBranch = User::query()->make([
            'role' => User::ROLE_MARKETING,
            'branch' => null,
        ]);

        $this->assertTrue($withBranch->canAccessPanel(filament()->getDefaultPanel()));
        $this->assertTrue($global->canAccessPanel(filament()->getDefaultPanel()));
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

    public function test_non_admin_users_can_edit_rider_data_but_not_points(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing Edita Rider',
            'email' => 'marketing-edit-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'EDITNOPTS001',
            'name' => 'Nombre Original',
            'branch' => 'SANTA CRUZ',
            'rango' => 'BRONCE',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'branch' => 'SANTA CRUZ',
            'movement_type' => 'purchase',
            'points' => 120,
            'occurred_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(EditRider::class, ['record' => $rider->getRouteKey()])
            ->assertFormFieldIsDisabled('points_balance');

        $page = app(EditRider::class);
        $page->record = $rider;

        $mutateMethod = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
        $mutateMethod->setAccessible(true);
        $handleMethod = new ReflectionMethod($page, 'handleRecordUpdate');
        $handleMethod->setAccessible(true);

        $data = $mutateMethod->invoke($page, [
            'rider_id' => $rider->rider_id,
            'name' => 'Nombre Actualizado',
            'branch' => 'SANTA CRUZ',
            'rango' => 'PLATA',
            'points_balance' => 999,
        ]);

        $handleMethod->invoke($page, $rider, $data);

        $rider->refresh();

        $this->assertSame('Nombre Actualizado', $rider->name);
        $this->assertSame('PLATA', $rider->rango);
        $this->assertSame(120, (int) $rider->movements()->sum('points'));
        $this->assertDatabaseMissing('rider_movements', [
            'rider_id' => $rider->getKey(),
            'movement_type' => 'manual_adjustment',
        ]);
    }

    public function test_manual_rider_creation_combines_first_names_and_last_names(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Crea Rider',
            'email' => 'admin-create-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        $page = app(CreateRider::class);

        $mutateMethod = new ReflectionMethod($page, 'mutateFormDataBeforeCreate');
        $mutateMethod->setAccessible(true);
        $handleMethod = new ReflectionMethod($page, 'handleRecordCreation');
        $handleMethod->setAccessible(true);

        $data = $mutateMethod->invoke($page, [
            'rider_id' => 'MANUAL001',
            'first_names' => 'Sandra Maria',
            'last_names' => 'Parada Caballero',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
        ]);

        $rider = $handleMethod->invoke($page, $data);

        $this->assertSame('PYAMANUAL001', $rider->rider_id);
        $this->assertSame('Sandra Maria Parada Caballero', $rider->name);
        $this->assertDatabaseHas('riders', [
            'rider_id' => 'PYAMANUAL001',
            'name' => 'Sandra Maria Parada Caballero',
            'creation_source' => 'manual',
        ]);
    }

    public function test_manual_rider_creation_persists_combined_names_from_form_state(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Form Crea Rider',
            'email' => 'admin-form-create-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateRider::class)
            ->fillForm([
                'rider_id' => 'FORM001',
                'first_names' => 'Sandra Maria',
                'last_names' => 'Parada Caballero',
                'branch' => 'SANTA CRUZ',
                'rango' => 'ORO',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('riders', [
            'rider_id' => 'PYAFORM001',
            'name' => 'Sandra Maria Parada Caballero',
            'creation_source' => 'manual',
        ]);
    }

    public function test_manual_rider_edit_persists_combined_names_from_form_state(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Form Edita Rider',
            'email' => 'admin-form-edit-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'PYAEDITFORM001',
            'name' => 'Nombre Original',
            'branch' => 'SANTA CRUZ',
            'rango' => 'BRONCE',
        ]);

        $this->actingAs($user);

        Livewire::test(EditRider::class, ['record' => $rider->getRouteKey()])
            ->fillForm([
                'rider_id' => 'EDITFORM001',
                'first_names' => 'Nombre',
                'last_names' => 'Actualizado Rider',
                'branch' => 'LA PAZ',
                'rango' => 'PLATA',
                'points_balance' => 0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $rider->refresh();

        $this->assertSame('Nombre Actualizado Rider', $rider->name);
        $this->assertSame('PYAEDITFORM001', $rider->rider_id);
        $this->assertSame('LA PAZ', $rider->branch);
        $this->assertSame('PLATA', $rider->rango);
    }

    public function test_manual_rider_creation_rejects_duplicate_normalized_id(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Duplica Rider',
            'email' => 'admin-duplicate-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        Rider::query()->create([
            'rider_id' => 'PYAABC001',
            'name' => 'Rider Existente',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
        ]);

        $this->actingAs($user);

        Livewire::test(CreateRider::class)
            ->fillForm([
                'rider_id' => 'ABC001',
                'first_names' => 'Nuevo',
                'last_names' => 'Duplicado',
                'branch' => 'SANTA CRUZ',
                'rango' => 'ORO',
            ])
            ->call('create')
            ->assertHasFormErrors(['rider_id']);

        $this->assertDatabaseCount('riders', 1);
    }
}
