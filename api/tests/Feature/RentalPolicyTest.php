<?php

use App\Filament\Resources\RentalPolicyResource\Pages\CreateRentalPolicy;
use App\Models\RentalPolicy;
use App\Models\User;
use Database\Seeders\RentalPolicySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('returns the default rental policy', function () {
    $this->seed(RentalPolicySeeder::class);
    $policy = RentalPolicy::default();
    expect($policy)->not->toBeNull();
    expect($policy->base_days)->toBe(1);
    expect($policy->deposit_mode)->toBe('charge');
});

it('keeps a single default policy', function () {
    $first = RentalPolicy::factory()->asDefault()->create();
    $second = RentalPolicy::factory()->asDefault()->create();

    expect($first->fresh()->is_default)->toBeFalse();
    expect(RentalPolicy::default()->is($second))->toBeTrue();
});

it('does not overwrite an existing default when re-seeded', function () {
    $this->seed(RentalPolicySeeder::class);
    RentalPolicy::default()->update(['base_fee_baisa' => 7000]);

    $this->seed(RentalPolicySeeder::class);

    expect(RentalPolicy::count())->toBe(1);
    expect(RentalPolicy::default()->base_fee_baisa)->toBe(7000);
});

it('lets an admin manage rental policies with every field', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $policy = RentalPolicy::factory()->create(['name' => 'Wedding khanjar']);

    $this->actingAs($admin)->get('/admin/rental-policies')->assertOk()->assertSee('Wedding khanjar');
    $this->actingAs($admin)->get('/admin/rental-policies/create')->assertOk();
    $this->actingAs($admin)->get("/admin/rental-policies/{$policy->id}/edit")->assertOk();

    Livewire::actingAs($admin)
        ->test(CreateRentalPolicy::class)
        ->fillForm([
            'name' => 'Eid',
            'base_days' => 2,
            'base_fee_baisa' => 8000,
            'extra_daily_baisa' => 2500,
            'weekly_fee_baisa' => 15000,
            'deposit_baisa' => 30000,
            'deposit_mode' => 'bank_transfer',
            'return_day_inclusive' => true,
            'buffer_days' => 2,
            'min_days' => 2,
            'max_days' => 14,
            'late_fee_per_day_baisa' => 4000,
            'damage_fee_notes' => 'Silver repairs charged at cost.',
            'require_id' => true,
            'require_contract' => true,
            'inspection_days' => 3,
            'is_default' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = RentalPolicy::firstWhere('name', 'Eid');
    expect($created->only(['base_days', 'weekly_fee_baisa', 'deposit_mode', 'return_day_inclusive', 'require_contract', 'inspection_days', 'is_default']))
        ->toBe([
            'base_days' => 2,
            'weekly_fee_baisa' => 15000,
            'deposit_mode' => 'bank_transfer',
            'return_day_inclusive' => true,
            'require_contract' => true,
            'inspection_days' => 3,
            'is_default' => true,
        ]);
    expect($created->damage_fee_notes)->toBe('Silver repairs charged at cost.');
});
