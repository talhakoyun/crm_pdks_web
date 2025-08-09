<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventService
{
    /**
     * Etkinlik katılım isteği oluşturur.
     *
     * @param Event $event
     * @return array
     */
    public function participate(Event $event): array
    {
        // Etkinlik aktif değilse veya tarihi geçmişse
        if ($event->status !== 'active' || $event->end_date < now()) {
            return [
                'success' => false,
                'message' => 'Bu etkinliğe katılım sağlanamaz.'
            ];
        }

        // Kullanıcı zaten katılım isteği göndermişse
        $existingParticipation = EventParticipant::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingParticipation) {
            return [
                'success' => false,
                'message' => 'Bu etkinlik için zaten katılım isteği gönderilmiş.'
            ];
        }

        // Kontenjan kontrolü
        if ($event->approvedParticipants()->count() >= $event->quota) {
            return [
                'success' => false,
                'message' => 'Etkinlik kontenjanı dolu!'
            ];
        }

        // Katılım isteği oluştur
        EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return [
            'success' => true,
            'message' => 'Katılım isteğiniz başarıyla gönderildi.'
        ];
    }
}
