{{-- Points recorded outside any lesson (e.g. older course purchase points). Shown only when there are any. --}}
@if($otherPointHistories->isNotEmpty())
    <div class="card shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom pt-4 pb-3 d-flex justify-content-between align-items-center" style="border-radius: 12px 12px 0 0;">
            <h5 class="font-weight-bold mb-0 text-dark"><i class="fa fa-plus-circle me-2"></i> Poin di luar materi</h5>
            <div class="text-end">
                <span class="badge badge-light-primary text-primary font-weight-bold px-3 py-2" style="font-size: 13px;">
                    <i class="fa fa-star me-1"></i> {{ number_format($otherPointHistories->sum('points'), 0, ',', '.') }} Poin
                </span>
            </div>
        </div>
        <div class="card-block p-0">
            <div class="list-group list-group-flush">
                @foreach($otherPointHistories as $history)
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h6 class="mb-1 text-dark font-weight-bold">{{ $history->description }}</h6>
                            <small class="text-muted">{{-- created_at is already in the viewer's timezone (HasLocalDates) --}}
                            {{ $history->created_at->locale('id')->isoFormat('D MMM YYYY, HH.mm') }}</small>
                        </div>
                        <span class="badge badge-light-success text-success font-weight-bold p-2 px-3 shadow-sm" style="font-size: 13px;">
                            <i class="fa fa-star me-1"></i> +{{ number_format($history->points, 0, ',', '.') }} Poin
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
