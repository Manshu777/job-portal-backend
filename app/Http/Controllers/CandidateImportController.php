<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CandidateTemplateExport;

class CandidateImportController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new CandidateTemplateExport, 'candidate_template.xlsx');
    }
}
