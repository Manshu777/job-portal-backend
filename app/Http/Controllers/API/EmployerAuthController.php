<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Employer;
use App\Models\Company;

use Illuminate\Support\Facades\Hash;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Auth;
use App\Mail\NewCompanyRegistered;
class EmployerAuthController extends Controller
{
    /**
     * Send OTP to email
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->contact_email;

        // Generate OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999)); // Secure 6-digit OTP

        // Store or update OTP in otp_verifications table
        OtpVerification::updateOrCreate(
            ['email' => $email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
                'session_token' => null, // Reset session token
            ]
        );

        try {
            Mail::to($email)->send(new SendOtpMail($otp));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to email',
        ]);
    }

    /**
     * Verify OTP before account creation
     */

    public function verifyOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'contact_email' => 'required|email',
        'otp' => 'required|numeric|digits:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $otpRecord = OtpVerification::where('email', $request->contact_email)
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

    // Check if employer exists
    $employer = Employer::where('contact_email', $request->contact_email)->first();
    if (!$employer) {
        return response()->json([
            'success' => false,
            'message' => 'No employer found with this email',
        ], 300);
    }

    // Generate and store token
    $token = $employer->createToken('EmployerToken')->plainTextToken;
    $otpRecord->update([
        'session_token' => $token,
        'session_token_expires_at' => Carbon::now()->addMinutes(30),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'OTP verified successfully',
        'email' => $request->contact_email,
        'session_token' => $token,
    ]);
}




