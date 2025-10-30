<?php

namespace Sajdoko\TallPress\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sajdoko\TallPress\Services\MediaService;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Upload image for inline editor use.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:'.tallpress_setting('images_max_size', 2048),
        ]);

        try {
            $media = $this->mediaService->upload(
                $request->file('file'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $media->id,
                    'url' => $media->url,
                    'filename' => $media->filename,
                    'width' => $media->width,
                    'height' => $media->height,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
