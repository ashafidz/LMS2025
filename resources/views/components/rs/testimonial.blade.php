@props(['review'])
<figure class="rs-quote">
    <blockquote>{{ $review->comment }}</blockquote>
    <x-rs.stars :value="$review->rating" />
    <figcaption>
        <a class="rs-person" href="{{ route('profile.show', $review->user->id) }}">
            <img src="{{ asset($review->user->profile_picture_url ?? 'assets/profile-images/avatar-1.png') }}" alt="">
            <span>
                <b>{{ $review->user->name }}</b>
                <span>{{ $review->user->studentProfile->headline ?? 'Siswa' }}</span>
            </span>
        </a>
    </figcaption>
</figure>
