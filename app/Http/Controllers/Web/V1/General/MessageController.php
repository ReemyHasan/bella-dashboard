<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\MessageRequest;
use App\Http\Resources\DashUser\MessageResource;
use App\Models\Message;
use App\Services\DashUser\MessageService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MessageController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_messages', only: ['index']),
            new Middleware('permission:view_message_by_id', only: ['show']),
            new Middleware('permission:create_message', only: ['store']),
            new Middleware('permission:update_message', only: ['update']),
            new Middleware('permission:delete_message', only: ['destroy']),

        ];
    }

    public function __construct(private MessageService $messageService) {}

    public function index(Request $request)
    {
        $messages = $this->messageService->list($request);
        return response()->format($this->returnPaginatedResponse($messages, MessageResource::collection($messages)), 'messages.success', 200);
    }

    public function store(MessageRequest $request)
    {
        $message = $this->messageService->create($request->validated());
        return response()->format(new MessageResource($message),  __('messages.created_successfully',  ['item' => __('constants.message')]), 201);
    }

    public function update(MessageRequest $request, Message $message)
    {
        $message = $this->messageService->update($message, $request->validated());
        return response()->format(new MessageResource($message),  __('messages.updated_successfully',  ['item' => __('constants.message')]), 200);
    }
    public function show(Message $message)
    {
        $message = $this->messageService->show($message);
        return response()->format(new MessageResource($message), 'messages.success', 200);
    }
    public function destroy(Message $message)
    {
        $returned = $this->messageService->delete($message);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.message')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.message')]), 200);
    }
}
