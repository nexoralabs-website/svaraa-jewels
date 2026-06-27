<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService) {}

    public function index(): View
    {
        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.checkout.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        Address::create(array_merge($validated, ['user_id' => Auth::id()]));

        $request->session()->flash('success', 'Address saved successfully!');

        return back()->with('address_saved', true);
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($address);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        $request->session()->flash('success', 'Address updated successfully!');

        return back()->with('address_saved', true);
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($address);

        $isDefault = $address->is_default;
        $address->delete();

        if ($isDefault) {
            $newDefault = Address::where('user_id', Auth::id())->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Address deleted.']);
        }

        $request->session()->flash('success', 'Address deleted successfully!');

        return back();
    }

    public function setDefault(Address $address)
    {
        $this->authorizeAddress($address);

        Address::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    private function authorizeAddress(Address $address): void
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
