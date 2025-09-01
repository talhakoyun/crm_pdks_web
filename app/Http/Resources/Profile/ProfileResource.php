<?php

namespace App\Http\Resources\Profile;

use App\Http\Resources\Company\CompanyResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProfileResource extends JsonResource
{
    private ?array $tokenData = null;

    public function __construct($resource, ?array $tokenData = null)
    {
        parent::__construct($resource);
        $this->tokenData = $tokenData;
    }

    /**
     * Veritabanından dakika (int) olarak gelen değeri HH:MM formatına çevirir
     */
    private function formatMinutesToHoursMinutes(null|int|string $minutes): string
    {
        if ($minutes === null || $minutes === '') {
            return '00:00';
        }
        if (!is_numeric($minutes)) {
            return '00:00';
        }
        $totalMinutes = (int) $minutes;
        if ($totalMinutes < 0) {
            $totalMinutes = 0;
        }
        $hours = intdiv($totalMinutes, 60);
        $remainingMinutes = $totalMinutes % 60;
        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shiftStartTimeRaw = $this->userShifts?->shiftDefinition?->start_time ?? '';
        $shiftEndTimeRaw = $this->userShifts?->shiftDefinition?->end_time ?? '';

        $inTolerance = Setting::where('company_id', $this->company_id)
            ->where('branch_id', $this->branch_id)
            ->where('key', 'in_tolerance')
            ->first();
        $outTolerance = Setting::where('company_id', $this->company_id)
            ->where('branch_id', $this->branch_id)
            ->where('key', 'out_tolerance')
            ->first();
        $shiftStartToleranceMinutes = $this->formatMinutesToHoursMinutes($inTolerance?->value);
        $shiftEndToleranceMinutes = $this->formatMinutesToHoursMinutes($outTolerance?->value);

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender == 1 ? 'Erkek' : 'Kadın',
            'role' => $this->role,
            'company' => new CompanyResource($this->company),
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->title,
            ],
            'settings' => [
                'outside' => boolval($this->allow_outside),
            ],
            'shift' => [
                'start' => Carbon::parse($shiftStartTimeRaw)->format('H:i'),
                'end' => Carbon::parse($shiftEndTimeRaw)->format('H:i'),
                'tolerance' => [
                    'start' => $shiftStartToleranceMinutes,
                    'end' => $shiftEndToleranceMinutes,
                ],
            ],
            'birthday' => $this->birthday,
            'created_at' => $this->created_at,
        ];

                if ($this->tokenData !== null) {
            // Token alanlarını en üst seviyeye taşı
            $tokenInfo = $this->tokenData;

            // Eğer refresh_expires_in bilgisi yoksa, refresh_ttl'den hesapla
            if (!isset($tokenInfo['refresh_expires_in'])) {
                $tokenInfo['refresh_expires_in'] = config('jwt.refresh_ttl') * 60;
            }

            $data = array_merge($data, $tokenInfo);
        }

        return $data;
    }
}
