<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::where('user_id', Auth::id());

        $data = $wallets->map(function ($wallet) {
            $balance = $wallet->transaction->sum('amount') ?? null;
            $wallet->unsetRelation('transaction');
            return array_merge($wallet->toArray(), [
                'currency_code' => $wallet->currency->code ?? null,
                'balance' => $balance
            ]);
        });
        return response()->json([
            'status' => 'success',
            'message' => 'Get all wallets successful',
            'data' => ['wallets' => $data]
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'currency_code' => 'required|exists:money_lover_currencies,code'
        ]);
        try {
            $currency = Currency::where('code', $data['currency_code'])->firstOrFail();
            $currencyCode = $currency->code;
            $wallet = Wallet::create([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'currency_id' => $currency->id
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Wallet added successful',
                'data' => array_merge($wallet->toArray(), ['currency_code' => $currencyCode])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(string $walletId)
    {
        $wallet = Wallet::find($walletId);
        if (!$wallet) {
            throw new NotFoundHttpException();
        }
        if ($wallet->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }
        $balance = $wallet->transaction->sum('amount') ?? null;
        $wallet->unsetRelation('transaction');
        return response()->json([
            'status' => 'success',
            'message' => 'Get wallet successful',
            'data' => array_merge($wallet->toArray(), [
                'currency_code' => $wallet->currency->code ?? null,
                'balance' => $balance
            ])
        ]);
    }

    public function update(Request $request, string $walletId)
    {
        $wallet = Wallet::find($walletId);
        if (!$wallet) {
            throw new NotFoundHttpException();
        }
        if ($wallet->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'currency_code' => 'sometimes|exists:money_lover_currencies,code'
        ]);
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['currency_code'])) {
            $currency = Currency::where('code', $data['currency_code'])->first();
            if ($currency) {
                $updateData['currency_id'] = $currency->id;
            }
        }
        $wallet->update($updateData);
        $wallet->refresh();
        $finalCurrencyCode = $wallet->currency->code ?? null;
        $walletArray = $wallet->toArray();
        unset($walletArray['currency']);
        return response()->json([
            'status' => 'success',
            'message' => 'Wallet added successful',
            'data' => array_merge($walletArray, ['currency_code' => $finalCurrencyCode])
        ]);
    }
    public function destroy(string $walletId)
    {
        $wallet = Wallet::find($walletId);
        if (!$wallet) {
            throw new NotFoundHttpException();
        }
        if ($wallet->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }
        $wallet->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Wallet deleted successful'
        ]);
    }
}
