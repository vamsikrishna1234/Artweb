<<<<<<< Updated upstream
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;


class UsersController extends Controller
{

    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if(auth()->user()->roles->contains(MODERATOR_ROLE)){
            $users = User::with(['roles', 'approved_by'])->whereHas('roles', function ($query) {
                $query->whereIn('id',[USER_ROLE,VENDOR_ROLE]);
            })->get();
        }else{
            $users = User::with(['roles', 'approved_by'])->get();
        }




        return view('admin.users.index', compact('users'));
    }

    public function indexData(Request $r){
        $input=$r->only(['role']);

      //  dd(User::get()->toArray());
        if(auth()->user()->roles->contains(MODERATOR_ROLE)){


                $users = User::with(['roles', 'approved_by'])->whereHas('roles', function ($query)use($input) {
                    if($input['role']!=0 && $input['role']!='0' ){
                        $query->where('id',$input['role']);

                    }else{
                        $query->whereIn('id',[USER_ROLE,VENDOR_ROLE]);
                    }

                })->get();




        }else{

            $users = User::with(['roles', 'approved_by'])->whereHas('roles', function ($query)use($input) {
                if($input['role']!=0 && $input['role']!='0' ) $query->where('id',$input['role']);
            })->get();

        }




        $model = $users;




        return view('admin.users.indexData',compact('users','input'));


    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::all()->pluck('title', 'id');

        $approved_bies = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.users.create', compact('roles', 'approved_bies'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::all()->pluck('title', 'id');

        $approved_bies = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $user->load('roles', 'approved_by');

        return view('admin.users.edit', compact('roles', 'approved_bies', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load('roles', 'approved_by');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        User::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
=======
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;


class UsersController extends Controller
{

    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if(auth()->user()->roles->contains(MODERATOR_ROLE)){
            $users = User::with(['roles', 'approved_by'])->whereHas('roles', function ($query) {
                $query->whereIn('id',[USER_ROLE,VENDOR_ROLE]);
            })->get();
        }else{
            $users = User::with(['roles', 'approved_by'])->get();
        }




        return view('admin.users.index', compact('users'));
    }

    public function indexData(Request $r){
        $input=$r->only(['role']);

      //  dd(User::get()->toArray());
        if(auth()->user()->roles->contains(MODERATOR_ROLE)){


                $users = User::with(['roles', 'approved_by'])->whereHas('roles', function ($query)use($input) {
                    if($input['role']!=0 && $input['role']!='0' ){
                        $query->where('id',$input['role']);

                    }else{
                        $query->whereIn('id',[USER_ROLE,VENDOR_ROLE]);
                    }

                })->get();




        }else{

            $users = User::with(['roles', 'approved_by'])->whereHas('roles', function ($query)use($input) {
                if($input['role']!=0 && $input['role']!='0' ) $query->where('id',$input['role']);
            })->get();

        }




        $model = $users;




        return view('admin.users.indexData',compact('users','input'));


    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::all()->pluck('title', 'id');

        $approved_bies = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.users.create', compact('roles', 'approved_bies'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::all()->pluck('title', 'id');

        $approved_bies = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $user->load('roles', 'approved_by');

        return view('admin.users.edit', compact('roles', 'approved_bies', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load('roles', 'approved_by');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        User::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
>>>>>>> Stashed changes
