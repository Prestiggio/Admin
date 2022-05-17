<?php
namespace Ry\Admin\Policies\Seo;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CustomLayoutPolicy
{
    use HandlesAuthorization;

    public function create(User $user) {
        return Response::deny(__('You are not allowed to add layout'));
    }
}