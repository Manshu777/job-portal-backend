<?php

namespace App\Http\Controllers\API;

use App\Models\Candidate;
use App\Models\OtpVerification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\SendOtpMail;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Signup Route
    public function signup(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:candidates,email',
            'full_name' => 'required|string|max:255',
        ]);

        $candidate = Candidate::create([
            'email' => $request->email,
            'full_name' => $request->full_name,
            'active_user' => 1,
        ]);

        return response()->json(['message' => 'Signup successful', 'user' => $candidate], 201);
    }

    public function sendOtp(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email',
        ]);

        // Generate a random 6-digit OTP
        $otp = rand(100000, 999999);

        // Check if an OTP record already exists
        $otpRecord = OtpVerification::where('email', $request->email)->first();

        if ($otpRecord) {
            // Update existing OTP record
            $otpRecord->update([
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
                'session_token' => null,
                'session_token_expires_at' => null,
            ]);
        } else {
            // Create new OTP record
            OtpVerification::create([
                'email' => $request->email,
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);
        }

        try {
            // Send OTP to the user's email
            Mail::to($request->email)->send(new SendOtpMail($otp));
        } catch (\Exception $e) {
            // If sending mail fails, return an error response
            return response()->json(["success" => false, 'message' => 'Failed to send OTP', 'error' => $e->getMessage()], 500);
        }

        // Return success response
        return response()->json(["success" => true]);
    }

public function verifyOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'otp' => 'required|numeric|digits:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $otpRecord = OtpVerification::where('email', $request->email)
        ->where('otp', $request->otp)
        ->first();

    if (!$otpRecord) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP',
        ], 400);
    }

    if (Carbon::now()->gt($otpRecord->expires_at)) {
        return response()->json([
            'success' => false,
            'message' => 'OTP has expired',
        ], 400);
    }

    // Check if candidate exists
    $candidate = Candidate::where('email', $request->email)->first();

    $response = [
        'success' => true,
        'message' => 'OTP verified successfully',
        'email' => $request->email,
    ];

    if ($candidate) {
        // Existing candidate: Generate token and update records
        $token = $candidate->createToken('candidate-api')->plainTextToken;
        $otpRecord->update([
            'token' => $token,
            'session_token_expires_at' => Carbon::now()->addMinutes(30),
        ]);

        $candidate->update([
            'last_login' => Carbon::now(),
            'token' => $token,
        ]);

        $response['token'] = $token;
        $response['user'] = $candidate;
        $response['message'] = 'OTP verified and user logged in successfully';
    } else {
        // New candidate: Create record and generate token
        $candidate = new Candidate();
        $candidate->email = $request->email;
        $candidate->token = Str::uuid()->toString();
        $candidate->last_login = Carbon::now();
        $candidate->save();

        $token = $candidate->createToken('candidate-api')->plainTextToken;
        $otpRecord->update([
            'token' => $token,
            'session_token_expires_at' => Carbon::now()->addMinutes(30),
        ]);

        $response['token'] = $token;
        $response['user'] = $candidate;
        $response['message'] = 'OTP verified and new candidate account created successfully';
    }

    return response()->json($response);
}

    public function profile(Request $request)
{

       $candidate = Auth::guard('candidate-api')->user();
    return response()->json($candidate);
}



    public function updateEmployer(Request $request)
    {
        // Get the authenticated employer
        $employer = Auth::guard('employer-api')->user();

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'company_name' => 'sometimes|string|max:255',
            'company_location' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|string|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'gst_number' => 'sometimes|string|max:15',
            'gst_certificate' => 'sometimes|file|mimes:pdf|max:2048', // PDF, max 2MB
            'company_pan_card' => 'sometimes|file|mimes:pdf|max:2048', // PDF, max 2MB
            'password' => 'sometimes|string|min:6',
        ], [
            'gst_certificate.mimes' => 'The GST certificate must be a PDF file.',
            'company_pan_card.mimes' => 'The company PAN card must be a PDF file.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle file uploads
        $gstCertificatePath = $employer->gst_certificate;
        $companyPanCardPath = $employer->company_pan_card;

        if ($request->hasFile('gst_certificate') && $request->file('gst_certificate')->isValid()) {
            $filename = 'gst_' . time() . '_' . $request->file('gst_certificate')->getClientOriginalName();
            $gstCertificatePath = $request->file('gst_certificate')->storeAs('documents', $filename, 'public');
        }

        if ($request->hasFile('company_pan_card') && $request->file('company_pan_card')->isValid()) {
            $filename = 'pan_' . time() . '_' . $request->file('company_pan_card')->getClientOriginalName();
            $companyPanCardPath = $request->file('company_pan_card')->storeAs('documents', $filename, 'public');
        }

        // Prepare data for update
        $updateData = [
            'name' => $request->input('name', $employer->name),
            'company_name' => $request->input('company_name', $employer->company_name),
            'company_location' => $request->input('company_location', $employer->company_location),
            'contact_person' => $request->input('contact_person', $employer->contact_person),
            'contact_phone' => $request->input('contact_phone', $employer->contact_phone),
            'gst_number' => $request->input('gst_number', $employer->gst_number),
            'Dgst_certificate' => $gstCertificatePath,
            'company_pan_card' => $companyPanCardPath,
        ];

        // Update password if provided
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Update employer record
        $employer->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Employer profile updated successfully',
            'data' => $employer->fresh(), // Retrieve fresh instance to include updated data
        ], 200);
    }
}