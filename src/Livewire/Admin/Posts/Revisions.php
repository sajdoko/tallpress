<?php

namespace Sajdoko\TallPress\Livewire\Admin\Posts;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\PostRevision;

class Revisions extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithToast;

    public Post $post;

    public $selectedRevision = null;

    public $showComparison = false;

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->authorize('update', $this->post);
    }

    public function viewRevision($revisionId)
    {
        $this->selectedRevision = PostRevision::findOrFail($revisionId);
        $this->showComparison = true;
    }

    public function closeComparison()
    {
        $this->selectedRevision = null;
        $this->showComparison = false;
    }

    public function restoreRevision($revisionId)
    {
        $this->authorize('update', $this->post);

        $revision = PostRevision::findOrFail($revisionId);

        // Restore the revision
        $this->post->update([
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'body' => $revision->body,
            'meta' => $revision->meta,
        ]);

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log(
                'restored post revision',
                $this->post,
                auth()->user(),
                ['revision_id' => $revision->id]
            );
        }

        $this->closeComparison();
        session()->flash('success', 'Revision restored successfully.');
        $this->redirectRoute('tallpress.admin.posts.revisions', $this->post);
    }

    public function deleteRevision($revisionId)
    {
        $this->authorize('update', $this->post);

        $revision = PostRevision::findOrFail($revisionId);

        // Ensure we don't delete all revisions - keep at least one
        if ($this->post->revisions()->count() <= 1) {
            $this->toastError('Cannot delete the last revision.');

            return;
        }

        $revision->delete();

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log(
                'deleted post revision',
                $this->post,
                auth()->user(),
                ['revision_id' => $revisionId]
            );
        }

        $this->closeComparison();
        $this->toastSuccess('Revision deleted successfully.');
    }

    public function deleteAllRevisions()
    {
        $this->authorize('update', $this->post);

        $count = $this->post->revisions()->count();

        if ($count === 0) {
            $this->toastError('No revisions to delete.');

            return;
        }

        $this->post->revisions()->delete();

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log(
                'deleted all post revisions',
                $this->post,
                auth()->user(),
                ['count' => $count]
            );
        }

        $this->closeComparison();
        $this->toastSuccess("{$count} revision(s) deleted successfully.");
    }

    public function render()
    {
        $revisions = $this->post->revisions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tallpress::admin.livewire.posts.revisions', [
            'revisions' => $revisions,
        ])->layout('tallpress::admin.layout');
    }
}
