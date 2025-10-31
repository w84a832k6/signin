@extends('layouts.app')

@section('title', '活動列表')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>活動列表</h1>
    <a href="{{ route('events.create') }}" class="btn btn-primary">建立新活動</a>
</div>

@if($events->isEmpty())
    <div class="alert alert-info">
        目前沒有任何活動，請建立第一個活動。
    </div>
@else
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>活動名稱</th>
                    <th>活動日期</th>
                    <th>參與者數量</th>
                    <th>已簽到</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr>
                        <td>{{ $event->name }}</td>
                        <td>{{ $event->event_date->format('Y-m-d') }}</td>
                        <td>{{ $event->participants->count() }}</td>
                        <td>{{ $event->participants->filter(fn($p) => $p->checkIn)->count() }}</td>
                        <td>
                            <a href="{{ route('events.show', $event->id) }}" class="btn btn-sm btn-info">查看</a>
                            <a href="{{ route('checkin.scan', $event->id) }}" class="btn btn-sm btn-success">簽到</a>
                            <a href="{{ route('events.export', $event->id) }}" class="btn btn-sm btn-secondary">匯出</a>
                            <form action="{{ route('events.destroy', $event->id) }}" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除這個活動嗎？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">刪除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

