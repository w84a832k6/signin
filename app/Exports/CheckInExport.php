<?php

namespace App\Exports;

use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CheckInExport implements FromCollection, WithHeadings, WithMapping
{
    protected $event;
    protected $headings = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->prepareHeadings();
    }

    protected function prepareHeadings()
    {
        $this->headings = ['身份證字號', '姓名'];
        
        // 從第一個參與者的 extra_data 取得所有欄位名稱
        $firstParticipant = $this->event->participants->first();
        if ($firstParticipant && $firstParticipant->extra_data) {
            foreach ($firstParticipant->extra_data as $key => $value) {
                if (!in_array($key, ['身份證', 'ID', 'id_number', 'idnumber', '證號', '身分證', '身分證號', '姓名', 'name', '姓名', '名字', '名稱'])) {
                    $this->headings[] = $key;
                }
            }
        }
        
        $this->headings[] = '簽到狀態';
        $this->headings[] = '簽到時間';
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function collection()
    {
        return $this->event->participants;
    }

    public function map($participant): array
    {
        $row = [
            $participant->id_number,
            $participant->name,
        ];

        // 加入 extra_data 的所有欄位值
        if ($participant->extra_data) {
            foreach ($participant->extra_data as $key => $value) {
                if (!in_array($key, ['身份證', 'ID', 'id_number', 'idnumber', '證號', '身分證', '身分證號', '姓名', 'name', '姓名', '名字', '名稱'])) {
                    $row[] = $value;
                }
            }
        }

        // 簽到狀態和時間
        if ($participant->checkIn) {
            $row[] = '已簽到';
            $row[] = $participant->checkIn->check_in_time->format('Y-m-d H:i:s');
        } else {
            $row[] = '未簽到';
            $row[] = '';
        }

        return $row;
    }
}
