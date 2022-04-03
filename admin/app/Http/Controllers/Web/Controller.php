<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller as BaseController;
use App\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    abstract protected function validateRequest(Model $model = null);

    public function storeBase(\Closure $createOperation, $redirectUrl = null)
    {
        $values = $this->validateRequest();

        try {
            $model = $createOperation($values);
            return redirect($redirectUrl ?? $model->path())
                ->with('flash', 'Sikeres létrehozás');
        } catch (\Exception $e) {
            Log::error($e);

            return back()
                ->withErrors(['error' => 'Ismeretlen hiba történt'])
                ->withInput();
        }
    }

    public function updateBase(Model $model, \Closure $updateOperation, $parentModel = null)
    {
        $this->authorize('update', $parentModel ?? $model);
        $values = $this->validateRequest($model);

        try {
            $modifiedValues = $updateOperation($values);
            $model->update($modifiedValues);

            return redirect($parentModel ? $parentModel->path() : $model->path())
                ->with('flash', 'Sikeres szerkesztés');
        } catch (\Exception $e) {
            Log::error($e);

            return back()
                ->withErrors(['error' => 'Ismeretlen hiba történt'])
                ->withInput();
        }
    }
}
