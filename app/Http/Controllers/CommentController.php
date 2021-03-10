<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Like;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CommentController extends Controller
{
    public function insertLikes(Request $request)
    {
        $likes = Like::where(['user_id' => Auth::user()->getId(), 'comment_id' => $request->id])->first();
        if (!$likes) {
            $likes = new Like();
            $likes->user_id = Auth::user()->getId();
            $likes->post_id = $request->postId;
            $likes->comment_id = $request->id;
            $likes->save();
        }
    }

    public function reply(Request $request)
    {
        $comment = Comment::where('id', $request->id)->first();
        $reply = new Reply();
        $reply->title = $request->reply;
        $reply->comment_id = $comment->id;
        $reply->user_id = Auth::id();
        $reply->post_id = $request->post_id;
        $reply->save();
        if ($request->hasfile('photo')) {
            foreach ($request->file('photo') as $image) {
                $name = time() . $image->getClientOriginalName();
                $image->move(public_path() . '/images', $name);
                $reply->images = $name;
                $reply->save();
            }
        }
        return Redirect::back();
    }

    public function reply_to_reply(Request $request)
    {
        $reply = Reply::where('id', $request->id)->first();
        $reply_to_reply = new Answer();
        $reply_to_reply->name = $request->answer;
        $reply_to_reply->reply_id = $reply->id;
        $reply_to_reply->user_id = Auth::id();
        $reply_to_reply->save();
        if ($request->hasfile('photo')) {
            foreach ($request->file('photo') as $image) {
                $name = time() . $image->getClientOriginalName();
                $image->move(public_path() . '/images', $name);
                $reply_to_reply->images = $name;
                $reply_to_reply->save();
            }
        }
        return back();
    }

    public function dislike(Request $request)
    {
        $likes = Like::where(['user_id' => Auth::id(), 'comment_id' => $request->id])->first();
        if ($likes) {
            $likes->delete();
        }
    }

    public function myComments()
    {
        $posts = Post::with('comments')->get();
        $user = User::where('id', Auth::id())->first();
        return view('comments.my-comments', compact('posts', $posts, 'user', $user));
    }

    public function memberComments(Request $request)
    {
        $memberComments = Comment::where('user_id', $request->id)->with('likes')->get();
        $user = User::where('id', $request->id)->first();
        return view('comments.member-comments', compact('memberComments', $memberComments, 'user', $user));
    }
}
