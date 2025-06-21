<?php

namespace App\Http\Controllers;

use App\Models\EmployerRegistrationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserManagementController extends Controller
{
    //
    public function allUsers()
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::latest()->get()->map(function ($user) {
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_picture' => null,
            ];

            if ($user->role === 'itian' && $user->itianProfile) {
                $data['profile_picture'] = $user->itianProfile->profile_picture ?? null;
            } elseif ($user->role === 'employer' && $user->employerProfile) {
                $data['profile_picture'] = $user->employerProfile->profile_picture ?? null;
            }

            return $data;
        });

        return response()->json($users);
    }

    public function getUnApprovedEmployers()
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::where('role', 'employer')
            ->where('is_active', false)
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return response()->json($users);
    }

    public function approveEmployer(Request $request, $id)
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        if ($user->role !== 'employer') {
            return response()->json(['message' => 'User is not an employer'], 400);
        }

        $user->is_active = true;
        $user->save();

        // Delete employer registration request after approval
        $employerRequest = EmployerRegistrationRequest::where('user_id', $user->id)->first();
        if ($employerRequest) {
            // Set status to Approved before sending email
            $employerRequest->status = 'Approved';
            $employerRequest->load('user'); // Ensure user is loaded
            Mail::to($user->email)->send(new \App\Mail\EmployerRegistrationRequestReviewed($employerRequest));
            $employerRequest->delete();
        }

        return response()->json(['message' => 'Employer approved successfully']);
    }

    public function rejectEmployer(Request $request, $id)
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        if ($user->role !== 'employer') {
            return response()->json(['message' => 'User is not an employer'], 400);
        }

        // Delete employer registration request after rejection
        $employerRequest = EmployerRegistrationRequest::where('user_id', $user->id)->first();
        if ($employerRequest) {
            // Set status to Rejected before sending email
            $employerRequest->status = 'Rejected';
            $employerRequest->load('user'); // Ensure user is loaded
            Mail::to($user->email)->send(new \App\Mail\EmployerRegistrationRequestReviewed($employerRequest));
            $employerRequest->delete();
        }

        $user->delete();

        return response()->json(['message' => 'Employer rejected and deleted successfully']);
    }

    public function deleteUser(Request $request, $id)
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        // Prevent deletion of admin users if they are only one admin
        $adminCount = User::where('role', 'admin')->count();
        if ($adminCount <= 1 && $user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete the last admin user'], 400);
        }


        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
