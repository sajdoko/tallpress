<?php

namespace Sajdoko\TallPress\Livewire\Front;

use Livewire\Component;

class SearchWidget extends Component
{
    public string $query = '';
    public string $search = '';

    public bool $searchEnabled = true;

    public function mount(): void
    {
        $this->searchEnabled = tallpress_setting('search_enabled', true);
        $this->search = request('q', '');
    }

    public function performSearch(): void
    {
        if (empty($this->search)) {
            return;
        }

        $this->redirect(route('tallpress.posts.index', ['q' => $this->search]), navigate: true);
    }

    public function render()
    {
        return view('tallpress::front.livewire.search-widget');
    }
}
