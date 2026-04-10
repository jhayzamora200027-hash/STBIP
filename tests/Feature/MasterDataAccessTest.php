<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\RegionItem;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterDataAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('region_item_histories');
        Schema::dropIfExists('social_technology_titles');
        Schema::dropIfExists('selectdocslogs');
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
            $table->string('inactive_status')->nullable();
            $table->text('inactive_remarks')->nullable();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });

        Schema::create('region_item_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_item_id')->nullable()->constrained('region_items')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('region_name')->nullable();
            $table->string('st_title');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('action');
            $table->text('update_row')->nullable();
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

        Schema::create('selectdocslogs', function (Blueprint $table) {
            $table->id();
            $table->string('updatedby')->nullable();
            $table->string('actionlogs')->nullable();
            $table->string('docselected')->nullable();
            $table->timestamps();
        });

        Schema::create('social_technology_titles', function (Blueprint $table) {
            $table->id();
            $table->string('social_technology')->unique();
            $table->timestamps();
        });

        DB::table('social_technology_titles')->insert([
            ['social_technology' => 'Item USER', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'Item ADMIN', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'Item SYSADMIN', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'Updated USER', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'Updated ADMIN', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'Updated SYSADMIN', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'History Item', 'created_at' => now(), 'updated_at' => now()],
            ['social_technology' => 'History Item Updated', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('region_item_histories');
        Schema::dropIfExists('social_technology_titles');
        Schema::dropIfExists('selectdocslogs');
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
                'status' => 'inactive',
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
            $this->assertDatabaseHas('region_item_histories', [
                'region_item_id' => $item->id,
                'updated_by' => $user->name,
                'action' => 'update',
            ]);
        }
    }

    public function test_history_logs_are_visible_only_to_admin_and_sysadmin(): void
    {
        $region = Region::query()->create(['name' => 'History Region']);

        $adminResponse = $this->actingAs($this->makeUser('admin'))->get(route('masterdata.index', ['tab' => 'updates', 'region_filter' => $region->name]));
        $adminResponse->assertOk()->assertSee('Open History Logs');

        $userResponse = $this->actingAs($this->makeUser('user'))->get(route('masterdata.index', ['tab' => 'updates', 'region_filter' => $region->name]));
        $userResponse->assertOk()->assertDontSee('Open History Logs');
    }

    public function test_region_item_history_logs_capture_add_and_update_details(): void
    {
        $region = Region::query()->create(['name' => 'History Region 2']);
        $admin = $this->makeUser('admin');

        $createResponse = $this->actingAs($admin)->post(route('masterdata.region-items.store'), [
            'region_id' => $region->id,
            'title' => 'History Item',
            'province' => 'Province A',
            'municipality' => 'City A',
            'adoption_status' => 'none',
            'with_expr' => '0',
            'with_moa' => '0',
            'with_res' => '0',
            'included_aip' => '0',
            'status' => 'ongoing',
        ]);

        $createResponse->assertRedirect();

        $item = RegionItem::query()->where('title', 'History Item')->firstOrFail();

        $this->assertDatabaseHas('region_item_histories', [
            'region_item_id' => $item->id,
            'region_name' => $region->name,
            'st_title' => 'History Item',
            'province' => 'Province A',
            'city' => 'City A',
            'updated_by' => $admin->name,
            'action' => 'add',
        ]);

        $updateResponse = $this->actingAs($admin)->patch(route('masterdata.region-items.update', $item), [
            'region_id' => $region->id,
            'title' => 'History Item Updated',
            'province' => 'Province B',
            'municipality' => 'City B',
            'adoption_status' => 'replicated',
            'with_expr' => '1',
            'with_moa' => '1',
            'year_of_moa' => '2024',
            'with_res' => '0',
            'included_aip' => '1',
            'status' => 'inactive',
            'inactive_status' => 'pending_document',
            'inactive_remarks' => 'Needs final signed file.',
        ]);

        $updateResponse->assertRedirect();

        $this->assertDatabaseHas('region_item_histories', [
            'region_item_id' => $item->id,
            'st_title' => 'History Item Updated',
            'province' => 'Province B',
            'city' => 'City B',
            'updated_by' => $admin->name,
            'action' => 'update',
        ]);

        $this->assertDatabaseCount('region_item_histories', 2);

        $updateRow = DB::table('region_item_histories')
            ->where('region_item_id', $item->id)
            ->where('action', 'update')
            ->value('update_row');

        $this->assertStringContainsString('Province: Province A -> Province B', (string) $updateRow);
        $this->assertStringContainsString('City / Municipality: City A -> City B', (string) $updateRow);
        $this->assertStringContainsString('ST Title: History Item -> History Item Updated', (string) $updateRow);
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
                $this->assertDatabaseHas('region_item_histories', [
                    'st_title' => 'Delete Me ' . strtoupper($role),
                    'updated_by' => strtoupper($role) . ' Tester',
                    'action' => 'delete',
                ]);
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