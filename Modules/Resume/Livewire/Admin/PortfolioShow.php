<?php

namespace Modules\Resume\Livewire\Admin;

use Livewire\Component;
use Modules\Resume\Models\Project;
use Modules\Resume\Services\PortfolioService;

class PortfolioShow extends Component
{
    public Project $project;

    // مودال نمایش دمو
    public bool $showDemo = false;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);

        $project->load(['category', 'technologies', 'skills']);
        $this->project = $project;
    }

    // نمایش دمو در مودال iframe
    public function openDemo(): void
    {
        $link = app(PortfolioService::class)->resolveDemoLink($this->project);

        if (! $link) {
            return;
        }

        $this->showDemo = true;
    }

    public function render()
    {
        return view('resume::livewire.admin.portfolio-show', [
            'demoLink' => app(PortfolioService::class)->resolveDemoLink($this->project),
        ]);
    }
}
