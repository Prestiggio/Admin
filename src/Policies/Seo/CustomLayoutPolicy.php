<?php
namespace Ry\Admin\Policies\Seo;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CustomLayoutPolicy
{
    use HandlesAuthorization;

    public function create(User $user) {
        $team = Team::find(4);
        if($user->hasTeamRole($team, 'admin')) {
            return Response::allow();
        }
        return Response::deny(__('You are not allowed to add layout'));
    }
}