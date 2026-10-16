@extends('layouts.home-layout')

@section('title', 'Katalog Kursus')

@section('content')
<div class="rs-page">
    <form action="{{ route('courses') }}" method="GET">
        <section class="rs-pagehead">
            <div class="rs-wrap">
                <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Katalog</p>
                <h1 class="rs-h1">Temukan Kursus Impianmu</h1>
                <p class="rs-lead">Jelajahi berbagai pilihan kursus untuk meningkatkan keterampilan dan karier Anda.</p>
                <div class="rs-search"><label for="catalog-search" class="rs-sr">Cari judul kursus</label><input id="catalog-search" type="text" name="search" placeholder="Cari judul kursus..." value="{{ request('search') }}"><button type="submit">Cari</button></div>
            </div>
        </section>

        <section class="rs-section">
            <div class="rs-wrap">
                <div class="rs-catalog">
                    {{-- Sidebar Filter Kategori --}}
                    <details class="rs-filter" open>
                        <summary>Filter kategori</summary>
                        <div class="rs-filter__list">
                            @foreach ($categories as $category)
                                <label class="rs-check" for="kategori-{{ $category->id }}"><input type="checkbox" name="categories[]" value="{{ $category->id }}" id="kategori-{{ $category->id }}" @if (is_array(request('categories')) && in_array($category->id, request('categories'))) checked @endif><span>{{ $category->name }}</span></label>
                            @endforeach
                        </div>
                        <button type="submit" class="rs-btn rs-btn--block">Terapkan filter</button>
                        @if (request()->filled('search') || request()->filled('categories'))
                            <p class="mt-3 mb-0"><a class="rs-link" href="{{ route('courses') }}">Hapus filter</a></p>
                        @endif
                    </details>

                    {{-- Konten Kanan --}}
                    <div>
                        <h2 class="rs-h2">Kelas Terbaru</h2>
                        <p class="rs-sub">Kami menyediakan kelas dengan materi yang dibuat sesuai kebutuhan di dunia Profesional.</p>
                        @if (request()->filled('search'))
                            <p class="rs-result-meta">Hasil pencarian untuk "{{ request('search') }}"</p>
                        @endif

                        {{-- Daftar Kursus --}}
                        @if ($courses->isNotEmpty())
                            <div class="rs-books">
                                @foreach ($courses as $course)
                                    <x-rs.course-book :course="$course" :show-instructor="true" />
                                @endforeach
                            </div>
                        @else
                            <div class="rs-empty">
                                <h3 class="rs-h3">Kursus tidak ditemukan</h3>
                                <p>Tidak ada kursus yang cocok dengan pencarian atau filter. Coba kata lain atau hapus filter.</p>
                                <a class="rs-btn" href="{{ route('courses') }}">Hapus filter dan lihat semua</a>
                            </div>
                        @endif

                        <x-rs.pager :paginator="$courses" />
                    </div>
                </div>
            </div>
        </section>
    </form>

    {{-- Bagian Rekomendasi --}}
    @if ($popularCourses->isNotEmpty())
        <section id="pricing" class="rs-section rs-section--paper">
            <div class="rs-wrap">
                <div class="rs-section-head">
                    <div>
                        <h2 class="rs-h2">Kursus Terpopuler</h2>
                    </div>
                </div>
                <div class="rs-books">
                    @foreach ($popularCourses as $course)
                        <x-rs.course-book :course="$course" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- BAGIAN BARU: Tampilkan Rekomendasi --}}
    {{-- @include('partials._recommended_courses', ['recommendedCourses' => $popularCourses, 'title' => 'Kursus Terpopuler']) --}}
</div>
@endsection
