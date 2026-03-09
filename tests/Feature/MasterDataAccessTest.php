<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\RegionItem;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDataAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('stsattachment');
        Schema::dropIfExists('region_items');
        Schema::dropIfExists('regions');

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });

        Schema::create('region_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('title');
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->boolean('with_expr')->default(false);
            $table->boolean('with_moa')->default(false);
            $table->integer('year_of_moa')->nullable();
            $table->boolean('with_res')->default(false);
            $table->integer('year_of_resolution')->nullable();
            $table->boolean('included_aip')->default(false);
            $table->boolean('with_adopted')->default(false);
            $table->boolean('with_replicated')->default(false);
            $table->string('status')->nullable();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });

        Schema::create('stsattachment', function (Blueprint $table) {
            $table->id();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('title')->nullable();
            $table->string('year_of_moa')->nullable();
            $table->string('action')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('stsattachment');
        Schema::dropIfExists('region_items');
        Schema::dropIfExists('regions');

        parent::tearDown();
    }

    public function test_write_roles_can_create_and_update_region_items(): void
    {
        foreach (['user', 'admin', 'sysadmin'] as $role) {
            $region = Region::query()->create(['name' => 'Region ' . strtoupper($role)]);
            $user = $this->makeUser($role);

            $createResponse = $this->actingAs($user)->post(route('masterdata.region-items.store'), [
                'region_id' => $region->id,
                'title' => 'Item ' . strtoupper($role),
                'province' => 'Province A',
                'municipality' => 'Municipality A',
                'adoption_status' => 'none',
                'with_expr' => '0',
                'with_moa' => '0',
                'with_res' => '0',
                'included_aip' => '0',
                'status' => 'ongoing',
            ]);

            $createResponse->assertRedirect();

            $item = RegionItem::query()->where('title', 'Item ' . strtoupper($role))->firstOrFail();

            $this->assertSame($user->name, $item->createdby);
            $this->assertSame($user->name, $item->updatedby);

            $updateResponse = $this->actingAs($user)->patch(route('masterdata.region-items.update', $item), [
                'region_id' => $region->id,
                'title' => 'Updated ' . strtoupper($role),
                'province' => 'Province B',
                'municipality' => 'Municipality B',
                'adoption_status' => 'adopted',
                'with_expr' => '1',
                'with_moa' => '1',
                'year_of_moa' => '2024',
                'with_res' => '1',
                'year_of_resolution' => '2025',
                'included_aip' => '1',
                'status' => 'dissolved',
            ]);

            $updateResponse->assertRedirect();

            $item->refresh();

            $this->assertSame('Updated ' . strtoupper($role), $item->title);
            $this->assertSame('Province B', $item->province);
            $this->assertSame('Municipality B', $item->municipality);
            $this->assertTrue($item->with_expr);
            $this->assertTrue($item->with_moa);
            $this->assertSame(2024, $item->year_of_moa);
            $this->assertTrue($item->with_res);
            $this->assertSame(2025, $item->year_of_resolution);
            $this->assertTrue($item->included_aip);
            $this->assertTrue($item->with_adopted);
            $this->assertFalse($item->with_replicated);
            $this->assertSame('dissolved', $item->status);
            $this->assertSame($user->name, $item->updatedby);
        }
    }

    public function test_only_admin_and_sysadmin_can_delete_region_items(): void
    {
        foreach ([
            'user' => false,
            'admin' => true,
            'sysadmin' => true,
        ] as $role => $canDelete) {
            $region = Region::query()->create(['name' => 'Delete Region ' . strtoupper($role)]);
            $item = RegionItem::query()->create([
                'region_id' => $region->id,
                'title' => 'Delete Me ' . strtoupper($role),
                'createdby' => 'Seeder',
                'updatedby' => 'Seeder',
            ]);

            $response = $this->actingAs($this->makeUser($role))->delete(route('masterdata.region-items.destroy', $item));

            $response->assertRedirect();

            if ($canDelete) {
                $this->assertDatabaseMissing('region_items', ['id' => $item->id]);
                continue;
            }

            $this->assertDatabaseHas('region_items', ['id' => $item->id]);
        }
    }

    private function makeUser(string $role): User
    {
        return User::factory()->make([
            'name' => strtoupper($role) . ' Tester',
            'email' => $role . '@example.com',
            'usergroup' => $role,
            'user_id' => strtoupper($role) . '-001',
        ]);
    }
}