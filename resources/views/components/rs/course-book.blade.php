@props(['course', 'showInstructor' => false])
@php
    $coverClass = 'rs-cover--c'.(($course->category_id ?? 0) % 5);
    $categoryName = $course->category?->name;
    $students = $course->getAttribute('students_count');
    $lessons = $course->getAttribute('lessons_count');
    $reviewsCount = $course->getAttribute('reviews_count');
@endphp
<a href="{{ route('courses.show', $course->slug) }}" class="rs-book">
    <div class="rs-cover {{ $coverClass }} {{ $course->thumbnail_url ? 'rs-cover--photo' : '' }}">
        @if ($course->thumbnail_url)
            <img src="{{ Storage::url($course->thumbnail_url) }}" alt="" loading="lazy">
            <span></span>
        @else
            <span class="rs-cover__publisher">{{ $siteSettings->site_name ?? '' }}</span>
        @endif
        <span class="rs-cover__series">{{ $categoryName ?? 'Kursus' }}</span>
    </div>
    <h3 class="rs-book__title">{{ Str::limit($course->title, 70) }}</h3>
    @if ($showInstructor && $course->instructor)
        <p class="rs-book__by">{{ $course->instructor->name }}</p>
    @endif
    @if (! is_null($reviewsCount))
        @if ($reviewsCount > 0)
            <span class="rs-rating">
                <x-rs.stars :value="$course->reviews_avg_rating" />
                {{ number_format($course->reviews_avg_rating, 1, ',', '.') }} ({{ $reviewsCount }} ulasan)
            </span>
        @else
            <span class="rs-rating">Belum ada ulasan</span>
        @endif
    @endif
    <div class="rs-book__foot">
        <x-rs.price :course="$course" />
        @if (! is_null($lessons) || ! is_null($students))
            <span class="rs-book__stats">
                @if (! is_null($lessons)){{ $lessons }} materi{{ '' }}@endif
                @if (! is_null($lessons) && ! is_null($students)), @endif
                @if (! is_null($students)){{ $students > 0 ? $students.' peserta' : 'Belum ada peserta' }}@endif
            </span>
        @endif
    </div>
</a>
