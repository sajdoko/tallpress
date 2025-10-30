<?php

namespace Sajdoko\TallPress\Livewire\Admin\Media;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Models\Media;
use Sajdoko\TallPress\Services\MediaService;

#[Lazy]
class Manager extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;
    use WithToast;

    public $files;

    public $search = '';

    public $uploading = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function uploadFiles()
    {
        // Check if files are selected
        if (empty($this->files) || ! is_array($this->files)) {
            $this->toastError('Please select at least one file to upload.');

            return;
        }

        $this->validate([
            'files.*' => 'required|file|max:10240', // 10MB max
        ]);

        $uploadedCount = 0;
        $mediaService = new MediaService;

        foreach ($this->files as $file) {
            try {
                $media = $mediaService->upload($file, auth()->id());

                // Log activity
                if (tallpress_setting('activity_log_enabled', true)) {
                    ActivityLog::log('uploaded media file', null, auth()->user(), [
                        'media_id' => $media->id,
                        'file_path' => $media->path,
                        'file_name' => $media->filename,
                    ]);
                }

                $uploadedCount++;
            } catch (\Exception $e) {
                $this->toastError("Failed to upload {$file->getClientOriginalName()}: ".$e->getMessage());
            }
        }

        $this->files = null;
        $this->uploading = false;

        if ($uploadedCount > 0) {
            $this->toastSuccess("{$uploadedCount} file(s) uploaded successfully.");
        }
    }

    public function deleteMedia($mediaId)
    {
        $media = Media::find($mediaId);

        if ($media) {
            // Check authorization
            $this->authorize('delete', $media);

            // Log activity
            if (tallpress_setting('activity_log_enabled', true)) {
                ActivityLog::log('deleted media file', null, auth()->user(), [
                    'media_id' => $media->id,
                    'file_path' => $media->path,
                ]);
            }

            $media->delete();
            $this->toastSuccess('File deleted successfully.');
        } else {
            $this->toastError('File not found.');
        }
    }

    public function placeholder(array $params = [])
    {
        return view('tallpress::admin.livewire.media.manager-placeholder')
            ->layout('tallpress::admin.layout');
    }

    public function render()
    {
        $user = auth()->user();
        $roleField = config('tallpress.roles.role_field', 'role');
        $isAdminOrEditor = in_array($user->{$roleField} ?? null, ['admin', 'editor']);

        $query = Media::query()
            ->orderBy('created_at', 'desc');

        // Non-admin/editor users can only see their own media
        if (! $isAdminOrEditor) {
            $query->where('uploaded_by', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('filename', 'like', "%{$this->search}%")
                    ->orWhere('path', 'like', "%{$this->search}%");
            });
        }

        $media = $query->paginate(20);

        return view('tallpress::admin.livewire.media.manager', [
            'media' => $media,
        ])->layout('tallpress::admin.layout');
    }
}