public function addCompany(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'company_location' => 'nullable|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'contact_phone' => 'nullable|string|max:20',
        'gst_certificate' => 'nullable|file|mimes:pdf|max:2048', // PDF, max 2MB
        'other_certificate' => 'nullable|file|mimes:pdf|max:2048', // PDF, max 2MB
    ], [
        'gst_certificate.mimes' => 'The GST certificate must be a PDF file.',
        'other_certificate.mimes' => 'The other certificate must be a PDF file.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Get the authenticated employer
    $employer = Auth::guard('employer-api')->user();

    if (!$employer) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    // Handle file uploads with unique filenames
    $gstCertificatePath = null;
    $otherCertificatePath = null;

    if ($request->hasFile('gst_certificate') && $request->file('gst_certificate')->isValid()) {
        $filename = 'gst_' . time() . '_' . $request->file('gst_certificate')->getClientOriginalName();
        $gstCertificatePath = $request->file('gst_certificate')->storeAs('documents', $filename, 'public');
    }

    if ($request->hasFile('other_certificate') && $request->file('other_certificate')->isValid()) {
        $filename = 'other_' . time() . '_' . $request->file('other_certificate')->getClientOriginalName();
        $otherCertificatePath = $request->file('other_certificate')->storeAs('documents', $filename, 'public');
    }

    // Create the company
    $company = Company::create([
        'employer_id' => $employer->id,
        'name' => $request->name,
        'company_location' => $request->company_location,
        'contact_person' => $request->contact_person,
        'contact_phone' => $request->contact_phone,
        'gst_certificate' => $gstCertificatePath,
        'other_certificate' => $otherCertificatePath,
        'is_approved' => false, // Default to pending approval
    ]);

    // Notify admin about new company registration
    try {
        Mail::to('manshu.developer@gmail.com')->send(new NewCompanyRegistered($employer, $company));
    } catch (\Exception $e) {
        Log::error('Failed to send company registration email: ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => 'Company added successfully, awaiting admin approval',
        'data' => $company,
    ], 201);
}
     /**
      * List all companies for the authenticated employer
      */
     public function listCompanies(Request $request)
     {
         $employer = Auth::guard('employer-api')->user();
 
         if (!$employer) {
             return response()->json([
                 'success' => false,
                 'message' => 'Unauthorized',
             ], 401);
         }
 
         $companies = Company::where('employer_id', $employer->id)->get();
 
         return response()->json([
             'success' => true,
             'message' => 'Companies retrieved successfully',
             'data' => $companies,
         ], 200);
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
            'gst_certificate' => $gstCertificatePath,
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

 
     /**
      * Admin approves or rejects a company
      */
     public function approveCompany(Request $request, $companyId)
     {
         // Validate the request
         $validator = Validator::make($request->all(), [
             'is_approved' => 'required|boolean',
             'reason' => 'nullable|string|max:500', // Optional reason for rejection
         ]);
 
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }
 
         // Check if the user is an admin (assuming middleware or role-based check)
         // For simplicity, assuming this is protected by an admin middleware
         $company = Company::find($companyId);
 
         if (!$company) {
             return response()->json([
                 'success' => false,
                 'message' => 'Company not found',
             ], 404);
         }
 
         // Update approval status
         $company->update([
             'is_approved' => $request->is_approved,
             'approval_reason' => $request->reason,
         ]);
 
         // Notify employer about approval/rejection (optional)
         try {
             $employer = $company->employer;
             $status = $request->is_approved ? 'approved' : 'rejected';
        
         } catch (\Exception $e) {
             // Log the error if needed
         }
 
         return response()->json([
             'success' => true,
             'message' => "Company {$status} successfully",
             'data' => $company,
         ], 200);
     }
 
     /**
      * List all companies for admin (pending, approved, rejected)
      */
     public function listAllCompanies(Request $request)
     {
         // Assuming this is protected by admin middleware
         $companies = Company::with('employer')->get();
 
         return response()->json([
             'success' => true,
             'message' => 'All companies retrieved successfully',
             'data' => $companies,
         ], 200);
     }


      public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_email' => 'required|email|unique:employers,contact_email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s&.,()-]+$/',
            'company_location' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'gst_certificate' => 'required|file|mimes:pdf|max:2048',
            'company_pan_card' => 'nullable|file|mimes:pdf|max:2048',
        ], [
            'gst_certificate.mimes' => 'The GST certificate must be a PDF file.',
            'company_pan_card.mimes' => 'The company PAN card must be a PDF file.',
            'company_name.regex' => 'The company name contains invalid characters. Use letters, numbers, spaces, and common punctuation (&.,()-).',
            'company_name.required' => 'Company name is required.',
            'company_location.required' => 'Company location is required.',
            'gst_certificate.required' => 'GST certificate is required.',
            'company_pan_card.required' => 'Company PAN card is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate company_name for legitimacy
        $needsReview = false;
        $reviewReason = null;
        if (strlen($request->company_name) < 5) {
            $validator->errors()->add('company_name', 'Company name must be at least 5 characters long.');
        }
        $suspiciousKeywords = ['test', 'fake', 'dummy', 'invalid', 'temp'];
        foreach ($suspiciousKeywords as $keyword) {
            if (stripos($request->company_name, $keyword) !== false) {
                $needsReview = true;
                $reviewReason = "Company name contains suspicious keyword: '$keyword'.";
                break;
            }
        }
        if (strlen($request->company_name) > 100) {
            $needsReview = true;
            $reviewReason = 'Company name is unusually long and requires verification.';
        }

     

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Handle file uploads
            $gstCertificatePath = null;
            $companyPanCardPath = null;

            if ($request->hasFile('gst_certificate') && $request->file('gst_certificate')->isValid()) {
                $filename = 'gst_' . time() . '_' . $request->file('gst_certificate')->getClientOriginalName();
                $gstCertificatePath = $request->file('gst_certificate')->storeAs('documents', $filename, 'public');
            }

            if ($request->hasFile('company_pan_card') && $request->file('company_pan_card')->isValid()) {
                $filename = 'pan_' . time() . '_' . $request->file('company_pan_card')->getClientOriginalName();
                $companyPanCardPath = $request->file('company_pan_card')->storeAs('documents', $filename, 'public');
            }

            // Create employer
            $employer = Employer::create([
                'contact_email' => $request->contact_email,
                'name' => $request->name,
                'password' => Hash::make($request->password),

            ]);

            // Create company
            $company = Company::create([
                'employer_id' => $employer->id,
                'name' => $request->company_name,
                'company_location' => $request->company_location,
                'contact_phone' => $request->contact_phone,
                'gst_certificate' => $gstCertificatePath,
                'other_certificate' => $companyPanCardPath,
                'is_approved' => false,
               
            ]);

            // Notify admin about new company registration
            try {
                Mail::to('manshu.developer@gmail.com')->send(new NewCompanyRegistered($employer, $company, [
                    'needs_review' => $needsReview,
                    'review_reason' => $reviewReason,
                ]));
            } catch (\Exception $e) {
                \Log::error('Failed to send company registration email: ' . $e->getMessage());
            }

            // Generate token
            $token = $employer->createToken('EmployerToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Employer account and company created successfully. Company awaits admin approval.',
                'token' => $token,
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'status' => $needsReview ? 'Pending Review' : 'Pending Approval',
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Employer signup failed: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employer account or company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'contact_email' => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $employer = Employer::where('contact_email', $request->contact_email)->first();

    if (!$employer || !Hash::check($request->password, $employer->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password',
        ], 401);
    }

    // Delete existing tokens to ensure single active session (optional)
    $employer->tokens()->delete();

    // Generate new token
    $token = $employer->createToken('EmployerToken')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'session_token' => $token,
        'employer' => [
            'id' => $employer->id,
            'name' => $employer->name,
            'contact_email' => $employer->contact_email,
            'company_name' => $employer->company_name,
        ],
    ], 200);
}

 public function profile(Request $request)
{
    // Retrieve the authenticated employer using the 'employer-api' guard
    $employer = Auth::guard('employer-api')->user();

    if (!$employer) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'message' => 'Employer profile retrieved successfully',
        'data' => $employer,
    ], 200);
}
}