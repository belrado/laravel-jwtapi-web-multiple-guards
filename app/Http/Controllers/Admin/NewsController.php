<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use App\Services\NewsService;

class NewsController extends Controller
{
    private News $news;
    private Category $category;
    protected NewsService $newsService;

    protected Tag $tag;
    public function __construct(News $news, Category $category, NewsService $newsService, Tag $tag)
    {
        $this->news = $news;
        $this->newsService = $newsService;
        $this->category = $category;
        $this->tag = $tag;
    }
    public function newsList()
    {
        $pageTitle = '뉴스 목록';
        $page = 1;
        $limit = 10;
        $tags = $this->tag::where('use', 'y')->get();
        $categories = $this->category->get();//$this->category::where('use', 'y')->get();
        $detailModalId = 'detailModal';
        $allUseSetModalId = 'allUseSetModalId';
        $allServiceDateSetModalId = 'allServiceDateSetModalId';
        $categoryId = 'categoryTab';
        $modalClass = "modal-lg modal-dialog-centered modal-dialog-scrollable";

        return view('pages.admin.news.list', compact('pageTitle', 'page', 'categories', 'limit', 'categoryId', 'modalClass', 'tags', 'detailModalId', 'allUseSetModalId', 'allServiceDateSetModalId'));
    }

    public function newsDetail($no)
    {
        $data['no'] = $no ?? 'none';
        return view('pages.admin.news.detail', $data);
    }

    public function newsWrite()
    {
        $pageTitle = '뉴스 작성';
        $categories = $this->category::where('use', 'y')->get();
        $tags = $this->tag::where('use', 'y')->get();
        return view('pages.admin.news.write', compact('pageTitle', 'categories', 'tags'));
    }

    public function category()
    {
        $pageTitle = 'News - 카테고리 / 해시태그';
        $modalClass = 'modal-fullscreen';
        return view('pages.admin.news.category', compact('pageTitle', ));
    }
}
