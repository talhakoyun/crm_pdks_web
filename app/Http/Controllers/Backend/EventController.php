<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\EventRequest;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\Request;

class EventController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Etkinlikler';
        $this->page = 'event';
        $this->upload = 'event';
        $this->model = new Event();
        $this->listQuery = Event::withCount(['approvedParticipants as approved_count', 'pendingParticipants as pending_count']);
        $this->request = new EventRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Zimmetler' => route('backend.debit_device_list'),
            ),
        );
        parent::__construct();
    }

    public function participants($eventId)
    {
        $event = Event::with(['participants.user'])->findOrFail($eventId);
        return view('backend.event.participants', compact('event'));
    }

    public function participantStatus(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:event_participants,id',
            'status' => 'required|in:approved,rejected'
        ]);

        $participant = EventParticipant::findOrFail($request->participant_id);
        $event = $participant->event;

        if ($request->status === 'approved' && $event->approvedParticipants()->count() >= $event->quota) {
            return response()->json(['success' => false, 'message' => 'Etkinlik kontenjanı dolu!'], 422);
        }

        $participant->status = $request->status;
        $participant->save();

        return response()->json(['success' => true]);
    }

    public function participantBulkStatus(Request $request)
    {
        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:event_participants,id',
            'status' => 'required|in:approved,rejected'
        ]);

        $participants = EventParticipant::whereIn('id', $request->participant_ids)->get();
        $event = $participants->first()->event;

        if ($request->status === 'approved') {
            $currentApproved = $event->approvedParticipants()->count();
            $toBeApproved = count($request->participant_ids);

            if (($currentApproved + $toBeApproved) > $event->quota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seçilen katılımcı sayısı kontenjanı aşıyor! Mevcut onaylı: ' . $currentApproved . ', Kontenjan: ' . $event->quota
                ], 422);
            }
        }

        foreach ($participants as $participant) {
            $participant->status = $request->status;
            $participant->save();
        }

        return response()->json(['success' => true]);
    }

}
