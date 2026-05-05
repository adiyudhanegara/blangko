<?php

namespace App\Http\Controllers;

use App\Exports\SubmissionExport;
use App\Models\FormRelease;
use Maatwebsite\Excel\Facades\Excel;

class AdminExportController extends Controller
{
    public function export(FormRelease $release)
    {
        $filename = str($release->name)->slug()->append('-submissions-' . now()->format('Ymd'))->append('.xlsx');

        return Excel::download(new SubmissionExport($release), $filename);
    }
}
