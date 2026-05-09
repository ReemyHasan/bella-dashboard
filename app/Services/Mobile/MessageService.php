<?php

namespace App\Services\Mobile;

use App\Enums\AssignmentType;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\Message;
use App\Models\MessageAssignee;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function list($request)
    {
        $user = auth()->user();

        return Message::query()
            ->with('createdBy')
            ->where(function ($q) use ($user) {

                /*
            |--------------------------------------------------------------------------
            | Messages created by him
            |--------------------------------------------------------------------------
            */
                $q->where(function ($sub) use ($user) {

                    $sub->where('created_by_type', get_class($user))
                        ->where('created_by_id', $user->id);
                })

                    /*
            |--------------------------------------------------------------------------
            | Messages targeting him directly
            |--------------------------------------------------------------------------
            */
                    ->orWhere(function ($sub) use ($user) {

                        $sub->where('target_type', 'marketer')
                            ->whereHas('assignees', function ($a) use ($user) {

                                $a->where('assignee_id', $user->id)
                                    ->where('assignee_type', AppUser::class);
                            });
                    })

                    /*
            |--------------------------------------------------------------------------
            | Messages targeting his team
            |--------------------------------------------------------------------------
            */
                    ->orWhere(function ($sub) use ($user) {

                        $sub->where('target_type', 'team')
                            ->whereHas('assignees', function ($a) use ($user) {

                                $a->where('assignee_id', $user->team_id)
                                    ->where('assignee_type', Team::class);
                            });
                    })

                    /*
            |--------------------------------------------------------------------------
            | Messages targeting his subteam
            |--------------------------------------------------------------------------
            */
                    ->orWhere(function ($sub) use ($user) {

                        $sub->where('target_type', 'sub_team')
                            ->whereHas('assignees', function ($a) use ($user) {

                                $a->where('assignee_id', $user->subteam_id)
                                    ->where('assignee_type', SubTeam::class);
                            });
                    });
            })->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = auth()->user();
        if (!$user->hasRole('Team Manager') && !$user->hasRole('Team Leader')) {
            throw new CustomException('لا يمكن توجيه رسالة إلا من قبل مدير أو مدير فريق');
        }
        return DB::transaction(function () use ($data) {
            $message = Message::create([
                'description' => $data['description'],
                'appears_from' => $data['appears_from'],
                'appears_to' => $data['appears_to'],
                'assignment_type' => AssignmentType::SPECIFIC->value,
                'target_type' => $data['target_type'],
                'created_by_id' => auth()->id(),
                'created_by_type' => get_class(auth()->user()),
            ]);
            if (
                !empty($data['assignees'])
            ) {
                $this->syncAssignees($message, $data);
            }
            $message->load(['assignees.team', 'assignees.marketer', 'assignees.subTeam']);

            return $message;
        });
    }

    public function update(Message $message, array $data)
    {
        $user = auth()->user();
        if (!$user->hasRole('Team Manager') && !$user->hasRole('Team Leader')) {
            throw new CustomException('لا يمكن إضافة زبون جديد إلا من قبل مدير أو مدير فريق');
        }
        if ($message->created_by_type != AppUser::class || $message->created_by_id != $user->id) {
            throw new CustomException('لا يمكن تعديل رسالة إلا من المنشئ له.');
        }
        return DB::transaction(function () use ($message, $data) {
            $message->update([
                'description' => $data['description'],
                'appears_from' => $data['appears_from'],
                'appears_to' => $data['appears_to'],
                'assignment_type' => AssignmentType::SPECIFIC->value,
                'target_type' => $data['target_type'],

            ]);

            $message->assignees()->delete();

            if (
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
        $user = auth()->user();

        $allowed = false;
        if (
            $message->created_by_type === get_class($user) &&
            $message->created_by_id == $user->id
        ) {
            $allowed = true;
        }
        if (
            !$allowed &&
            $message->target_type === 'marketer'
        ) {

            $allowed = $message->assignees()
                ->where('assignee_id', $user->id)
                ->where('assignee_type', AppUser::class)
                ->exists();
        }

        if (
            !$allowed &&
            $message->target_type === 'team' &&
            $user->team_id
        ) {

            $allowed = $message->assignees()
                ->where('assignee_id', $user->team_id)
                ->where('assignee_type', Team::class)
                ->exists();
        }

        if (
            !$allowed &&
            $message->target_type === 'sub_team' &&
            $user->subteam_id
        ) {

            $allowed = $message->assignees()
                ->where('assignee_id', $user->subteam_id)
                ->where('assignee_type', SubTeam::class)
                ->exists();
        }

        /*
    |--------------------------------------------------------------------------
    | ❌ Unauthorized
    |--------------------------------------------------------------------------
    */
        if (!$allowed) {

            throw new CustomException(
                'لا تملك صلاحية عرض هذه الرسالة'
            );
        }
        $message->load(['assignees.team', 'assignees.marketer', 'assignees.subTeam', 'createdBy']);
        return $message;
    }
}
