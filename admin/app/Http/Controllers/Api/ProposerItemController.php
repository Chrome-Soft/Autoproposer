<?php

namespace App\Http\Controllers\Api;

use App\Interaction;
use App\Proposer;
use App\ProposerItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProposerItemController extends Controller
{
    public function index(Proposer $proposer)
    {
        $items = $proposer->items;
        return response()->json([
            'items' => $items
        ]);
    }

    public function destroy(Proposer $proposer, ProposerItem $proposerItem)
    {
        $this->authorize('update', $proposer);
        $proposerItem->delete();

        return response()->json('OK');
    }
}
