<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class CommentAdminController extends Controller
{
    public function getAllComment()
    {
        try {
            $comments = Comment::with(['user', 'book'])
                            ->orderBy('created_at', 'desc')
                            ->get();
            if ($comments->isEmpty()) return HttpResponse::respondWithSuccess([], "Không có bình luận nào.");
            return HttpResponse::respondWithSuccess($comments,"Lấy comment thành công");    
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }                
    }

    public function getCommentsByBookId($bookId)
    {
        try {
            $comments = Comment::with('user') 
                            ->where('book_id', $bookId) 
                            ->orderBy('created_at', 'desc') 
                                ->get();
            if ($comments->isEmpty()) return HttpResponse::respondWithSuccess([], "Không có bình luận nào.");
            return HttpResponse::respondWithSuccess($comments,"Lấy comment thành công");
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function deleteComment($commentId)
    {
        try {
            if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
            $comment = Comment::find($commentId);
            if (!$comment) {
                return HttpResponse::respondError('Comment not found');
            }
            $comment->delete();
            return HttpResponse::respondWithSuccess('Comment deleted successfully');
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }   
}
