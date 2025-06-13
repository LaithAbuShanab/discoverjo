<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Legal Documents - Discover Jordan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; line-height: 1.6; }
        h1, h2 { color: #2c3e50; }
        h3 { color: #34495e; margin-top: 20px; }
        p { margin-bottom: 10px; }
        ul { padding-left: 20px; }
    </style>
</head>
<body>

<h1>Privacy Policy</h1>
<p><strong>Last Updated:</strong> {{ $last_updated }}</p>

@foreach ($data as $section)
    @foreach ($section as $sectionTitle => $documents)
        <h2>{{ $sectionTitle }}</h2>
        @foreach ($documents as $doc)
            <h3>{{ $doc->title }}</h3>
            <div>{!! $doc->content !!}</div>

            @if (!empty($doc->terms))
                <ul>
                    @foreach ($doc->terms as $term)
                        <li><strong>{{ $term['title'] ?? '' }}</strong> - {{ $term['content'] ?? '' }}</li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    @endforeach
@endforeach

</body>
</html>
