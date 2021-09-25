<?php

namespace App\Http\Controllers;

use cebe\markdown\GithubMarkdown;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\HtmlString;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $method = 'GET';
        $tag_id = 'PHP';
        $per_page = 30;

        $url = config('qiita.url') . '/api/v2/tags/' . $tag_id
            . '/items?page=1&per_page=' . $per_page;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $articles = null;
        }

        // ここから自分の記事取得
        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/authenticated_user/items';

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $my_articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $my_articles = null;
        }

        return view('articles.index')
            ->with(compact('articles', 'my_articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $method = 'POST';

        $url = config('qiita.url') . '/api/v2/items';

        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);
        // dd($tag_array);

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => $request->private == 'true' ? true : false,
            'tags' => $tags,
        ];
        // dd($data);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);
        } catch (ClientException $e) {
            // dd($e);
            // return back()->withErrors(['error' => $e->getResponse()->getReasonPhrase()]);
            return back()->withErrors(['error' => '投稿に失敗しました']);
        }
        $message = new HtmlString("記事の投稿に成功しました");
        return redirect()->route('articles.index')
            ->with('flash_message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            $parser = new GithubMarkdown();
            $parser->keepListStartNumber = true;
            $parser->enableNewlines = true;

            $html_string = $parser->parse($article->body);
            $article->html = new HtmlString($html_string);

            $tag_array = array_map(function ($tag) {
                return $tag->name;
            }, $article->tags);
            $article->tags = implode(', ', $tag_array);
        } catch (\Throwable $th) {
            return back();
        }

        //ユーザー情報を取得
        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/authenticated_user';

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $user = json_decode($body, false);
        } catch (\Throwable $th) {
            return back();
        }

        return view('articles.show')
            ->with(compact('article', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            $tag_array = array_map(function ($tag) {
                return $tag->name;
            }, $article->tags);
            $article->tags = implode(' ', $tag_array);
        } catch (\Throwable $th) {
            return back();
        }

        return view('articles.edit')
            ->with(compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $method = 'PATCH';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);
        // dd($tag_array);

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => $request->private == 'true' ? true : false,
            'tags' => $tags,
        ];
        // dd($data);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);
        } catch (ClientException $e) {
            // dd($e);
            // return back()->withErrors(['error' => $e->getResponse()->getReasonPhrase()]);
            return back()->withErrors(['error' => '更新に失敗しました']);
        }
        $message = new HtmlString("記事の更新に成功しました");
        return redirect()->route('articles.index')
            ->with('flash_message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $method = 'DELETE';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ]
        ];

        $client = new Client();

        try {
            $client->request($method, $url, $options);
        } catch (ClientException $e) {
            return back()->withErrors(['error' => '削除処理を失敗しました']);
        }
        return redirect()->route('articles.index')
            ->with('flash_message', '記事を削除しました');
    }
}
