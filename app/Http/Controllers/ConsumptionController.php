<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use Illuminate\Http\Request;

class ConsumptionController extends Controller
{
    /**
     * Display the consumption history page.
     */
    public function history(Request $request)
    {
        $userId = $request->user()->id;
        
        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to   = $request->query('to', now()->toDateString());
        
        $query = Consumption::where('user_id', $userId)
            ->with(['medicine', 'schedule'])
            ->whereBetween('consumed_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // Optional filtering by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optional filtering by medicine name
        if ($request->filled('search')) {
            $query->whereHas('medicine', function($q) use ($request) {
                $q->where('medicine_name', 'like', '%' . $request->search . '%');
            });
        }

        $consumptions = $query->orderByDesc('consumed_at')->paginate(15);
        $consumptions->appends($request->all());

        $adherenceRate = Consumption::adherenceRate($userId, $from . ' 00:00:00', $to . ' 23:59:59');

        return view('consumptions.history', compact('consumptions', 'adherenceRate', 'from', 'to'));
    }
}
