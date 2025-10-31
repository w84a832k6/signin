<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantImport;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $event = Event::create([
            'name' => $validated['name'],
            'event_date' => $validated['event_date'],
        ]);

        // 儲存上傳的 Excel 檔案
        $filePath = $request->file('excel_file')->store('uploads', 'local');

        // 匯入參與者資料
        try {
            $import = new ParticipantImport($event->id);
            Excel::import($import, $filePath);
            // 匯入成功後刪除 Excel 檔案
            Storage::delete($filePath);
        } catch (\Exception $e) {
            $event->delete();
            Storage::delete($filePath);
            return back()->withErrors(['excel_file' => 'Excel 匯入失敗：' . $e->getMessage()])->withInput();
        }

        return redirect()->route('events.show', $event->id)
            ->with('success', '活動建立成功，參與者資料已匯入');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::with(['participants.checkIn'])->findOrFail($id);
        return view('events.show', compact('event'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', '活動已刪除');
    }
}
