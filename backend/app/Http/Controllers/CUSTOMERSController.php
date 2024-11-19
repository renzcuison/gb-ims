<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CUSTOMERSController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        if ($customers->count() > 0) {
            return response()->json([
                'status' => 200,
                'customers' => $customers
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found.'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:customers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Customer added successfully.',
            'customer' => $customer
        ], 200);
    }

    public function show($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            return response()->json([
                'status' => 200,
                'customer' => $customer
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Customer not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:customers,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $customer = Customer::find($id);
        if ($customer) {
            $customer->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Customer updated successfully.',
                'customer' => $customer
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Customer not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $customer->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Customer deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Customer not found.'
            ], 404);
        }
    }
}