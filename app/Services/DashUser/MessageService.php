<?php

namespace App\Services\DashUser;

use App\Enums\NotificationType;
use App\Enums\PaginationEnum;
use App\Events\NotificationEvent;
use App\Models\Message;
use App\Models\MessageAssignee;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function list($request)
    {
        return Message::with('createdBy')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $message = DB::transaction(function () use ($data) {
            $message = Message::create([
                'description' => $data['description'],
                'appears_from' => $data['appears_from'],
                'appears_to' => $data['appears_to'],
                'assignment_type' => $data['assignment_type'],
                'target_type' => $data['target_type'],
                'created_by_id' => auth()->id(),
                'created_by_type' => get_class(auth()->user()),
            ]);
            if (
                $data['assignment_type'] === 'specific' &&
                !empty($data['assignees'])
            ) {
                $this->syncAssignees($message, $data);
            }
            $message->load(['assignees.team', 'assignees.marketer', 'assignees.subTeam']);

            return $message;
        });
        // event(new NotificationEvent(
        //     type: NotificationType::MESSAGE,
        //     data: [
        //         'message' => $message,
        //     ]
        // ));
        return $message;
    }

    public function update(Message $message, array $data)
    {
        return DB::transaction(function () use ($message, $data) {
            $message->update([
                'description' => $data['description'],
                'appears_from' => $data['appears_from'],
                'appears_to' => $data['appears_to'],
                'assignment_type' => $data['assignment_type'],
                'target_type' => $data['target_type'],

            ]);

            $message->assignees()->delete();

            if (
                $data['assignment_type'] === 'specific' &&
                !empty($data['assignees'])
            ) {
                $this->syncAssignees($message, $data);
            }
            $message->load(['assignees.team', 'assignees.marketer', 'assignees.subTeam']);

            return $message;
        });
    }

    private function syncAssignees(Message $message, array $data): void
    {
        $column = match ($data['target_type']) {
            'team' => 'team_id',
            'sub_team' => 'sub_team_id',
            'marketer' => 'marketer_id',
        };

        $rows = collect($data['assignees'])
            ->unique()
            ->map(fn($id) => [
                'message_id' => $message->id,
                $column => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();

        MessageAssignee::insert($rows);
    }
    public function show(Message $message)
    {
        $message->load(['assignees.team', 'assignees.marketer', 'assignees.subTeam', 'createdBy']);
        return $message;
    }

    public function delete(Message $message)
    {
        return $message->delete();
    }
}
