<?php

namespace App\Notifications;

use App\Models\Extension;
use Illuminate\Notifications\Notification;

/**
 * In-app (database channel only) notification for a marketplace listing's
 * certification status changing. Not yet wired to any automatic trigger —
 * there is no certification pipeline yet (see wiki.md §3 roadmap); dispatch
 * manually via `$user->notify(new ExtensionCertifiedNotification($extension))`
 * until one exists.
 */
class ExtensionCertifiedNotification extends Notification
{
    public function __construct(public Extension $extension)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'extension_certified',
            'title' => 'Extension certified',
            'message' => "{$this->extension->name} has been certified and is now listed in the marketplace.",
            'extension_id' => $this->extension->id,
            'url' => route('extensions.show', $this->extension),
        ];
    }
}
