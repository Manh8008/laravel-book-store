<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Models\Books;
use App\Http\Library\HttpResponse;

class CommentController extends Controller
{
    public function addComment(Request $request, $bookId)
    {
        if (!Auth::check()) return HttpResponse::respondError('Bạn phải đăng nhập để comment');
        $request->validate([
            'content' => 'required|string',  
        ]);
        try {
            $book = Books::find($bookId);
            if (!$book) return HttpResponse::respondError('Sách không tồn tại');
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'book_id' => $bookId,
                'content' => $request->content,
            ]);
            return HttpResponse::respondWithSuccess($comment,"Bình luận thành công");
        } catch (\Exception $e) {
            return HttpResponse::respondNotFound();
        }
    }

    public function deleteComment($commentId)
    {
        try {
            if (!Auth::check()) return HttpResponse::respondError('Bạn phải đăng nhập để comment');
            $comment = Comment::find($commentId);
            if (!Auth::check()) return HttpResponse::respondError('Bình luận không tồn tại');
            if ($comment->user_id != Auth::id()) return HttpResponse::respondError('Bạn không có quyền xóa bình luận này');
            $comment->delete();
            return HttpResponse::respondWithSuccess(null,"Bình luận đã được xóa");
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getCommentsByBook($bookId)
    {
        try {
            $book = Books::find($bookId);
            if (!$book) return HttpResponse::respondError('Sách không tồn tại');
            $comments = $book->comments()->get();  
            if ($comments->isEmpty())  return HttpResponse::respondWithSuccess([], 'Sách không có bình luận nào');
            return HttpResponse::respondWithSuccess($comments);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

}
