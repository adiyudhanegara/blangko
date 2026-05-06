<?php

namespace App\Http\Controllers;

use App\Exports\ReleaseSetExport;
use App\Exports\SubmissionExport;
use App\Models\FormRelease;
use App\Models\ReleaseSet;
use Maatwebsite\Excel\Facades\Excel;

class AdminExportController extends Controller
{
    public function export(FormRelease $release)
    {
        $release->load(['form', 'releaseSet']);
        $base     = $release->form?->title ?? $release->releaseSet?->name ?? 'export';
        $filename = str($base)->slug()->append('-submissions-' . now()->format('Ymd'))->append('.xlsx');

        return Excel::download(new SubmissionExport($release), $filename);
    }

    public function exportReleaseSet(ReleaseSet $releaseSet)
    {
        $filename = str($releaseSet->name)->slug()
            ->append('-export-' . now()->format('Ymd'))
            ->append('.xlsx');

        return Excel::download(new ReleaseSetExport($releaseSet), $filename);
    }
}
