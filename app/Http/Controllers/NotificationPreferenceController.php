<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationPreferenceController extends Controller
{
    /**
     * Mostrar preferencias de notificaciones
     */
    public function index(Request $request)
    {
        $preferences = NotificationPreference::where('user_id', $request->user()->id)
            ->get()
            ->keyBy('type');

        // Tipos de notificación disponibles
        $types = [
            NotificationPreference::TYPE_TASK_CREATED => 'Tarea creada',
            NotificationPreference::TYPE_TASK_UPDATED => 'Tarea actualizada',
            NotificationPreference::TYPE_TASK_DELETED => 'Tarea eliminada',
            NotificationPreference::TYPE_TASK_ASSIGNED => 'Tarea asignada',
            NotificationPreference::TYPE_TASK_MOVED => 'Tarea movida',
            NotificationPreference::TYPE_COMMENT_CREATED => 'Comentario creado',
            NotificationPreference::TYPE_COMMENT_MENTIONED => 'Mencionado en comentario',
            NotificationPreference::TYPE_PROJECT_UPDATED => 'Proyecto actualizado',
            NotificationPreference::TYPE_PROJECT_MEMBER_ADDED => 'Agregado a proyecto',
        ];

        return Inertia::render('Notifications/Preferences', [
            'preferences' => $preferences,
            'types' => $types,
        ]);
    }

    /**
     * Actualizar preferencias
     */
    public function update(Request $request)
    {
        $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.type' => ['required', 'string'],
            'preferences.*.channel' => ['required', 'string', 'in:in_app,email,both,none'],
            'preferences.*.enabled' => ['boolean'],
        ]);

        foreach ($request->preferences as $prefData) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'type' => $prefData['type'],
                ],
                [
                    'channel' => $prefData['channel'],
                    'enabled' => $prefData['enabled'] ?? true,
                ]
            );
        }

        return redirect()->back()->with('message', 'Preferencias actualizadas exitosamente.');
    }

    /**
     * Actualizar una preferencia específica
     */
    public function updateSingle(Request $request, string $type)
    {
        $request->validate([
            'channel' => ['required', 'string', 'in:in_app,email,both,none'],
            'enabled' => ['boolean'],
        ]);

        NotificationPreference::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'type' => $type,
            ],
            [
                'channel' => $request->channel,
                'enabled' => $request->input('enabled', true),
            ]
        );

        return response()->json(['success' => true]);
    }
}
