<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function snake_to_title($value): string
{
    return Str::title(str_replace('_', ' ', $value));
}


function diffForHumans($date)
{

    $locale = config('app.locale') ?? config('app.fallback_locale');
    Carbon::setlocale($locale);
    return Carbon::parse($date)->diffForHumans();
}

function showDateTime($date, $format = 'Y-m-d h:i A')
{
    $locale = config('app.locale') ?? config('app.fallback_locale');
    Carbon::setlocale($locale);
    return Carbon::parse($date)->translatedFormat($format);
}


function getPublicFileUrl(?string $path): ?string
{
    if (!$path || !Storage::disk('public')->exists($path)) {
        return null;
    }

    return asset('storage/' . ltrim($path, '/'));
}

function saveFile($file, string $directory): string
{
    return $file->store($directory, 'public');
}

function generateUniqueSymbol(
    string $prefix,
    string $model,
    string $column = 'symbol',
    int $length = 4
): string {
    do {
        $symbol = $prefix . '-' . strtoupper(Str::random(2)) . rand(10, 99);
    } while ($model::where($column, $symbol)->exists());

    return $symbol;
}
// function getPublicFileUrl(?string $path): ?string
// {
//     if (!$path || !Storage::disk('opt_uploads')->exists($path)) {
//         return null;
//     }

//     // If you configured 'url' in filesystems.php for otp_upload, use it:
//     return Storage::disk('opt_uploads')->url($path);
// }

// function saveFile($file, string $directory = 'uploads'): string
// {
//     return $file->store($directory, 'opt_uploads');
// }