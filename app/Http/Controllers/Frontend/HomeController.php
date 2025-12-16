<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Product;
use App\Models\File;
use App\Models\Course;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage with featured items
     */
    public function index(): View
    {
        $featuredServices = Service::active()
            ->featured()
            ->ordered()
            ->limit(6)
            ->get();

        $featuredProducts = Product::active()
            ->featured()
            ->limit(6)
            ->get();

        $featuredFiles = File::active()
            ->featured()
            ->limit(6)
            ->get();

        $featuredCourses = Course::active()
            ->featured()
            ->limit(6)
            ->get();

        $topServices = Service::active()
            ->orderBy('sold_count', 'desc')
            ->limit(3)
            ->get();

        $topProducts = Product::active()
            ->inStock()
            ->orderBy('sold_count', 'desc')
            ->limit(3)
            ->get();

        return view('frontend.home.index', [
            'featuredServices' => $featuredServices,
            'featuredProducts' => $featuredProducts,
            'featuredFiles' => $featuredFiles,
            'featuredCourses' => $featuredCourses,
            'topServices' => $topServices,
            'topProducts' => $topProducts,
        ]);
    }

    /**
     * Display search results
     */
    public function search(): View
    {
        $query = request()->input('q');
        $type = request()->input('type', 'all');

        $results = [];

        if ($type === 'all' || $type === 'services') {
            $results['services'] = Service::active()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->limit(10)
                ->get();
        }

        if ($type === 'all' || $type === 'products') {
            $results['products'] = Product::active()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->limit(10)
                ->get();
        }

        if ($type === 'all' || $type === 'files') {
            $results['files'] = File::active()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->limit(10)
                ->get();
        }

        if ($type === 'all' || $type === 'courses') {
            $results['courses'] = Course::active()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->limit(10)
                ->get();
        }

        return view('frontend.home.search', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
        ]);
    }
}
