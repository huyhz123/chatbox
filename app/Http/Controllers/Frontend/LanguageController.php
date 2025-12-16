<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * List of available languages
     */
    protected array $languages = [
        'en' => 'English',
        'vi' => 'Tiếng Việt',
    ];

    /**
     * Switch language
     */
    public function switch(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'language' => 'required|string|in:' . implode(',', array_keys($this->languages)),
        ]);

        $language = $request->input('language');

        // Store in session
        session()->put('language', $language);

        // If user is authenticated, update preference
        if (auth()->check()) {
            auth()->user()->update([
                'preferred_language' => $language,
            ]);
        }

        // Set app locale
        app()->setLocale($language);

        // If AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Language switched to ' . $this->languages[$language],
                'language' => $language,
            ]);
        }

        // Redirect back
        return redirect()->back()->with('message', 'Language switched to ' . $this->languages[$language]);
    }

    /**
     * Get available languages
     */
    public function list(): JsonResponse
    {
        $currentLanguage = session()->get('language', config('app.locale', 'en'));

        $languages = array_map(function ($key, $name) use ($currentLanguage) {
            return [
                'code' => $key,
                'name' => $name,
                'current' => $key === $currentLanguage,
            ];
        }, array_keys($this->languages), $this->languages);

        return response()->json([
            'success' => true,
            'languages' => array_values($languages),
            'current' => $currentLanguage,
        ]);
    }

    /**
     * Get current language
     */
    public function current(): JsonResponse
    {
        $currentLanguage = session()->get('language', config('app.locale', 'en'));

        return response()->json([
            'success' => true,
            'language' => $currentLanguage,
            'name' => $this->languages[$currentLanguage] ?? $currentLanguage,
        ]);
    }

    /**
     * Get translations
     */
    public function translations(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'nullable|string|in:' . implode(',', array_keys($this->languages)),
        ]);

        $language = $request->input('language', session()->get('language', config('app.locale', 'en')));

        try {
            // Load translation file
            $translations = trans('messages', [], $language);

            return response()->json([
                'success' => true,
                'language' => $language,
                'translations' => $translations ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load translations: ' . $e->getMessage(),
            ], 422);
        }
    }
}
