<<<<<<< Updated upstream
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceToApproveController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('service_to_approve_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.serviceToApproves.index');
    }
}
=======
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceToApproveController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('service_to_approve_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.serviceToApproves.index');
    }
}
>>>>>>> Stashed changes
