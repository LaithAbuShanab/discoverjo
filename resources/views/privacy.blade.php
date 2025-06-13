<!-- resources/views/privacy.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Legal Documents - Rehletna-jo</title>
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
<h1>Legal Information</h1>

@foreach ($data as $section => $documents)
    <h2>{{ $section }}</h2>
    @foreach ($documents as $doc)
        <h3>{{ $doc->title }}</h3>
        <p>{!! nl2br(e($doc->content)) !!}</p>

        @if (!empty($doc->terms))
            <ul>
                @foreach ($doc->terms as $term)
                    <li>{{ $term['title'] ?? '' }} - {{ $term['content'] ?? '' }}</li>
                @endforeach
            </ul>
        @endif
    @endforeach
@endforeach

</body>
</html>
