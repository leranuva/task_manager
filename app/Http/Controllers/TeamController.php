<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        $user = $request->user();
        
        $query = Team::with(['owner', 'users'])
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('users', function ($query) use ($user) {
                      $query->where('users.id', $user->id);
                  });
            });

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $teams = $query->latest()->paginate(15);

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Team::class);

        return Inertia::render('Teams/Create');
    }

    public function store(StoreTeamRequest $request)
    {
        $this->authorize('create', Team::class);

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'avatar' => $request->avatar,
            'owner_id' => $request->user()->id,
        ]);

        // El owner NO se agrega a team_user (es owner_id, no rol en pivot)
        // Solo se almacena en teams.owner_id

        return redirect()->route('teams.show', $team)
            ->with('message', 'Equipo creado exitosamente.');
    }

    public function show(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        $team->load([
            'owner',
            'users' => function ($query) {
                $query->withPivot('role', 'joined_at');
            },
            'projects' => function ($query) {
                $query->where('is_archived', false)
                      ->latest()
                      ->limit(10);
            },
            'invitations' => function ($query) {
                $query->pending()->with('invitedBy');
            },
        ]);

        return Inertia::render('Teams/Show', [
            'team' => $team,
            'canManageMembers' => $request->user()->can('manageMembers', $team),
        ]);
    }

    public function edit(Team $team)
    {
        $this->authorize('update', $team);

        return Inertia::render('Teams/Edit', [
            'team' => $team,
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $this->authorize('update', $team);

        $team->update($request->validated());

        return redirect()->route('teams.show', $team)
            ->with('message', 'Equipo actualizado exitosamente.');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();

        return redirect()->route('teams.index')
            ->with('message', 'Equipo eliminado exitosamente.');
    }

    // Gestión de miembros
    public function members(Team $team)
    {
        $this->authorize('view', $team);

        $members = $team->users()
            ->withPivot('role', 'joined_at')
            ->get()
            ->map(function ($user) use ($team) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'joined_at' => $user->pivot->joined_at,
                    'is_owner' => $team->owner_id === $user->id,
                ];
            });

        // Agregar el owner si no está en la lista
        if (!$members->contains('id', $team->owner_id)) {
            $owner = $team->owner;
            $members->prepend([
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => 'owner',
                'joined_at' => $team->created_at,
                'is_owner' => true,
            ]);
        }

        return response()->json($members->values());
    }

    public function addMember(Request $request, Team $team)
    {
        $this->authorize('manageMembers', $team);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        // ⚠️ IMPORTANTE: El owner NO se almacena en team_user
        // El owner se gestiona exclusivamente mediante teams.owner_id
        if ($request->user_id === $team->owner_id) {
            return back()->withErrors(['user_id' => 'El propietario del equipo no se agrega como miembro. El owner se gestiona mediante owner_id.']);
        }

        // Verificar que el usuario no esté ya en el equipo
        if ($team->users()->where('users.id', $request->user_id)->exists()) {
            return back()->withErrors(['user_id' => 'El usuario ya es miembro del equipo.']);
        }

        $team->users()->attach($request->user_id, [
            'role' => $request->role,
            'joined_at' => now(),
        ]);

        return back()->with('message', 'Miembro agregado exitosamente.');
    }

    public function updateMemberRole(Request $request, Team $team, User $user)
    {
        $this->authorize('manageMembers', $team);

        // No permitir cambiar el rol del owner
        if ($team->owner_id === $user->id) {
            return back()->withErrors(['role' => 'No se puede cambiar el rol del propietario del equipo.']);
        }

        $request->validate([
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        $team->users()->updateExistingPivot($user->id, [
            'role' => $request->role,
        ]);

        return back()->with('message', 'Rol actualizado exitosamente.');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('manageMembers', $team);

        // No permitir eliminar al owner
        if ($team->owner_id === $user->id) {
            return back()->withErrors(['user' => 'No se puede eliminar al propietario del equipo.']);
        }

        $team->users()->detach($user->id);

        return back()->with('message', 'Miembro eliminado exitosamente.');
    }
}
