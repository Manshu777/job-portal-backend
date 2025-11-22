<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Posted</title>
</head>

<body style="margin:0; padding:0; background:#eef2f7; font-family:Arial, Helvetica, sans-serif;">
<table width="100%" cellspacing="0" cellpadding="0" style="background:#eef2f7; padding:25px 0;">
    <tr>
        <td align="center">

            <table width="650" cellspacing="0" cellpadding="0" style="background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,0.1); overflow:hidden;">
                
                <!-- HEADER -->
                <tr>
                    <td style="background:#1e40af; padding:25px; text-align:center;">
                        <h1 style="margin:0; color:#fff; font-size:24px; font-weight:600;">
                            New Job Posting Awaiting Approval
                        </h1>
                    </td>
                </tr>

                <!-- BODY -->
                <tr>
                    <td style="padding:30px;">

                        <!-- EMPLOYER INFO -->
                        <h2 style="font-size:20px; color:#1e40af; margin-bottom:10px;">Employer Information</h2>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                            <tr>
                                <td style="font-weight:bold; width:30%; padding:6px 0;">Name:</td>
                                <td>{{ $employer->name }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; padding:6px 0;">Email:</td>
                                <td>{{ $employer->email }}</td>
                            </tr>
                        </table>

                        <!-- COMPANY INFO -->
                        <h2 style="font-size:20px; color:#1e40af; margin-bottom:10px;">Company Information</h2>

                        @if($isNewCompany)
                            <div style="background:#fff7ed; padding:15px; border-left:4px solid #f59e0b; border-radius:6px; margin-bottom:15px;">
                                <strong style="color:#b45309;">New Company Registered</strong>
                                <p style="margin:6px 0;"><strong>Company Name:</strong> {{ $company->name }}</p>
                                <p style="margin:6px 0;"><strong>PAN:</strong> {{ $company->other_certificate ? 'Uploaded' : 'Not Uploaded' }}</p>
                                <p style="margin:6px 0;"><strong>Status:</strong> Pending Approval</p>
                            </div>
                        @else
                            <div style="background:#ecfdf5; padding:15px; border-left:4px solid #10b981; border-radius:6px; margin-bottom:15px;">
                                <strong style="color:#059669;">Existing Approved Company</strong>
                                <p style="margin:6px 0;"><strong>Company Name:</strong> {{ $company->name }}</p>
                            </div>
                        @endif

                        <!-- JOB INFORMATION -->
                        <h2 style="font-size:20px; color:#1e40af; margin-bottom:10px;">Job Information</h2>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                            <tr>
                                <td style="font-weight:bold; width:30%; padding:6px 0;">Job Title:</td>
                                <td>{{ $jobPosting->job_title }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; padding:6px 0;">Job Role:</td>
                                <td>{{ $jobPosting->job_role }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; padding:6px 0;">Job Type:</td>
                                <td>{{ $jobPosting->job_type }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; padding:6px 0;">Location:</td>
                                <td>{{ $jobPosting->location }} ({{ $jobPosting->work_location_type }})</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; padding:6px 0;">Salary Range:</td>
                                <td>
                                    ₹{{ number_format($jobPosting->min_salary) }}
                                    @if($jobPosting->max_salary)
                                        – ₹{{ number_format($jobPosting->max_salary) }}
                                    @endif
                                    <span style="color:#555;">({{ $jobPosting->pay_type }})</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; padding:6px 0;">Candidates Required:</td>
                                <td>{{ $jobPosting->number_of_candidates_required }}</td>
                            </tr>
                        </table>

                        <!-- JOINING FEE ALERT -->
                        @if($joiningFeeText === 'YES - Will charge joining fee')
                            <div style="background:#fee2e2; padding:15px; border-left:4px solid #ef4444; border-radius:6px; margin-bottom:20px;">
                                <strong style="color:#b91c1c;">Joining Fee Alert</strong>
                                <p style="margin:6px 0;">This employer will charge a joining fee → HIGH FRAUD RISK.</p>
                            </div>
                        @else
                            <div style="background:#ecfdf5; padding:15px; border-left:4px solid #10b981; border-radius:6px; margin-bottom:20px;">
                                <strong style="color:#059669;">No Joining Fee</strong>
                                <p style="margin:6px 0;">This job does not require a joining fee.</p>
                            </div>
                        @endif

                        <!-- JOB POST TIME -->
                        <p style="color:#777; font-size:14px; margin-top:25px;">
                            Job ID: <strong>#{{ $jobPosting->id }}</strong><br>
                            Posted on: {{ now()->format('d M, Y h:i A') }}
                        </p>

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td style="background:#f8f9fa; padding:20px; text-align:center;">
                        <p style="color:#666; margin:0; font-size:14px;">Hiring Boat – Admin Notification</p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>
</body>
</html>
