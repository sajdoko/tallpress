<?php

namespace Sajdoko\TallPress\Livewire\Admin\Comments;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Models\Comment;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithToast;

    public $search = '';

    public $filter = 'all';

    public $selectedComments = [];

    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedComments = $this->getCommentsQuery()->pluck('id')->toArray();
        } else {
            $this->selectedComments = [];
        }
    }

    public function approveComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->update(['approved' => true]);

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('approved comment', $comment, auth()->user());
        }

        $this->toastSuccess('Comment approved successfully.');
    }

    public function rejectComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->update(['approved' => false]);

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('rejected comment', $comment, auth()->user());
        }

        $this->toastSuccess('Comment rejected successfully.');
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        // Log activity before deletion
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('deleted comment', $comment, auth()->user(), [
                'author_name' => $comment->author_name,
                'body' => substr($comment->body, 0, 100),
            ]);
        }

        $comment->delete();

        $this->toastSuccess('Comment deleted successfully.');
    }

    public function bulkApprove()
    {
        if (empty($this->selectedComments)) {
            $this->toastError('No comments selected.');

            return;
        }

        Comment::whereIn('id', $this->selectedComments)->update(['approved' => true]);

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log(
                'bulk approved comments',
                null,
                auth()->user(),
                ['comment_count' => count($this->selectedComments)]
            );
        }

        $count = count($this->selectedComments);
        $this->selectedComments = [];
        $this->selectAll = false;

        $this->toastSuccess("{$count} comments approved successfully.");
    }

    public function bulkDelete()
    {
        if (empty($this->selectedComments)) {
            $this->toastError('No comments selected.');

            return;
        }

        Comment::whereIn('id', $this->selectedComments)->delete();

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log(
                'bulk deleted comments',
                null,
                auth()->user(),
                ['comment_count' => count($this->selectedComments)]
            );
        }

        $count = count($this->selectedComments);
        $this->selectedComments = [];
        $this->selectAll = false;

        $this->toastSuccess("{$count} comments deleted successfully.");
    }

    protected function getCommentsQuery()
    {
        $query = Comment::with('post')
            ->whereHas('post'); // Only comments with non-deleted posts

        if ($this->filter === 'pending') {
            $query->where('approved', false);
        } elseif ($this->filter === 'approved') {
            $query->where('approved', true);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('body', 'like', '%'.$this->search.'%')
                    ->orWhere('author_name', 'like', '%'.$this->search.'%')
                    ->orWhere('author_email', 'like', '%'.$this->search.'%');
            });
        }

        return $query->latest();
    }

    public function render()
    {
        $comments = $this->getCommentsQuery()->paginate(15);

        return view('tallpress::admin.livewire.comments.index', [
            'comments' => $comments,
        ])->layout('tallpress::admin.layout');
    }
}
