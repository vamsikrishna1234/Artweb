<<<<<<< Updated upstream
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServicesForUserController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('services_for_user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.servicesForUsers.index');
    }
}
=======
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServicesForUserController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('services_for_user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.servicesForUsers.index');
    }
}
>>>>>>> Stashed changes
