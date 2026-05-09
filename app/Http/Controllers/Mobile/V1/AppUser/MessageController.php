<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\General\MessageRequest;
use App\Http\Resources\Mobile\MessageResource;
use App\Models\Message;
use App\Services\Mobile\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{


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
}
