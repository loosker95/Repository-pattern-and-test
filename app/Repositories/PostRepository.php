<?php

namespace App\Repositories;

use DB;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Benchmark;
use App\Http\Requests\CreateRequest;

class PostRepository
{
    public function getAll()
    {
        try {
            // $data = DB::table('posts')->orderByDesc('id')->paginate(5);
            $data = Post::with('comments')->orderBy('id', 'desc')->paginate(10);
            // Benchmark::dd(fn () => Post::with('comments')->orderBy('id', 'desc')->paginate(10));
            return $data;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getOne($slug)
    {
        $data = Post::with(['comments' => function ($query) {
            $query->orderBy('id', 'desc');
        }])->where('posts.slug', $slug)->get();
        if (!$data) {
            return abort(404);
        }
        return $data;
    }

    public function addPost(CreateRequest $request)
    {
        Post::create([
            'user_id' => $this->getUserInfo()->id,
            'author' => $this->getUserInfo()->name,
            'slug' =>  $request->title
        ] + $request->validated());
    }

    public function changeEdit($id)
    {
        $data = DB::table('posts')->where('id', $id)->first();
        if (!$data) {
            return abort(404);
        }
        return $data;
    }

    public function infosUpdate(CreateRequest $request, $id)
    {
        DB::table('posts')->where('id', $id)->update([
            'title' => $request->title,
            'summary' => $request->summary,
            'body' => $request->body,
        ]);
    }

    public function toTrash($id)
    {
        Post::where('id', $id)->with('comments')->delete();
    }

    private function getUserInfo()
    {
        return User::inRandomOrder()->first();
    }
}
