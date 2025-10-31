<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\CheckIn;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckInController extends Controller
{
    public function scan($eventId)
    {
        $event = Event::findOrFail($eventId);
        return view('checkin.scan', compact('event'));
    }

    public function checkIn(Request $request, $eventId)
    {
        $request->validate([
            'id_number' => 'required|string',
        ]);

        $event = Event::findOrFail($eventId);
        $idNumber = trim($request->input('id_number'));

        // 尋找參與者
        $participant = Participant::where('event_id', $event->id)
            ->where('id_number', $idNumber)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => '身份證字號不存在於此活動的參與者名單中',
            ], 404);
        }

        // 檢查是否已簽到
        if ($participant->checkIn) {
            return response()->json([
                'success' => false,
                'message' => '此參與者已經簽到過了',
                'check_in_time' => $participant->checkIn->check_in_time->format('Y-m-d H:i:s'),
            ], 400);
        }

        // 建立簽到記錄
        try {
            DB::beginTransaction();
            
            CheckIn::create([
                'participant_id' => $participant->id,
                'check_in_time' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '簽到成功',
                'participant_name' => $participant->name,
                'check_in_time' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '簽到失敗：' . $e->getMessage(),
            ], 500);
        }
    }
}
