<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EMPLOYEESController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        if ($employees->count() > 0) {
            return response()->json([
                'status' => 200,
                'employees' => $employees
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
            'role' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $employee = Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role' => $request->role,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Employee added successfully.',
            'employee' => $employee
        ], 200);
    }

    public function show($id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            return response()->json([
                'status' => 200,
                'employee' => $employee
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Employee not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $employee = Employee::find($id);
        if ($employee) {
            $employee->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'role' => $request->role,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Employee updated successfully.',
                'employee' => $employee
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Employee not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            $employee->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Employee deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Employee not found.'
            ], 404);
        }
    }
}