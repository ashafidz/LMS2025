<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ProfilingComponent;
use Illuminate\Http\Request;

class ProfilingComponentController extends Controller
{
    public function index()
    {
        $components = ProfilingComponent::orderBy('order')->get();
        return view('superadmin.profiling.index', compact('components'));
    }

    public function show(ProfilingComponent $component)
    {
        $component->load(['dimensions.questions' => function($q) {
            $q->orderBy('order');
        }]);
        
        return view('superadmin.profiling.show', compact('component'));
    }
}
