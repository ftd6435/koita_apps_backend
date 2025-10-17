<?php

namespace App\Modules\Fixing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fixing\Models\Fixing;
use App\Modules\Fixing\Requests\StoreFixingRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FixingController extends Controller
{
    use ApiResponses;

    public function index()
    {
        // Code here...
    }

    public function show(string $id)
    {
        // Code here...
    }

    public function store(StoreFixingRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        DB::beginTransaction();

        try {
            // Code here...

            if (!empty($request->barres)) {
                // Code here...
            }

            $fixing = Fixing::create();

            DB::commit();

            return $this->successResponse($fixing, 'Fxing ajouté avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse('Erreur lors de l\'ajout du fixing : '.$e->getMessage(), 500);
        }
    }

    public function update()
    {
        // Code here...
    }

    public function destroy(string $id)
    {
        // Code here...
    }

    public function restore(string $id)
    {
        // Code here...
    }

    public function forceDelete(string $id)
    {
        // Code here...
    }
}
