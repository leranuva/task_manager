<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteTeamMemberRequest;
use App\Http\Requests\InviteProjectMemberRequest;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class InvitationController extends Controller
{
    // Invitar a equipo
    public function inviteToTeam(InviteTeamMemberRequest $request, Team $team)
    {
        $user = $request->user();
        
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // Si el usuario ya es miembro, no enviar invitación
            if ($team->hasMember($existingUser)) {
                return back()->withErrors(['email' => 'Este usuario ya es miembro del equipo.']);
            }
        }

        // Verificar si ya hay una invitación pendiente
        $existingInvitation = Invitation::where('email', $request->email)
            ->where('invitable_type', Team::class)
            ->where('invitable_id', $team->id)
            ->pending()
            ->first();

        if ($existingInvitation) {
            return back()->withErrors(['email' => 'Ya existe una invitación pendiente para este correo.']);
        }

        $invitation = Invitation::create([
            'email' => $request->email,
            'invitable_type' => Team::class,
            'invitable_id' => $team->id,
            'role' => $request->role,
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        // TODO: Enviar email de invitación
        // Mail::to($request->email)->send(new TeamInvitationMail($invitation));

        return back()->with('message', 'Invitación enviada exitosamente.');
    }

    // Invitar a proyecto
    public function inviteToProject(InviteProjectMemberRequest $request, Project $project)
    {
        $user = $request->user();
        
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // Si el usuario ya es miembro, no enviar invitación
            if ($project->hasMember($existingUser)) {
                return back()->withErrors(['email' => 'Este usuario ya es miembro del proyecto.']);
            }
        }

        // Verificar si ya hay una invitación pendiente
        $existingInvitation = Invitation::where('email', $request->email)
            ->where('invitable_type', Project::class)
            ->where('invitable_id', $project->id)
            ->pending()
            ->first();

        if ($existingInvitation) {
            return back()->withErrors(['email' => 'Ya existe una invitación pendiente para este correo.']);
        }

        $invitation = Invitation::create([
            'email' => $request->email,
            'invitable_type' => Project::class,
            'invitable_id' => $project->id,
            'role' => $request->role,
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        // TODO: Enviar email de invitación
        // Mail::to($request->email)->send(new ProjectInvitationMail($invitation));

        return back()->with('message', 'Invitación enviada exitosamente.');
    }

    // Aceptar invitación
    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->pending()
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('dashboard')
                ->with('error', 'La invitación ha expirado.');
        }

        // Buscar o crear usuario
        $user = User::where('email', $invitation->email)->first();
        
        if (!$user) {
            // Si el usuario no existe, redirigir a registro
            return redirect()->route('register', ['invitation' => $token])
                ->with('message', 'Por favor, crea una cuenta para aceptar la invitación.');
        }

        // Verificar autenticación
        if (!$request->user() || $request->user()->id !== $user->id) {
            return redirect()->route('login')
                ->with('message', 'Por favor, inicia sesión para aceptar la invitación.');
        }

        // Aceptar invitación
        $invitation->accept();

        // Agregar usuario al recurso
        if ($invitation->invitable_type === Team::class) {
            $team = Team::findOrFail($invitation->invitable_id);
            $team->users()->syncWithoutDetaching([
                $user->id => [
                    'role' => $invitation->role,
                    'joined_at' => now(),
                ]
            ]);
            
            return redirect()->route('teams.show', $team)
                ->with('message', 'Te has unido al equipo exitosamente.');
        } elseif ($invitation->invitable_type === Project::class) {
            $project = Project::findOrFail($invitation->invitable_id);
            $project->users()->syncWithoutDetaching([
                $user->id => [
                    'role' => $invitation->role,
                    'joined_at' => now(),
                ]
            ]);
            
            return redirect()->route('projects.show', $project)
                ->with('message', 'Te has unido al proyecto exitosamente.');
        }

        return redirect()->route('dashboard')
            ->with('error', 'Tipo de invitación no válido.');
    }

    // Rechazar invitación
    public function reject(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->pending()
            ->firstOrFail();

        $invitation->delete();

        return redirect()->route('dashboard')
            ->with('message', 'Invitación rechazada.');
    }

    // Cancelar invitación
    public function cancel(Request $request, Invitation $invitation)
    {
        // Verificar que el usuario que cancela sea quien invitó o tenga permisos
        if ($invitation->invited_by !== $request->user()->id) {
            abort(403, 'No tienes permiso para cancelar esta invitación.');
        }

        $invitation->delete();

        return back()->with('message', 'Invitación cancelada exitosamente.');
    }
}
