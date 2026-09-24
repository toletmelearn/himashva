<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    protected function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'country' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['user_id'] = Auth::id();
        $data['country'] = $data['country'] ?? 'India';
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        Address::create($data);

        return back()->with('success', 'Address added.');
    }

    public function update(Request $request, string $id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validate($this->rules());
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('success', 'Address updated.');
    }

    public function destroy(string $id)
    {
        Address::where('user_id', Auth::id())->findOrFail($id)->delete();

        return back()->with('success', 'Address removed.');
    }
}
