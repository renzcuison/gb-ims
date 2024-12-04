<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SUPPLIERSController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        if ($suppliers->count() > 0) {
            return response()->json([
                'status' => 200,
                'suppliers' => $suppliers
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
            'supplier_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $supplier = Supplier::create([
            'supplier_name' => $request->supplier_name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Supplier created successfully.',
            'supplier' => $supplier
        ], 200);
    }

    public function show($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            return response()->json([
                'status' => 200,
                'supplier' => $supplier
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Supplier not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $supplier = Supplier::find($id);
        if ($supplier) {
            $supplier->update([
                'supplier_name' => $request->supplier_name,
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'address' => $request->address
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Supplier updated successfully.',
                'supplier' => $supplier
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Supplier not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $supplier->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Supplier deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Supplier not found.'
            ], 404);
        }
    }
}