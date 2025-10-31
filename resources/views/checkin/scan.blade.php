@extends('layouts.app')

@section('title', '條碼掃描簽到')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4>{{ $event->name }} - 條碼掃描簽到</h4>
                <p class="text-muted mb-0">活動日期：{{ $event->event_date->format('Y年m月d日') }}</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div id="reader" style="width: 100%; min-height: 300px;"></div>
                        <div class="text-center mt-3">
                            <button id="startScanBtn" class="btn btn-primary">開始掃描</button>
                            <button id="stopScanBtn" class="btn btn-danger" style="display: none;">停止掃描</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="result" class="alert" style="display: none;"></div>
                        <div class="mt-4">
                            <h5>手動輸入</h5>
                            <form id="manualCheckInForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="manual_id_number" class="form-label">身份證字號</label>
                                    <input type="text" class="form-control" id="manual_id_number" name="id_number" required>
                                </div>
                                <button type="submit" class="btn btn-primary">手動簽到</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #reader {
        border: 2px solid #ddd;
        border-radius: 8px;
        background: #f8f9fa;
    }
    #reader video {
        width: 100%;
        height: auto;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrcodeScanner = null;
const eventId = {{ $event->id }};

document.addEventListener('DOMContentLoaded', function() {
    const startScanBtn = document.getElementById('startScanBtn');
    const stopScanBtn = document.getElementById('stopScanBtn');
    const resultDiv = document.getElementById('result');
    const manualForm = document.getElementById('manualCheckInForm');

    function showResult(message, type = 'info') {
        resultDiv.style.display = 'block';
        resultDiv.className = 'alert alert-' + type;
        resultDiv.innerHTML = message;
        
        if (type === 'success' || type === 'danger') {
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, 5000);
        }
    }

    function performCheckIn(idNumber) {
        fetch(`/checkin/${eventId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ id_number: idNumber })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult(
                    `✓ 簽到成功！<br>掃描到的字串：<strong>${idNumber}</strong><br>參與者：${data.participant_name}<br>簽到時間：${data.check_in_time}`,
                    'success'
                );
            } else {
                showResult(
                    `✗ ${data.message}<br>掃描到的字串：<strong>${idNumber}</strong>${data.check_in_time ? '<br>簽到時間：' + data.check_in_time : ''}`,
                    'danger'
                );
            }
            // 1秒後自動重新開始掃描
            setTimeout(() => {
                startScanning();
            }, 1000);
        })
        .catch(error => {
            showResult(`✗ 簽到處理發生錯誤，請稍後再試<br>掃描到的字串：<strong>${idNumber}</strong>`, 'danger');
            console.error('Error:', error);
            // 1秒後自動重新開始掃描
            setTimeout(() => {
                startScanning();
            }, 1000);
        });
    }

    function startScanning() {
        if (html5QrcodeScanner) {
            return;
        }

        html5QrcodeScanner = new Html5Qrcode("reader");
        
        html5QrcodeScanner.start(
            { facingMode: "environment" }, // 使用後置相機（手機）或預設相機（電腦）
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            (decodedText, decodedResult) => {
                // 掃描成功，立即停止掃描
                console.log('掃描結果:', decodedText);
                
                // 立即停止掃描，等待簽到處理回應
                stopScanning();
                
                showResult(
                    `📷 掃描成功！<br>掃描到的字串：<strong>${decodedText}</strong><br>正在處理簽到...`,
                    'info'
                );
                
                // 處理簽到
                performCheckIn(decodedText);
            },
            (errorMessage) => {
                // 掃描錯誤（忽略，持續掃描）
            }
        )
        .catch((err) => {
            console.error('無法啟動相機:', err);
            showResult('無法啟動相機，請確認已授權相機權限', 'warning');
        });

        startScanBtn.style.display = 'none';
        stopScanBtn.style.display = 'inline-block';
    }

    function stopScanning() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }).catch((err) => {
                console.error('停止掃描失敗:', err);
            });
        }
        startScanBtn.style.display = 'inline-block';
        stopScanBtn.style.display = 'none';
    }

    startScanBtn.addEventListener('click', startScanning);
    stopScanBtn.addEventListener('click', stopScanning);

    // 手動輸入表單
    manualForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const idNumber = document.getElementById('manual_id_number').value.trim();
        if (idNumber) {
            performCheckIn(idNumber);
            document.getElementById('manual_id_number').value = '';
        }
    });

    // 自動開始掃描（頁面載入時）
    startScanning();
});
</script>
@endpush

