@extends('layouts.app')

@section('title', $event->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $event->name }}</h1>
        <p class="text-muted">活動日期：{{ $event->event_date->format('Y年m月d日') }}</p>
    </div>
    <div>
        <a href="{{ route('checkin.scan', $event->id) }}" class="btn btn-success">簽到頁面</a>
        <a href="{{ route('events.export', $event->id) }}" class="btn btn-secondary">匯出 Excel</a>
        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">返回列表</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">統計資訊</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="text-center">
                    <h3>{{ $event->participants->count() }}</h3>
                    <p class="text-muted mb-0">總參與者數</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-success">{{ $event->participants->filter(fn($p) => $p->checkIn)->count() }}</h3>
                    <p class="text-muted mb-0">已簽到</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-warning">{{ $event->participants->filter(fn($p) => !$p->checkIn)->count() }}</h3>
                    <p class="text-muted mb-0">未簽到</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3>{{ $event->participants->count() > 0 ? round($event->participants->filter(fn($p) => $p->checkIn)->count() / $event->participants->count() * 100, 1) : 0 }}%</h3>
                    <p class="text-muted mb-0">簽到率</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">參與者列表</h5>
    </div>
    <div class="card-body">
        @if($event->participants->isEmpty())
            <div class="alert alert-info">此活動尚無參與者</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>身份證字號</th>
                            <th>姓名</th>
                            <th>簽到狀態</th>
                            <th>簽到時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($event->participants as $participant)
                            <tr>
                                <td>{{ $participant->id_number }}</td>
                                <td>{{ $participant->name }}</td>
                                <td>
                                    @if($participant->checkIn)
                                        <span class="badge bg-success">已簽到</span>
                                    @else
                                        <span class="badge bg-warning">未簽到</span>
                                    @endif
                                </td>
                                <td>
                                    @if($participant->checkIn)
                                        {{ $participant->checkIn->check_in_time->format('Y-m-d H:i:s') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

