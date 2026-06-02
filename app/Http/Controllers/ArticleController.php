<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articleService,
    ) {}

    /**
     * Public article listing.
     */
    public function index()
    {
        $articles = $this->articleService->getPublished();
        $popular = $this->articleService->getPopular();

        return view('articles.index', compact('articles', 'popular'));
    }

    /**
     * Show single article.
     */
    public function show(string $slug)
    {
        $article = $this->articleService->findBySlug($slug);

        if (!$article) {
            abort(404);
        }

        return view('articles.show', compact('article'));
    }
}
