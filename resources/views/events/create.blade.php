@extends('layouts.app')

@section('title', '建立活動')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>建立新活動</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">活動名稱 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="event_date" class="form-label">活動日期 <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('event_date') is-invalid @enderror" id="event_date" name="event_date" value="{{ old('event_date') }}" required>
                        @error('event_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="excel_file" class="form-label">參與者 Excel 檔案 <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                        <div class="form-text">
                            請上傳 Excel 檔案（.xlsx 或 .xls）。檔案需包含「id」和「name」欄位，其他英文欄位也會一併匯入。
                        </div>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">取消</a>
                        <button type="submit" class="btn btn-primary">建立活動</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

