<?php

namespace App\Http\Controllers\Web\V1\General;


use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\UpdateSettingRequest;
use App\Models\Setting;
use App\Traits\HandlesImageUpload;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller implements HasMiddleware
{
    use HandlesImageUpload;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:clear_caches', only: ['clearCache']),
            new Middleware('permission:view_general_settings', only: ['all']),
            new Middleware('permission:update_general_settings', only: ['update']),

        ];
    }

    public function clearCache()
    {
        // Artisan::call('cache:clear');
        // Artisan::call('config:clear');
        // Artisan::call('route:clear');
        // Artisan::call('view:clear');
        Artisan::call('optimize:clear');

        return response()->format(null,  "تم مسح كل الكاشات.", 200);
    }


    public function all()
    {
        $data = Cache::rememberForever('settings', function () {
            return Setting::pluck('value', 'field');
        });

        $data = Cache::rememberForever('settings', function () {
            return Setting::pluck('value', 'field')->toArray();
        });

        if (!empty($data['offer_general_image'])) {
            $data['offer_general_image'] = getPublicFileUrl($data['offer_general_image']);
        }

        return response()->format($data, "تم جلب الإعدادات بنجاح.", 200);
    }

    public function update(UpdateSettingRequest $request)
    {
        $data = $request->validated();

        foreach ($data as $field => $value) {
            $newValue = $value;
            if ($field == 'offer_general_image') {
                $newValue = $this->uploadImage($value, 'general_settings');
            }
            Setting::where('field', $field)
                ->update([
                    'value' => $newValue
                ]);
        }

        Cache::forget('settings');

        $settings = Cache::rememberForever('settings', function () {
            return Setting::pluck('value', 'field');
        });

        return response()->format(
            $this->formatSettings($settings),
            __('messages.updated_successfully', ['item' => __('constants.general_settings')]),
            200
        );
    }

    private function formatSettings($settings)
    {
        if (!empty($settings['offer_general_image'])) {
            $settings['offer_general_image'] = getPublicFileUrl($settings['offer_general_image']);
        }

        return $settings;
    }
}
