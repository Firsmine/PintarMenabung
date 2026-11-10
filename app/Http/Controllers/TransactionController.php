<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1',
            'month' => 'sometimes|integer|min:1|max:12',
            'year' => 'sometimes|integer|min:1',
        ]);
        $query = Transaction::with(['wallet', 'category'])
            ->whereHas('wallet', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }
        $transactions = $query
            ->orderBy('date', 'desc')
            ->paginate($request->input('per_page', 25));
        return response()->json([
            $transactions
        ], 200);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => 'required|integer|exists:money_lover_wallets,id',
            'category_id' => 'required|integer|exists:money_lover_categories,id',
            'amount' => 'required|integer|min:1',
            'date' => 'required|date|date_format:Y-m-d',
            'note' => 'nullable|string'
        ]);

        $transaction = Transaction::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction added successful',
            'data' => $transaction
        ]);
    }
    public function destroy(string $transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if (!$transaction) {
            throw new NotFoundHttpException();
        }
        $wallet = Wallet::find($transaction->wallet_id);
        if (!$wallet || $wallet->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException('Forbidden access');
        }

        $transaction->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successful'
        ]);
    }
}
