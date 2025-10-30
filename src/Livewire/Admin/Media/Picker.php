<?php

namespace Sajdoko\TallPress\Livewire\Admin\Media;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\Media;
use Sajdoko\TallPress\Services\MediaService;

class Picker extends Component
{
    use WithFileUploads;
    use WithPagination;
    use WithToast;

    public $files;

    public $search = '';

    public $selectedMedia = null;

    public $mode = 'library'; // 'library' or 'upload'

    public $target = 'editor'; // 'editor' or 'featured-image'

    protected $listeners = ['openMediaPicker' => 'open', 'setMediaTarget' => 'setTarget'];

    public function setTarget($target)
    {
        $this->target = $target ?? 'editor';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectMedia($mediaId)
    {
        $this->selectedMedia = Media::find($mediaId);
    }

    public function insertSelected()
    {
        if ($this->selectedMedia) {
            if ($this->target === 'featured-image') {
                // Dispatch to Livewire component for featured image
                $this->dispatch('mediaSelectedForFeatured', [
                    'id' => $this->selectedMedia->id,
                    'url' => $this->selectedMedia->url,
                    'filename' => $this->selectedMedia->filename,
                    'width' => $this->selectedMedia->width,
                    'height' => $this->selectedMedia->height,
                ]);
            } else {
                // Dispatch to Alpine/JS for editor
                $this->dispatch('mediaSelected', [
                    'id' => $this->selectedMedia->id,
                    'url' => $this->selectedMedia->url,
                    'filename' => $this->selectedMedia->filename,
                    'width' => $this->selectedMedia->width,
                    'height' => $this->selectedMedia->height,
                ]);
            }
            $this->close();
        }
    }

    public function uploadFiles()
    {
        if (empty($this->files) || ! is_array($this->files)) {
            $this->toastError('Please select at least one file to upload.');

            return;
        }

        $this->validate([
            'files.*' => 'required|file|max:10240',
        ]);

        $mediaService = new MediaService;
        $uploadedMedia = null;

        foreach ($this->files as $file) {
            try {
                $uploadedMedia = $mediaService->upload($file, auth()->id());
            } catch (\Exception $e) {
                $this->toastError("Failed to upload {$file->getClientOriginalName()}: ".$e->getMessage());
            }
        }

        $this->files = null;

        if ($uploadedMedia) {
            // Auto-select the last uploaded image
            $this->selectedMedia = $uploadedMedia;
            $this->mode = 'library';
            $this->toastSuccess('File(s) uploaded successfully.');
        }
    }

    public function switchMode($mode)
    {
        $this->mode = $mode;
    }

    public function close()
    {
        $this->dispatch('closeMediaPicker');
        $this->reset(['selectedMedia', 'search', 'files', 'mode', 'target']);
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

        return view('tallpress::admin.livewire.media.picker', [
            'media' => $media,
        ]);
    }
}
