<?php

namespace App\Imports;

use App\Models\Participant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ParticipantImport implements ToCollection, WithHeadingRow
{
    protected $eventId;
    protected $idNumberColumn = null;
    protected $nameColumn = null;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
        // 設置標題格式化器為 none，保留原始中文標題
        HeadingRowFormatter::default(HeadingRowFormatter::FORMATTER_NONE);
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        if ($collection->isEmpty()) {
            throw new \Exception('Excel 檔案為空或格式不正確');
        }

        // 找出身份證和姓名欄位（從第一行資料判斷）
        $firstRow = $collection->first();
        if ($firstRow) {
            foreach ($firstRow->keys() as $key) {
                $normalizedKey = strtolower(trim($key));
                // 檢查是否為身份證欄位
                if (in_array($normalizedKey, ['身份證', 'id', 'id_number', 'idnumber', '證號', '身分證', '身分證號'])) {
                    $this->idNumberColumn = $key;
                }
                // 檢查是否為姓名欄位
                if (in_array($normalizedKey, ['姓名', 'name', '姓名', '名字', '名稱'])) {
                    $this->nameColumn = $key;
                }
            }
        }

        if (!$this->idNumberColumn) {
            throw new \Exception('無法識別身份證欄位，請確認 Excel 包含「身份證」、「ID」或「id_number」欄位');
        }

        if (!$this->nameColumn) {
            throw new \Exception('無法識別姓名欄位，請確認 Excel 包含「姓名」或「name」欄位');
        }

        // 處理每一行資料
        foreach ($collection as $row) {
            $idNumber = trim((string) $row[$this->idNumberColumn] ?? '');
            $name = trim((string) $row[$this->nameColumn] ?? '');

            if (empty($idNumber)) {
                continue; // 跳過沒有身份證的資料
            }

            // 建立 extra_data，包含所有欄位（除了已使用的）
            $extraData = [];
            foreach ($row as $column => $value) {
                if ($column !== $this->idNumberColumn && $column !== $this->nameColumn) {
                    $extraData[$column] = $value;
                }
            }

            // 檢查是否已存在
            $participant = Participant::where('event_id', $this->eventId)
                ->where('id_number', $idNumber)
                ->first();

            if (!$participant) {
                Participant::create([
                    'event_id' => $this->eventId,
                    'id_number' => $idNumber,
                    'name' => $name ?: '未提供',
                    'extra_data' => $extraData,
                ]);
            }
        }
    }
}
