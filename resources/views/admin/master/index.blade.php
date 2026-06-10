@extends('layouts.app')
@section('title', 'Master Data')
@section('page-title', 'Master Data')

@section('content')
@include('partials.alerts')
<div class="row g-3">
    @foreach($cards as $card)
        <div class="col-md-4">
            <a href="{{ $card['route'] }}" class="text-decoration-none text-dark">
                <div class="card p-4 h-100">
                    <div class="text-muted small">{{ $card['label'] }}</div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="fs-3 fw-bold">{{ $card['count'] }}</div>
                        <i class="bi bi-database fs-3 text-primary"></i>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection