@extends('blog::layouts.backend.edit-post')

@section('content')
    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-body">
                <h3 wire:loading.remove wire:target="submit"
                    class="text-center font-bold text-2xl">Create New Post</h3>
                <h3 wire:loading.remove wire:target="submit"
                    class="text-center font-bold text-2xl text-red-500">You have an error in one of the fields <br> Please Check Again</h3>

                <div class="row">
                    <div class="col-lg-12">
                        <label for="inputName">Title</label>
                        <input type="text" wire:model="title" value="{{ old('title') }}"
                               placeholder="e.g. Top 10 Programming Languages in 2021"
                               title="A short and catchy title for your post"
                               class="form-control" style="direction: rtl">
                    </div>
                    <div class="col-lg-6">
                        <label class="text-sm font-medium text-gray-700" for="inputName">Description:</label>
                        <textarea wire:model="description"
                                  placeholder="a short text about your post"
                                  class="form-control"></textarea>
                    </div>
                    <div class="col-lg-6">
                        <label class="text-sm font-medium text-gray-700" for="inputCategories">Choose a category</label>
                        <select wire:model="category" class="form-control">
                            <option value=0>Select an category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                        @error('image') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="text-sm font-medium text-gray-700" for="inputContent">Content</label>
                        <div class="mt-2" wire:ignore>
                            <div wire:ignore x-ref="surface"
                                 class="border border-gray-300 rounded-lg p-3 bg-white">
                                <div x-ref="editor"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-center">
                    <button class="btn btn-primary" type="submit" wire:loading.attr="disabled"
                    wire:target="submit">
                        <span class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Saving Product
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
