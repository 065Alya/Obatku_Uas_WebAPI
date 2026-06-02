<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ArticleService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ArticleManagementController extends Controller
{
    public function __construct(
        protected ArticleService $articleService,
    ) {}

    /**
     * List all articles (admin).
     */
    public function index()
    {
        $articles = $this->articleService->getAll();

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.articles.create');
    }

    /**
     * Store article.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:50',
            'is_published' => 'boolean',
        ]);

        if ($request->boolean('is_published')) {
            $validated['published_at'] = now();
        }

        $this->articleService->create($validated);

        ActivityLogService::log('create', "Membuat artikel: {$validated['title']}", 'HealthArticle');

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dibuat!');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $article = \App\Models\HealthArticle::findOrFail($id);

        return view('admin.articles.edit', compact('article'));
    }

    /**
     * Update article.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:50',
            'is_published' => 'boolean',
        ]);

        if ($request->boolean('is_published') && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $this->articleService->update($id, $validated);

        ActivityLogService::log('update', "Memperbarui artikel: {$validated['title']}", 'HealthArticle', $id);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil diperbarui!');
    }

    /**
     * Delete article.
     */
    public function destroy(int $id)
    {
        $this->articleService->delete($id);

        ActivityLogService::log('delete', 'Menghapus artikel', 'HealthArticle', $id);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }
}
