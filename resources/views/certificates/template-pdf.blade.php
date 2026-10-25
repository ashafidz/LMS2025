<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        {!! $fontFaceCss !!}
        @page { margin: 0; }
        body { margin: 0; padding: 0; }
        .page { position: relative; width: {{ $pageW }}mm; height: {{ $pageH }}mm; overflow: hidden; }
        .background { position: absolute; left: 0; top: 0; width: {{ $pageW }}mm; height: {{ $pageH }}mm; }
        .el { position: absolute; line-height: 1.2; }
    </style>
</head>
<body>
    <div class="page" style="width: {{ $pageW }}mm; height: {{ $pageH }}mm;">
        <img class="background" src="{{ $backgroundPath }}" alt="">
        @foreach ($elements as $e)
            <div class="el" style="left: {{ $e['left_mm'] }}mm; top: {{ $e['top_mm'] }}mm; width: {{ $e['width_mm'] }}mm; text-align: {{ $e['align'] }}; font-family: '{{ $e['family'] }}', sans-serif; font-size: {{ $e['size'] }}pt; color: {{ $e['color'] }}; font-weight: {{ $e['bold'] ? 'bold' : 'normal' }}; font-style: {{ $e['italic'] ? 'italic' : 'normal' }};">{{ $e['text'] }}</div>
        @endforeach
    </div>
</body>
</html>
