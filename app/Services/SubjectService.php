<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\SubjectGroup;
use App\Models\UserSubjectGroup;
use App\Models\UserSubjectGroupSubject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SubjectService
{

    public $user;

    public function __construct($user = null)
    {
        if ($user) {
            $this->user = $user;
        }
    }

    protected function isAdmin(): bool
    {
        return $this->user && $this->user->hasRole('admin');
    }

    public function getUserSubjectGroups()
    {
        if (!$this->user) return collect();
        if ($this->isAdmin()) {
            return UserSubjectGroup::with(['subjects', 'group'])->get();
        }
        return $this->user->groups()->select('id', 'subject_group_id')->with(['subjects'])->withWhereHas('group')->get();
    }

    public function getSubjectsByUserRole()
    {
        if (!$this->user) return collect();

        $query = UserSubjectGroupSubject::select('id', 'user_subject_group_id', 'subject_id', 'hour_rate');

        if ($this->isAdmin()) {
            // Allow all
        } elseif ($this->user->role === 'tutor') {
            $query->whereHas('userSubjectGroup', function ($query) {
                $query->select('id');
                $query->whereUserId($this->user->id);
            });
        } elseif ($this->user->role === 'student') {
            $query->whereHas('slots', function ($query) {
                $query->select('id', 'user_subject_group_subject_id');
                $query->whereHas('bookings', function ($query) {
                    $query->select('id');
                    $query->whereStudentId($this->user->id);
                });
            });
        }

        $subjects = $query->with(['group:subject_groups.id,name', 'subject:id,name'])->get()
            ->groupBy('user_subject_group_id')
            ->map(function ($group) {
                return [
                    'group_name' => $group->first()->group ? $group->first()->group->name : null,
                    'subjects' => $group->map(function ($item) {
                        return [
                            'id'           => $item->id,
                            'subject_name' => $item->subject ? $item->subject->name : null,
                            'hour_rate'    => $item->hour_rate,
                        ];
                    })
                ];
            });

        return $subjects;
    }

    public function getSubjectGroups()
    {
        return SubjectGroup::get(['id', 'name']);
    }

    public function getSubjects()
    {
        return Subject::get(['id', 'name']);
    }

    public function setSubjectGroups(&$groupIds)
    {
        if (!$this->user) return [];

        $groups = $this->isAdmin()
            ? UserSubjectGroup::get()
            : $this->user->groups()->get();

        $data = [];
        foreach ($groups as $group) {
            if (in_array($group->subject_group_id, $groupIds)) {
                unset($groupIds[array_search($group->subject_group_id, $groupIds)]);
            } else {
                $isDeleted = $this->deleteUserSubjectGroup($group->id);
                if (!$isDeleted) {
                    unset($groupIds[array_search($group->subject_group_id, $groupIds)]);
                }
            }
        }
        foreach ($groupIds as $id) {
            $data[] = [
                'user_id' => $this->user->id,
                'subject_group_id' => $id
            ];
        }

        return $this->user->groups()->createMany($data);
    }

    public function getUserSubjectGrouaps()
    {
        if (!$this->user) return collect();

        return $this->isAdmin()
            ? UserSubjectGroup::pluck('subject_group_id')
            : $this->user->groups()->pluck('subject_group_id');
    }

    public function setUserSubjectGroup($subjectGroup): void
    {
        if (!$this->user || !$subjectGroup) return;

        $group = $this->isAdmin()
            ? UserSubjectGroup::firstOrCreate(['user_id' => $this->user->id, 'subject_group_id' => $subjectGroup['subject_group_id']])
            : $this->user->groups()->firstOrCreate(['subject_group_id' => $subjectGroup['subject_group_id']]);

        foreach ($subjectGroup['subject_id'] as $subj) {
            UserSubjectGroupSubject::updateOrCreate(['user_subject_group_id' => $group->id, 'subject_id' => $subj]);
        }
    }

    public function getUserSubjectGroup($subjectGroupId)
    {
        if (!$this->user) return [];

        $groupSubjects = $this->isAdmin()
            ? UserSubjectGroup::where('subject_group_id', $subjectGroupId)->first()
            : $this->user->groups()->whereSubjectGroupId($subjectGroupId)->first();

        $returnData['subject_group_id'] = $subjectGroupId;
        $returnData['group'] = $groupSubjects->group->name;
        foreach ($groupSubjects->subjects as $subject) {
            $returnData['subject_id'][] = $subject->id;
        }
        return $returnData;
    }

    public function getUserGroupSubjects($groupId)
    {
        $group = $this->isAdmin()
            ? UserSubjectGroup::whereId($groupId)->first()
            : $this->user->groups()->whereId($groupId)->first();

        if ($group) {
            return $group?->subjects()?->get()?->pluck('name','id')?->toArray() ?? [];
        }
        return [];
    }

    public function getUserGroupSubject($pivotId): UserSubjectGroupSubject
    {
        return UserSubjectGroupSubject::whereId($pivotId)->first();
    }

    public function setUserSubject($id, $subject): ?UserSubjectGroupSubject
    {
        $group = $this->isAdmin()
            ? UserSubjectGroup::whereId($subject['user_subject_group_id'])->first()
            : $this->user?->groups()?->whereId($subject['user_subject_group_id'])->first();

        if ($group) {
            return $group->userSubjects()->updateOrCreate(['id' => $id], $subject);
        }

        return null;
    }


    public function deteletSubject($userGroupId, $userSubjectId)
    {
        $group = $this->isAdmin()
            ? UserSubjectGroup::whereId($userGroupId)->first()
            : $this->user->groups()?->whereId($userGroupId)->first();

        if ($group) {
            $groupSubject = $group->userSubjects()
                ->whereId($userSubjectId)
                ->whereDoesntHave('slots');

            if ($groupSubject) {
                return $groupSubject->delete();
            }
        }
        return null;
    }

    public function updateSubjectSortOrder($subjectList)
    {
        foreach ($subjectList as $group) {
            foreach ($group['items'] as $subject) {
                UserSubjectGroupSubject::find($subject['value'])->update(['sort_order' => $subject['order']]);
            }
        }
    }

    public function updateSubjectGroupSortOrder($groupList)
    {
        foreach ($groupList as $group) {
            UserSubjectGroup::find($group['value'])->update(['sort_order' => $group['order']]);
        }
    }

    public function deleteSubject($pivotId): void
    {
        UserSubjectGroupSubject::whereId($pivotId)->delete();
    }

    public function deleteUserSubjectGroup($groupId): bool
    {
        if (!$this->user) return false;

        $group = $this->isAdmin()
            ? UserSubjectGroup::whereId($groupId)->whereDoesntHave('userSubjects.slots')->first()
            : $this->user->groups()->whereId($groupId)->whereDoesntHave('userSubjects.slots')->first();

        if ($group) {
            $group->userSubjects()->delete();
            $group->delete();
            return true;
        }
        return false;
    }
}
