<?php

namespace App\Services\Mobile;

use App\Enums\AssignmentType;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\Message;
use App\Models\MessageAssignee;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function list($request)
    {
        $user = auth()->user();

        return Message::query()
            ->with('createdBy')

            ->where(function ($q) use ($user) {

                $q->where(function ($sub) use ($user) {

                    $sub->where('created_by_type', get_class($user))
                        ->where('created_by_id', $user->id);
                })


                    ->orWhere(function ($sub) use ($user) {

                        $sub->where(function ($dateQuery) {

                            $now = now();

                            $dateQuery
                                ->where(function ($q) use ($now) {

                                    // between from/to
                                    $q->whereNotNull('appears_from')
                                        ->whereNotNull('appears_to')
                                        ->where('appears_from', '<=', $now)
                                        ->where('appears_to', '>=', $now);
                                })

                                ->orWhere(function ($q) use ($now) {

                                    // only from
                                    $q->whereNotNull('appears_from')
                                        ->whereNull('appears_to')
                                        ->where('appears_from', '<=', $now);
                                })

                                ->orWhere(function ($q) use ($now) {

                                    // only to
                                    $q->whereNull('appears_from')
                                        ->whereNotNull('appears_to')
                                        ->where('appears_to', '>=', $now);
                                })

                                ->orWhere(function ($q) {

                                    // always visible if both null
                                    $q->whereNull('appears_from')
                                        ->whereNull('appears_to');
                                });
                        })
                            ->where(function ($targetQuery) use ($user) {

                                if ($user->is_warehouse_man) {

                                    $targetQuery
                                        // ALL warehouse keepers
                                        ->where(function ($q) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::ALL->value
                                            )
                                                ->where(
                                                    'target_type',
                                                    'warehouse_keeper'
                                                );
                                        })

                                        // SPECIFIC warehouse keeper
                                        ->orWhere(function ($q) use ($user) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::SPECIFIC->value
                                            )
                                                ->where(
                                                    'target_type',
                                                    'warehouse_keeper'
                                                )
                                                ->whereHas('assignees', function ($a) use ($user) {
                                                    $a->where(
                                                        'marketer_id',
                                                        $user->id
                                                    );
                                                });
                                        });

                                    return;
                                }

                                /*
                    |--------------------------------------------------------------------------
                    | TEAM MANAGER
                    |--------------------------------------------------------------------------
                    */
                                if ($user->hasRole('Team Manager')) {

                                    $targetQuery
                                        // ALL team
                                        ->where(function ($q) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::ALL->value
                                            )
                                                ->where('target_type', 'team');
                                        })

                                        // SPECIFIC team
                                        ->orWhere(function ($q) use ($user) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::SPECIFIC->value
                                            )
                                                ->where('target_type', 'team')
                                                ->whereHas('assignees', function ($a) use ($user) {
                                                    $a->where(
                                                        'team_id',
                                                        $user->team_id
                                                    );
                                                });
                                        })

                                        // SPECIFIC marketer
                                        ->orWhere(function ($q) use ($user) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::SPECIFIC->value
                                            )
                                                ->where('target_type', 'marketer')
                                                ->whereHas('assignees.marketer', function ($a) use ($user) {
                                                    $a->where('team_id', $user->team_id);
                                                });
                                        });

                                    return;
                                }

                                /*
                    |--------------------------------------------------------------------------
                    | TEAM LEADER
                    |--------------------------------------------------------------------------
                    */
                                if ($user->hasRole('Team Leader')) {

                                    $targetQuery
                                        // ALL sub-team
                                        ->where(function ($q) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::ALL->value
                                            )
                                                ->where('target_type', 'sub_team');
                                        })

                                        // SPECIFIC sub-team
                                        ->orWhere(function ($q) use ($user) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::SPECIFIC->value
                                            )
                                                ->where('target_type', 'sub_team')
                                                ->whereHas('assignees', function ($a) use ($user) {
                                                    $a->where(
                                                        'sub_team_id',
                                                        $user->subteam_id
                                                    );
                                                });
                                        })

                                        // SPECIFIC marketer
                                        ->orWhere(function ($q) use ($user) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::SPECIFIC->value
                                            )
                                                ->where('target_type', 'marketer')
                                                ->whereHas('assignees.marketer', function ($a) use ($user) {
                                                    $a->where(
                                                        'subteam_id',
                                                        $user->subteam_id
                                                    );
                                                });
                                        });

                                    return;
                                }

                                /*
                    |--------------------------------------------------------------------------
                    | MARKETER
                    |--------------------------------------------------------------------------
                    */
                                if ($user->hasRole('Marketer')) {

                                    $targetQuery
                                        // ALL marketers
                                        ->where(function ($q) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::ALL->value
                                            )
                                                ->where('target_type', 'marketer');
                                        })

                                        // SPECIFIC marketer
                                        ->orWhere(function ($q) use ($user) {
                                            $q->where(
                                                'assignment_type',
                                                AssignmentType::SPECIFIC->value
                                            )
                                                ->where('target_type', 'marketer')
                                                ->whereHas('assignees', function ($a) use ($user) {
                                                    $a->where(
                                                        'marketer_id',
                                                        $user->id
                                                    );
                                                });
                                        });

                                    return;
                                }

                                // No matching user type
                                $targetQuery->whereRaw('1 = 0');
                            });
                    });
            })
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = auth()->user();
        if (!$user->hasRole('Team Manager') && !$user->hasRole('Team Leader')) {
            throw new CustomException('لا يمكن توجيه رسالة إلا من قبل مدير أو مدير فريق');
        }
        $message = DB::transaction(function () use ($data) {
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

        /*
        |--------------------------------------------------------------------------
        | ALL
        |--------------------------------------------------------------------------
        */
        if (!$allowed && $message->assignment_type === AssignmentType::ALL->value) {

            if (
                $user->is_warehouse_man &&
                $message->target_type === 'warehouse_keeper'
            ) {
                $allowed = true;
            } elseif (
                $user->hasRole('Marketer') &&  $message->target_type === 'marketer'
            ) {
                $allowed = true;
            } elseif (
                $user->team_id &&  $message->target_type === 'team'
            ) {
                $allowed = true;
            } elseif (
                $user->subteam_id && $message->target_type === 'sub_team'
            ) {
                $allowed = true;
            }
        }
        if (
            !$allowed &&
            $message->assignment_type === AssignmentType::SPECIFIC->value
        ) {

            if (
                $user->is_warehouse_man &&
                $message->target_type === 'warehouse_keeper'
            ) {
                $allowed = $message->assignees()
                    ->where('marketer_id', $user->id)
                    ->exists();
            } elseif (
                $user->hasRole('Marketer') &&
                $message->target_type === 'marketer'
            ) {
                $allowed = $message->assignees()
                    ->where('marketer_id', $user->id)
                    ->exists();
            } elseif (
                $user->hasRole('Team Manager') &&
                $message->target_type === 'team'
            ) {
                $allowed = $message->assignees()
                    ->where('team_id', $user->team_id)
                    ->exists();
            } elseif (
                $user->hasRole('Team Leader') &&
                $message->target_type === 'sub_team'
            ) {
                $allowed = $message->assignees()
                    ->where('sub_team_id', $user->subteam_id)
                    ->exists();
            }
        }

        if (!$allowed) {

            throw new CustomException(
                'لا تملك صلاحية عرض هذه الرسالة'
            );
        }
        $message->load(['assignees.team', 'assignees.marketer', 'assignees.subTeam', 'createdBy']);
        return $message;
    }
}
