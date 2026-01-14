<?php

namespace App\Http\Controllers\CustomizedPackages;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request, $project_id)
    {
        $project = Project::findOrFail($project_id);

        // For now, we'll use a default package structure
        // In a real implementation, this would come from a packages table
        $package = [
            'id' => 1,
            'name' => 'NCD Appointments Review Package',
            'description' => 'Advanced appointment management and review system for NCD projects',
            'price' => 300,
            'currency' => 'USD',
            'features' => [
                'Advanced appointment scheduling',
                'Patient review and analytics',
                'Custom report generation',
                'Priority support',
                'Export capabilities',
                'Real-time data synchronization'
            ],
            'trial_days' => 14
        ];

        return Inertia::render('Customizations/Payment', [
            'project' => $project,
            'package' => $package
        ]);
    }
}