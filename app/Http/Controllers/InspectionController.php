<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class InspectionController extends Controller
{
    public function downloadWord($inspectionId)
{
    $inspection = \App\Models\MedicalInspection::with('medicalHistory')->findOrFail($inspectionId);

    $templatePath = public_path('app/templates/inspection_template.docx');
    $fileName = 'inspection_'.$inspection->id.'.docx';

    $templateProcessor = new TemplateProcessor($templatePath);

    $templateProcessor->setValue('history', '№'.$inspection->medicalHistory->id . ' - ' . $inspection->medicalHistory->created_at->format('d.m.Y H:i'));
    $templateProcessor->setValue('date', $inspection->created_at->format('d.m.Y H:i'));

    $outputPath = storage_path('app/public/'.$fileName);
    $templateProcessor->saveAs($outputPath);

    return response()->download($outputPath)->deleteFileAfterSend();
}
}
