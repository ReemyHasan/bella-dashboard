<?php

namespace App\Http\Controllers\Shared\V1;

use App\Enums\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // $user->load('notifications');
        $notifications = $user->notifications()
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);

        return response()->format($this->returnPaginatedResponse($notifications, NotificationResource::collection($notifications)), 'messages.success', 200);
    }
    public function markAsRead($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->whereNull("read_at")->where('id', $id)->firstOrFail();
        $notification->read_at = now();
        $notification->save();
        return response()->format(null, 'تم تغيير حالة الإشعار إلى مقروء', 200);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $client = auth()->user();
        $client->update(['fcm_token' => $request->fcm_token]);

        return response()->format(null, 'messages.success', 200);
    }
}
