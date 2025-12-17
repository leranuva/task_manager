<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Enviar notificación a usuarios
     */
    public function notify(array $users, $notification, array $channels = ['database', 'mail']): void
    {
        foreach ($users as $user) {
            if (!$user instanceof User) {
                $user = User::find($user);
            }

            if (!$user) {
                continue;
            }

            // Verificar preferencias del usuario
            $preference = $this->getPreference($user, $notification->getType());
            
            if (!$preference || !$preference->enabled) {
                continue;
            }

            // Determinar canales según preferencias
            $userChannels = $this->getChannelsForUser($preference, $channels);

            // Enviar notificación
            foreach ($userChannels as $channel) {
                try {
                    if ($channel === 'database') {
                        $user->notify($notification);
                    } elseif ($channel === 'mail' && in_array('mail', $channels)) {
                        $user->notify($notification);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending notification', [
                        'user_id' => $user->id,
                        'channel' => $channel,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Obtener preferencia de notificación
     */
    public function getPreference(User $user, string $type): ?NotificationPreference
    {
        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        // Si no existe preferencia, crear una por defecto (ambos canales habilitados)
        if (!$preference) {
            $preference = NotificationPreference::create([
                'user_id' => $user->id,
                'type' => $type,
                'channel' => NotificationPreference::CHANNEL_BOTH,
                'enabled' => true,
            ]);
        }

        return $preference;
    }

    /**
     * Obtener canales según preferencia
     */
    protected function getChannelsForUser(NotificationPreference $preference, array $availableChannels): array
    {
        $channels = [];

        if ($preference->channel === NotificationPreference::CHANNEL_BOTH) {
            $channels = $availableChannels;
        } elseif ($preference->channel === NotificationPreference::CHANNEL_IN_APP) {
            $channels = ['database'];
        } elseif ($preference->channel === NotificationPreference::CHANNEL_EMAIL) {
            $channels = ['mail'];
        } elseif ($preference->channel === NotificationPreference::CHANNEL_NONE) {
            $channels = [];
        }

        return array_intersect($channels, $availableChannels);
    }

    /**
     * Agrupar notificaciones inteligentemente
     */
    public function groupNotifications($notifications): array
    {
        $grouped = [];

        foreach ($notifications as $notification) {
            $key = $this->getGroupKey($notification);
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'type' => $notification->type,
                    'notifications' => [],
                    'count' => 0,
                    'latest' => null,
                    'users' => [],
                    'summary' => '',
                ];
            }

            $grouped[$key]['notifications'][] = $notification;
            $grouped[$key]['count']++;

            // Agregar usuario a la lista
            $data = $notification->data ?? [];
            $userName = $data['user_name'] ?? 'Usuario desconocido';
            if (!in_array($userName, $grouped[$key]['users'])) {
                $grouped[$key]['users'][] = $userName;
            }

            if (!$grouped[$key]['latest'] || $notification->created_at > $grouped[$key]['latest']->created_at) {
                $grouped[$key]['latest'] = $notification;
            }
        }

        // Generar resumen para cada grupo
        foreach ($grouped as &$group) {
            $group['summary'] = $this->generateSummary($group);
        }

        return array_values($grouped);
    }

    /**
     * Obtener clave de agrupación
     */
    protected function getGroupKey($notification): string
    {
        $data = $notification->data ?? [];
        
        // Agrupar por tipo y sujeto
        $type = $notification->type;
        $subjectType = $data['subject_type'] ?? null;
        $subjectId = $data['subject_id'] ?? null;
        $action = $data['action'] ?? 'unknown';
        
        // Para comentarios, agrupar por tarea/proyecto
        if (str_contains($type, 'CommentNotification')) {
            return "comment_{$subjectType}_{$subjectId}";
        }
        
        // Para tareas, agrupar por proyecto y tipo de acción
        if (str_contains($type, 'TaskNotification')) {
            $projectId = $data['project_id'] ?? null;
            return "task_{$action}_{$projectId}";
        }
        
        // Para proyectos, agrupar por proyecto
        if (str_contains($type, 'ProjectNotification')) {
            $projectId = $data['project_id'] ?? null;
            return "project_{$projectId}";
        }
        
        // Default: agrupar por tipo
        return $type;
    }

    /**
     * Generar resumen del grupo
     */
    protected function generateSummary(array $group): string
    {
        $count = $group['count'];
        $users = $group['users'];
        $type = $group['type'];

        if ($count === 1) {
            return $users[0] . ' ' . $this->getActionText($type);
        }

        if (count($users) === 1) {
            return $users[0] . " realizó {$count} acciones";
        }

        if (count($users) <= 3) {
            $usersText = implode(', ', array_slice($users, 0, -1)) . ' y ' . end($users);
            return "{$usersText} realizaron {$count} acciones";
        }

        return count($users) . " usuarios realizaron {$count} acciones";
    }

    /**
     * Obtener texto de acción
     */
    protected function getActionText(string $type): string
    {
        if (str_contains($type, 'TaskNotification')) {
            return 'actualizó una tarea';
        }
        if (str_contains($type, 'CommentNotification')) {
            return 'comentó';
        }
        if (str_contains($type, 'ProjectNotification')) {
            return 'actualizó el proyecto';
        }
        return 'realizó una acción';
    }

    /**
     * Marcar notificaciones como leídas
     */
    public function markAsRead(User $user, array $notificationIds = null): void
    {
        $query = $user->notifications();

        if ($notificationIds) {
            $query->whereIn('id', $notificationIds);
        }

        $query->update(['read_at' => now()]);
    }

    /**
     * Obtener notificaciones no leídas
     */
    public function getUnread(User $user, int $limit = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->latest()
            ->paginate($limit);
    }
}
