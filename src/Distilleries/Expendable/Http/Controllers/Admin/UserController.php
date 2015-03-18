<?php namespace Distilleries\Expendable\Http\Controllers\Admin;

use Distilleries\Expendable\Contracts\LayoutManagerContract;
use Distilleries\Expendable\Datatables\User\UserDatatable;
use Distilleries\Expendable\Formatter\Message;
use Distilleries\Expendable\Forms\User\UserForm;
use Distilleries\Expendable\Http\Controllers\Admin\Base\BaseComponent;
use Distilleries\Expendable\Models\User;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;

class UserController extends BaseComponent {


    public function __construct(UserDatatable $datatable, UserForm $form, User $model, LayoutManagerContract $layoutManager)
    {
        parent::__construct($model, $layoutManager);
        $this->datatable = $datatable;
        $this->form      = $form;
    }

    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------


    public function getProfile(Guard $auth)
    {
        return $this->getEdit($auth->user()->getKey());
    }

    // ------------------------------------------------------------------------------------------------

    public function postProfile(Request $request, Guard $auth)
    {
        if ($auth->user()->getAuthIdentifier() == $request->get($this->model->getKeyName()))
        {
            $this->postEdit($request);

            return $this->getProfile($auth);
        }

        abort(403, trans('permission-util::errors.unthorized'));
    }

    // ------------------------------------------------------------------------------------------------

    public function postSearchWithRole(Request $request)
    {
        $query = $this->model->where('role_id', '=', $request->get('role'));
        $this->postSearch($request, $query);

        return $this->postSearch($request, $query);
    }

}