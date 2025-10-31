<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Exports\CheckInExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export($id)
    {
        $event = Event::with(['participants.checkIn'])->findOrFail($id);
        
        $fileName = $event->name . '_簽到記錄_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new CheckInExport($event), $fileName);
    }
}
