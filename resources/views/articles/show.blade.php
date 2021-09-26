<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/github-markdown-css@3.0.1/github-markdown.min.css">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <div class="button-block">
            <button type="button" onclick="location.href='{{ route('articles.index') }}'" class="btn back-button">一覧へ戻る</button>
            @if ($article->user->permanent_id == $user->permanent_id)
                <button type="button" onclick="location.href='{{ route('articles.edit', $article->id ) }}'" class="btn edit-button">編集する</button>
                <button type="submit" onclick="if(!confirm('本当に削除していいですか？')){return false}" class="btn delete-button" form="delete-form">削除する</button>
                <form action="{{ route('articles.destroy', $article->id) }}" method="POST" id="delete-form">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
        @if (!empty($errors))
            <div class="error">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif
        <div class="article-block">
            <div class="post-status">
                <img src="{{ $article->user->profile_image_url }}" alt="" class="icon">
                <p>
                    <a href="https://qiita.com/{{ $article->user->id }}">{{ '@'.$article->user->id }}</a>
                    @if ($article->created_at == $article->updated_at)
                    {{ 'が' . substr($article->created_at, 0, 10) . 'に作成' }}
                    @else
                    {{ 'が' . substr($article->updated_at, 0, 10) . 'に更新' }}
                    @endif
                </p>
            </div>
            <h1>{{ $article->title }}</h1>
            <p class="tags"><i class="fa fa-tags mr-1of2"></i>
                {{ $article->tags }}
            </p>
            <div class="markdown-body">
                {{ $article->html }}
            </div>
        </div>
    </div>
</body>
</html>
