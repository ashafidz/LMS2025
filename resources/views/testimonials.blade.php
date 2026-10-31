@extends('layouts.home-layout')

@section('title', 'Testimoni Pengguna')

@section('content')
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Testimoni</p>
            <h1 class="rs-h1">Pengalaman Pengguna Platform</h1>
            <p class="rs-lead">Inilah cerita nyata dari pengguna yang sudah memanfaatkan platform kami untuk belajar dan berkembang.</p>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="rs-section">
        <div class="rs-wrap">
            @if ($allPlatformReviews->isNotEmpty())
                <div class="rs-quotes rs-quotes--3">
                    @foreach ($allPlatformReviews as $review)
                        <x-rs.testimonial :review="$review" />
                    @endforeach
                </div>
            @else
                <div class="rs-empty">
                    <p>Belum ada testimoni yang bisa ditampilkan.</p>
                </div>
            @endif

            {{-- Pagination --}}
            <x-rs.pager :paginator="$allPlatformReviews" />
        </div>
    </section><!-- /Testimonials Section -->
</div>
@endsection
