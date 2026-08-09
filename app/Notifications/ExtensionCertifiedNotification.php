<?php

namespace App\Notifications;

use App\Models\Extension;
use Illuminate\Notifications\Notification;

/**
 * In-app (database channel only) notification for a marketplace listing's
 * certification status changing. Fired automatically for every member of
 * the extension's developer team by App\Models\Extension::booted(), which
 * fires only when `status` transitions into "certified" via an update —
 * never on initial creation. Certification itself remains a manual,
 * hand-set flag (wiki.md §3, §7 roadmap) — only this notification is
 * automatic; nothing here changes how or when an extension gets certified.
 */
class ExtensionCertifiedNotification extends Notification
{
    public function __construct(public Extension $extension) {}

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
