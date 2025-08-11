<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class InspectionController extends Controller
{
    public function downloadWord($inspectionId)
    {
        $inspection = \App\Models\MedicalInspection::with('medicalHistory')->findOrFail($inspectionId);

        $templatePath = public_path('app/templates/priemniy_osmotr_template.docx');
        $fileName = 'priemniy_osmotr_'.$inspection->medicalHistory->number.'.docx';

        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('date', $inspection->created_at->format('d.m.Y'));
        $templateProcessor->setValue('full_name', $inspection->patient->full_name);
        $templateProcessor->setValue('date_birthday', $inspection->patient->birth_date);
        $templateProcessor->setValue('medical_history', $inspection->medical_history);
        $templateProcessor->setValue('complaints', $inspection->complaints);
        $templateProcessor->setValue('history_life', $inspection->history_life);
        $templateProcessor->setValue('epidemiological_history', $inspection->epidemiological_history);
        $templateProcessor->setValue('objectively', $inspection->objectively);
        $templateProcessor->setValue('local_state', $inspection->local_state);
        $templateProcessor->setValue('admission_diagnosis', $inspection->admission_diagnosis
                                                            ?? $inspection?->mkbClass?->name
                                                            ?? 'Нет');
        $templateProcessor->setValue('recommended', $inspection->recommended);
        $templateProcessor->setValue('doctor_name', $inspection->initialDoctor->name);

        $outputPath = storage_path('app/public/'.$fileName);
        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend();
    }
    public function downloadDepartmentInspection($inspectionId)
    {
        $inspection = \App\Models\DepartmentInspection::with('medicalHistory')->findOrFail($inspectionId);

        $templatePath = public_path('app/templates/otdelniy_osmotr_template.docx');
        $fileName = 'otdelniy_osmotr_'.$inspection->medicalHistory->number.'.docx';

        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('date', $inspection->created_at->format('d.m.Y'));
        $templateProcessor->setValue('full_name', $inspection->patient->full_name);
        $templateProcessor->setValue('date_birthday', $inspection->patient->birth_date);
        $templateProcessor->setValue('medical_history', $inspection->medical_history);
        $templateProcessor->setValue('complaints', $inspection->complaints);
        $templateProcessor->setValue('history_life', $inspection->history_life);
        $templateProcessor->setValue('epidemiological_history', $inspection->epidemiological_history);
        $templateProcessor->setValue('objectively', $inspection->objectively);
        $templateProcessor->setValue('local_state', $inspection->local_state);
        $templateProcessor->setValue('admission_diagnosis', $inspection->admission_diagnosis);
        $templateProcessor->setValue('recommended', $inspection->recommended);
        $templateProcessor->setValue('doctor_name', $inspection->assignedDoctor->name);

        $outputPath = storage_path('app/public/'.$fileName);
        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend();
    }
}
