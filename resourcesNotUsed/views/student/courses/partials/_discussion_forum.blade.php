{{-- resources/views/student/courses/partials/_discussion_forum.blade.php --}}

<hr class="my-5">
<div class="discussion-container">
    <h4 class="font-weight-bold mb-4">Diskusi Pelajaran</h4>

    {{-- Form untuk Komentar Baru --}}
    @if (!$is_preview)
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('student.lessons.discussions.store', $lesson->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <textarea name="content" class="form-control" rows="3" placeholder="Tulis komentar atau pertanyaan Anda di sini..."
                            required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Daftar Komentar yang Sudah Ada --}}
    @forelse ($discussions as $discussion)
        <div class="media mb-4">
            <img class="d-flex mr-3 rounded-circle"
                src="{{ $discussion->user->profile_picture_url ? asset('storage/' . ltrim($discussion->user->profile_picture_url, '/')) : 'https://placehold.co/50x50' }}"
                alt="User Avatar" style="width: 50px; height: 50px;">
            <div class="media-body">
                <div class="mt-0 d-flex align-items-center gap-2">
                    <h5 class="d-flex align-items-center mr-1">
                        {{ $discussion->user->name }}
                        @if ($discussion->user->equippedBadge)
                            <span class="badge badge-primary mx-1">
                                {{ $discussion->user->equippedBadge->title }}
                            </span>
                        @endif
                    </h5>
                    <small class="text-muted mx-1">- {{ $discussion->created_at->diffForHumans() }}</small>
                </div>

                {!! nl2br(e($discussion->content)) !!}

                {{-- Tombol Balas (jika bukan pratinjau) --}}
                @if (!$is_preview && !$discussion->is_deleted)
                    <br>
                    <a href="#" class="reply-btn" data-toggle="collapse"
                        data-target="#reply-form-{{ $discussion->id }}">Balas</a>

                    {{-- Tampilkan tombol Hapus hanya untuk pemilik komentar --}}
                    @if (Auth::user()->id === $discussion->user_id)
                        <span class="text-muted small mx-1">&middot;</span>
                        <form action="{{ route('student.lessons.discussions.destroy', $discussion->id) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini? Aksi ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-danger p-0 small">Hapus</button>
                        </form>
                    @endif
                @endif

                {{-- Form Balasan yang Tersembunyi --}}
                <div class="collapse mt-3" id="reply-form-{{ $discussion->id }}">
                    <form action="{{ route('student.lessons.discussions.store', $lesson->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $discussion->id }}">
                        <div class="form-group">
                            <textarea name="content" class="form-control" rows="2" placeholder="Tulis balasan Anda..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-secondary">Kirim Balasan</button>
                    </form>
                </div>

                {{-- Menampilkan Balasan --}}
                @foreach ($discussion->replies as $reply)
                    <div class="media mt-4">
                        <a class="d-flex pr-3" href="#">
                            <img src="{{ $reply->user->profile_picture_url ? asset('storage/' . ltrim($reply->user->profile_picture_url, '/')) : 'https://placehold.co/40x40' }}"
                                alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                        </a>
                        <div class="media-body">
                            <div class="mt-0 d-flex align-items-center gap-2">
                                <h5 class="d-flex align-items-center mr-1">
                                    {{ $reply->user->name }}
                                    @if ($reply->user->equippedBadge)
                                        <span class="badge badge-primary mx-1">
                                            {{ $reply->user->equippedBadge->title }}
                                        </span>
                                    @endif
                                </h5>
                                <small class="text-muted mx-1">- {{ $reply->created_at->diffForHumans() }}</small>
                            </div>
                            {{-- <h5 class="mt-0">{{ $reply->user->name }} <small class="text-muted">- {{ $reply->created_at->diffForHumans() }}</small></h5> --}}
                            {!! nl2br(e($reply->content)) !!}

                            {{-- Tampilkan tombol Hapus hanya untuk pemilik komentar --}}
                            @if (!$reply->is_deleted)
                                @if (Auth::user()->id === $reply->user_id)
                                    <br>
                                    <form action="{{ route('student.lessons.discussions.destroy', $reply->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini? Aksi ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 small">Hapus</button>
                                    </form>
                                @endif
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-muted text-center">Jadilah yang pertama memulai diskusi untuk pelajaran ini.</p>
    @endforelse
</div>
