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

use App\Models\CandidateEducation;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Signup Route



     public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:candidates,email',
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
            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP sent successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify password reset OTP
     */
    public function verifyPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:candidates,email',
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

        // Generate a reset token
        $resetToken = Str::random(60);
        $otpRecord->update([
            'session_token' => $resetToken,
            'session_token_expires_at' => Carbon::now()->addMinutes(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'reset_token' => $resetToken,
            'email' => $request->email,
        ], 200);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:candidates,email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $otpRecord = OtpVerification::where('email', $request->email)
            ->where('session_token', $request->reset_token)
            ->first();

        // if (!$otpRecord) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid reset token',
        //     ], 400);
        // }

        // if (Carbon::now()->gt($otpRecord->session_token_expires_at)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Reset token has expired',
        //     ], 400);
        // }

        $candidate = Candidate::where('email', $request->email)->first();
        if (!$candidate) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Update password
        $candidate->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear OTP record
        // $otpRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ], 200);
    }
    
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
        // 1. Authenticate candidate
        $candidate = Auth::guard('candidate-api')->user();

        if (! $candidate) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate not found',
            ], 404);
        }

        // 2. Load ONLY educations (no experiences)
        $candidate->load('educations');

        // 3. Convert to array
        $data = $candidate->toArray();

        // ------------------------------------------------------------
        // 4. Normalise JSON columns (skills, preferred_job_titles, job_roles)
        // ------------------------------------------------------------
        $jsonFields = ['skills', 'preferred_job_titles', 'job_roles'];

        foreach ($jsonFields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field])) {
                    continue;
                }

                if (is_string($data[$field])) {
                    $decoded = json_decode($data[$field], true);
                    $data[$field] = is_array($decoded) ? $decoded : [$data[$field]];
                } else {
                    $data[$field] = [$data[$field]];
                }
            } else {
                $data[$field] = [];
            }
        }

        // ------------------------------------------------------------
        // 5. Add file URLs (optional but helpful for frontend)
        // ------------------------------------------------------------
        if (!empty($data['profile_pic'])) {
            $data['profile_pic_url'] = asset('storage/' . $data['profile_pic']);
        }
        if (!empty($data['resume'])) {
            $data['resume_url'] = asset('storage/' . $data['resume']);
        }

        // ------------------------------------------------------------
        // 6. Return clean payload
        // ------------------------------------------------------------
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
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