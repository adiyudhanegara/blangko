<?php

namespace App\Http\Controllers;

use App\Exports\FormImportTemplateExport;
use App\Exports\ReleaseSetExport;
use App\Exports\SubmissionExport;
use App\Models\Answer;
use App\Models\FormRelease;
use App\Models\ReleaseSet;
use Illuminate\Support\Facades\Storage;
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

    public function formImportTemplate()
    {
        return Excel::download(new FormImportTemplateExport(), 'form-import-template.xlsx');
    }

    public function serveFile(Answer $answer, int $index = -1)
    {
        if ($index >= 0 && $answer->file_paths) {
            $file = $answer->file_paths[$index] ?? abort(404);
            return Storage::disk('local')->response($file['path'], $file['original_name']);
        }

        if ($answer->file_path) {
            return Storage::disk('local')->response(
                $answer->file_path,
                $answer->file_original_name ?? basename($answer->file_path),
            );
        }

        abort(404);
    }
}
