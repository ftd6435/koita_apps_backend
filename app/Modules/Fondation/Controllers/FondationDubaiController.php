<?php

namespace App\Modules\Fondation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fondation\Requests\UpdateDubaiCorrectionRequest;
use App\Modules\Fondation\Services\FondationDubaiService;

class FondationDubaiController extends Controller
{
    protected FondationDubaiService $fondationDubaiService;

    public function __construct(FondationDubaiService $fondationDubaiService)
    {
        $this->fondationDubaiService = $fondationDubaiService;
    }

    public function updateCorrections(UpdateDubaiCorrectionRequest $request)
    {
        return $this->fondationDubaiService->updateCorrections($request->validated());
    }
}
