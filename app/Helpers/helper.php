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
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}

function showDateTime($date, $format = 'Y-m-d h:i A')
{
    $locale = (request()->hasHeader('Accept-Language')) ? request()->header('Accept-Language') : config('app.fallback_locale');
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

function arabicFix($text)
{
    // Reverse text safely
    $text = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $text = array_reverse($text);
    $text = implode('', $text);

    // Fix numbers direction (keep numbers LTR)
    $text = preg_replace_callback('/[0-9]+/', function ($matches) {
        return strrev($matches[0]);
    }, $text);

    return $text;
}