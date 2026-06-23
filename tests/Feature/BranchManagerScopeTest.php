<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Articulos\ArticulosResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Riders\Pages\CreateRider;
use App\Filament\Resources\Riders\Pages\EditRider;
use App\Filament\Resources\Riders\Pages\ListRiders;
use App\Filament\Resources\Riders\RiderResource;
use App\Models\Rider;
use App\Models\RiderAuditLog;
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

    public function test_two_word_rider_name_is_split_between_first_and_last_name_on_edit(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Edita Nombre Corto',
            'email' => 'admin-edit-short-name@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        $rider = Rider::query()->create([
            'rider_id' => 'SHORTNAME001',
            'name' => 'carlos arce',
            'branch' => 'LA PAZ',
            'rango' => 'BRONCE',
            'created_by' => $user->getKey(),
            'creation_source' => 'manual',
        ]);

        Livewire::test(EditRider::class, ['record' => $rider->getRouteKey()])
            ->assertFormSet([
                'first_names' => 'carlos',
                'last_names' => 'arce',
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

    public function test_manual_rider_creation_rejects_numeric_only_name_parts(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Rechaza Ceros',
            'email' => 'admin-reject-zero-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateRider::class)
            ->fillForm([
                'rider_id' => 'ZERO001',
                'first_names' => 'Juan1',
                'last_names' => 'Perez2',
                'branch' => 'LA PAZ',
                'rango' => 'BRONCE',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_names',
                'last_names',
            ]);

        $this->assertDatabaseMissing('riders', [
            'rider_id' => 'PYAZERO001',
        ]);
    }

    public function test_rider_updates_are_written_to_audit_log(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Audita Rider',
            'email' => 'admin-audit-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        $rider = Rider::query()->create([
            'rider_id' => 'AUDIT001',
            'name' => 'Nombre Original',
            'branch' => 'LA PAZ',
            'rango' => 'BRONCE',
            'created_by' => $user->getKey(),
            'creation_source' => 'manual',
        ]);

        $rider->update([
            'name' => 'Nombre Actualizado',
            'rango' => 'PLATA',
            'updated_by' => $user->getKey(),
        ]);

        $createdLog = RiderAuditLog::query()
            ->where('rider_id', $rider->getKey())
            ->where('event', 'created')
            ->firstOrFail();
        $updatedLog = RiderAuditLog::query()
            ->where('rider_id', $rider->getKey())
            ->where('event', 'updated')
            ->firstOrFail();

        $this->assertSame($user->getKey(), $createdLog->user_id);
        $this->assertSame($user->getKey(), $updatedLog->user_id);
        $this->assertSame('Nombre Original', $updatedLog->old_values['name']);
        $this->assertSame('Nombre Actualizado', $updatedLog->new_values['name']);
        $this->assertSame('BRONCE', $updatedLog->old_values['rango']);
        $this->assertSame('PLATA', $updatedLog->new_values['rango']);
    }

    public function test_rider_detail_renders_audit_logs(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Ve Audit Logs',
            'email' => 'admin-view-audit-rider@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        $rider = Rider::query()->create([
            'rider_id' => 'AUDITVIEW001',
            'name' => 'Nombre Original',
            'branch' => 'LA PAZ',
            'rango' => 'BRONCE',
            'created_by' => $user->getKey(),
            'creation_source' => 'manual',
        ]);

        $rider->update([
            'name' => 'Nombre Cambiado',
            'updated_by' => $user->getKey(),
        ]);

        $this->get(RiderResource::getUrl('view', ['record' => $rider]))
            ->assertOk()
            ->assertSee('Audit logs del rider')
            ->assertSee('Nombre Original')
            ->assertSee('Nombre Cambiado');
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

    public function test_advisor_can_access_panel_and_only_see_their_created_riders(): void
    {
        $advisor = User::query()->create([
            'name' => 'Asesor Uno',
            'email' => 'asesor-uno@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADVISOR,
        ]);

        $otherAdvisor = User::query()->create([
            'name' => 'Asesor Dos',
            'email' => 'asesor-dos@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADVISOR,
        ]);

        $ownRider = Rider::query()->create([
            'rider_id' => 'ADV001',
            'name' => 'Rider Propio',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
            'created_by' => $advisor->getKey(),
            'creation_source' => 'manual',
        ]);

        $otherRider = Rider::query()->create([
            'rider_id' => 'ADV002',
            'name' => 'Rider Ajeno',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
            'created_by' => $otherAdvisor->getKey(),
            'creation_source' => 'manual',
        ]);

        $this->actingAs($advisor);

        $visibleIds = Rider::query()
            ->visibleTo($advisor)
            ->pluck('rider_id')
            ->all();

        $this->assertTrue($advisor->canAccessPanel(filament()->getDefaultPanel()));
        $this->assertContains($ownRider->rider_id, $visibleIds);
        $this->assertNotContains($otherRider->rider_id, $visibleIds);
        $this->assertTrue(RiderResource::canCreate());
        $this->assertTrue(RiderResource::canEdit($ownRider));
        $this->assertTrue(RiderResource::canDelete($ownRider));
        $this->assertFalse(RiderResource::canEdit($otherRider));
        $this->assertFalse(RiderResource::canDelete($otherRider));
        $this->assertFalse(ProductResource::canViewAny());
        $this->assertFalse(ArticulosResource::canViewAny());
    }

    public function test_advisor_dashboard_totals_are_limited_to_their_created_riders(): void
    {
        $advisor = User::query()->create([
            'name' => 'Asesor Dashboard',
            'email' => 'asesor-dashboard@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADVISOR,
        ]);

        $otherAdvisor = User::query()->create([
            'name' => 'Asesor Otro Dashboard',
            'email' => 'asesor-other-dashboard@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADVISOR,
        ]);

        $ownRider = Rider::query()->create([
            'rider_id' => 'ADVDASH001',
            'name' => 'Rider Dashboard Propio',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
            'created_by' => $advisor->getKey(),
            'creation_source' => 'manual',
        ]);

        $otherRider = Rider::query()->create([
            'rider_id' => 'ADVDASH002',
            'name' => 'Rider Dashboard Ajeno',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
            'created_by' => $otherAdvisor->getKey(),
            'creation_source' => 'manual',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $ownRider->getKey(),
            'branch' => 'SANTA CRUZ',
            'movement_type' => 'purchase',
            'points' => 100,
            'occurred_at' => now(),
        ]);

        RiderMovement::query()->create([
            'rider_id' => $otherRider->getKey(),
            'branch' => 'SANTA CRUZ',
            'movement_type' => 'purchase',
            'points' => 900,
            'occurred_at' => now(),
        ]);

        $this->actingAs($advisor);

        $page = app(Dashboard::class);
        $method = new ReflectionMethod($page, 'movementQuery');
        $method->setAccessible(true);

        $this->assertSame(100, (int) $method->invoke($page)->sum('points'));
    }

    public function test_rider_export_query_can_filter_by_creator_role(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Exporta',
            'email' => 'admin-export@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $advisor = User::query()->create([
            'name' => 'Asesor Exporta',
            'email' => 'advisor-export@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADVISOR,
        ]);

        $adminRider = Rider::query()->create([
            'rider_id' => 'EXPORTADMIN001',
            'name' => 'Rider Admin Export',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
            'created_by' => $admin->getKey(),
            'creation_source' => 'manual',
        ]);

        $advisorRider = Rider::query()->create([
            'rider_id' => 'EXPORTADVISOR001',
            'name' => 'Rider Asesor Export',
            'branch' => 'SANTA CRUZ',
            'rango' => 'ORO',
            'created_by' => $advisor->getKey(),
            'creation_source' => 'manual',
        ]);

        $this->actingAs($admin);

        $page = app(ListRiders::class);
        $method = new ReflectionMethod($page, 'exportRidersQuery');
        $method->setAccessible(true);

        $allExportIds = $method->invoke($page)->pluck('rider_id')->all();
        $advisorExportIds = $method->invoke($page, User::ROLE_ADVISOR)->pluck('rider_id')->all();

        $this->assertContains($adminRider->rider_id, $allExportIds);
        $this->assertContains($advisorRider->rider_id, $allExportIds);
        $this->assertNotContains($adminRider->rider_id, $advisorExportIds);
        $this->assertContains($advisorRider->rider_id, $advisorExportIds);
    }
}
