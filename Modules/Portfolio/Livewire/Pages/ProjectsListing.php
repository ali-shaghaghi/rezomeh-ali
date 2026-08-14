<?php

namespace Modules\Portfolio\Livewire\Pages;

use Modules\Core\Models\Project;
use Livewire\Component;

class ProjectsListing extends Component
{
    public $status = 'published';
    public $search = '';
    public $categoryFilter;

    public function mount()
    {
        $this->categoryFilter = request('category');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Project::with(['category', 'technologies']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%");
        }

        if ($this->categoryFilter) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->categoryFilter);
            });
        }

        return view('livewire.portfolio.projects-listing', [
            'projects' => $query->latest()->paginate(10),
            'categories' => \Modules\Core\Models\Category::all(),
        ]);
    }
}