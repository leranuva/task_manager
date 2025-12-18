<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;
    protected User $teamOwner;
    protected User $teamMember;
    protected Team $team;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuarios de prueba
        $this->superAdmin = User::factory()->create(['is_super_admin' => true]);
        $this->regularUser = User::factory()->create(['is_super_admin' => false]);
        $this->teamOwner = User::factory()->create(['is_super_admin' => false]);
        $this->teamMember = User::factory()->create(['is_super_admin' => false]);

        // Crear equipo
        $this->team = Team::factory()->create(['owner_id' => $this->teamOwner->id]);

        // Agregar miembro al equipo
        $this->team->users()->attach($this->teamMember->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        // Crear proyecto
        $this->project = Project::factory()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->teamOwner->id,
        ]);
    }

    /** @test */
    public function super_admin_can_view_any_users()
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue($this->superAdmin->can('viewAny', User::class));
    }

    /** @test */
    public function regular_user_cannot_view_any_users()
    {
        $this->actingAs($this->regularUser);
        $this->assertFalse($this->regularUser->can('viewAny', User::class));
    }

    /** @test */
    public function super_admin_can_create_users()
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue($this->superAdmin->can('create', User::class));
    }

    /** @test */
    public function regular_user_cannot_create_users()
    {
        $this->actingAs($this->regularUser);
        $this->assertFalse($this->regularUser->can('create', User::class));
    }

    /** @test */
    public function super_admin_cannot_delete_self()
    {
        $this->actingAs($this->superAdmin);
        $this->assertFalse($this->superAdmin->can('delete', $this->superAdmin));
    }

    /** @test */
    public function super_admin_can_delete_other_users()
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue($this->superAdmin->can('delete', $this->regularUser));
    }

    /** @test */
    public function team_owner_can_view_team()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('view', $this->team));
    }

    /** @test */
    public function team_member_can_view_team()
    {
        $this->actingAs($this->teamMember);
        $this->assertTrue($this->teamMember->can('view', $this->team));
    }

    /** @test */
    public function regular_user_cannot_view_team()
    {
        $this->actingAs($this->regularUser);
        $this->assertFalse($this->regularUser->can('view', $this->team));
    }

    /** @test */
    public function team_owner_can_update_team()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('update', $this->team));
    }

    /** @test */
    public function team_member_cannot_update_team()
    {
        $this->actingAs($this->teamMember);
        $this->assertFalse($this->teamMember->can('update', $this->team));
    }

    /** @test */
    public function team_owner_can_delete_team()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('delete', $this->team));
    }

    /** @test */
    public function team_member_cannot_delete_team()
    {
        $this->actingAs($this->teamMember);
        $this->assertFalse($this->teamMember->can('delete', $this->team));
    }

    /** @test */
    public function team_owner_can_manage_members()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('manageMembers', $this->team));
    }

    /** @test */
    public function team_member_cannot_manage_members()
    {
        $this->actingAs($this->teamMember);
        $this->assertFalse($this->teamMember->can('manageMembers', $this->team));
    }

    /** @test */
    public function project_owner_can_view_project()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('view', $this->project));
    }

    /** @test */
    public function team_member_can_view_project()
    {
        $this->actingAs($this->teamMember);
        $this->assertTrue($this->teamMember->can('view', $this->project));
    }

    /** @test */
    public function regular_user_cannot_view_project()
    {
        $this->actingAs($this->regularUser);
        $this->assertFalse($this->regularUser->can('view', $this->project));
    }

    /** @test */
    public function project_owner_can_update_project()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('update', $this->project));
    }

    /** @test */
    public function project_owner_can_delete_project()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('delete', $this->project));
    }

    /** @test */
    public function team_member_cannot_delete_project()
    {
        $this->actingAs($this->teamMember);
        $this->assertFalse($this->teamMember->can('delete', $this->project));
    }

    /** @test */
    public function super_admin_can_do_everything()
    {
        $this->actingAs($this->superAdmin);
        
        $this->assertTrue($this->superAdmin->can('viewAny', User::class));
        $this->assertTrue($this->superAdmin->can('view', $this->team));
        $this->assertTrue($this->superAdmin->can('view', $this->project));
        $this->assertTrue($this->superAdmin->can('update', $this->team));
        $this->assertTrue($this->superAdmin->can('delete', $this->team));
        $this->assertTrue($this->superAdmin->can('manageMembers', $this->team));
    }

    /** @test */
    public function team_owner_can_transfer_ownership()
    {
        $this->actingAs($this->teamOwner);
        $this->assertTrue($this->teamOwner->can('transferOwnership', $this->team));
    }

    /** @test */
    public function team_member_cannot_transfer_ownership()
    {
        $this->actingAs($this->teamMember);
        $this->assertFalse($this->teamMember->can('transferOwnership', $this->team));
    }
}
