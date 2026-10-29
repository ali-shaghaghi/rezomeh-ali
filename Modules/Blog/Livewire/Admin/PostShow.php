<?php

namespace Modules\Blog\Livewire\Admin;

use Livewire\Component;
use Modules\Core\Models\BlogPost;

class PostShow extends Component
{
    public BlogPost $post;

    public function mount(BlogPost $post): void
    {
        $this->post = $post->load(['user', 'categories', 'tags', 'comments.user']);
    }

    public function render()
    {
        return view('blog::livewire.admin.post-show');
    }
}
