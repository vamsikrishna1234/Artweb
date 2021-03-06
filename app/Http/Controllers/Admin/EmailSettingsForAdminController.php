<<<<<<< Updated upstream
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmailSettingsForAdminRequest;
use App\Models\EmailSettingsForAdmin;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailSettingsForAdminController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('email_settings_for_admin_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $emailSettingsForAdmins = EmailSettingsForAdmin::all();

        return view('admin.emailSettingsForAdmins.index', compact('emailSettingsForAdmins'));
    }

    public function edit(EmailSettingsForAdmin $emailSettingsForAdmin)
    {
        abort_if(Gate::denies('email_settings_for_admin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.emailSettingsForAdmins.edit', compact('emailSettingsForAdmin'));
    }

    public function update(UpdateEmailSettingsForAdminRequest $request, EmailSettingsForAdmin $emailSettingsForAdmin)
    {
        $emailSettingsForAdmin->update($request->all());

        return redirect()->route('admin.email-settings-for-admins.index');
    }

    public function show(EmailSettingsForAdmin $emailSettingsForAdmin)
    {
        abort_if(Gate::denies('email_settings_for_admin_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.emailSettingsForAdmins.show', compact('emailSettingsForAdmin'));
    }
}
=======
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmailSettingsForAdminRequest;
use App\Models\EmailSettingsForAdmin;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailSettingsForAdminController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('email_settings_for_admin_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $emailSettingsForAdmins = EmailSettingsForAdmin::all();

        return view('admin.emailSettingsForAdmins.index', compact('emailSettingsForAdmins'));
    }

    public function edit(EmailSettingsForAdmin $emailSettingsForAdmin)
    {
        abort_if(Gate::denies('email_settings_for_admin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.emailSettingsForAdmins.edit', compact('emailSettingsForAdmin'));
    }

    public function update(UpdateEmailSettingsForAdminRequest $request, EmailSettingsForAdmin $emailSettingsForAdmin)
    {
        $emailSettingsForAdmin->update($request->all());

        return redirect()->route('admin.email-settings-for-admins.index');
    }

    public function show(EmailSettingsForAdmin $emailSettingsForAdmin)
    {
        abort_if(Gate::denies('email_settings_for_admin_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.emailSettingsForAdmins.show', compact('emailSettingsForAdmin'));
    }
}
>>>>>>> Stashed changes
