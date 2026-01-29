<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
class JobDescriptionController extends Controller
{
    
  public function generateJobDescription(Request $request)
{
    // Validation - allow arrays for course & specialization
    $validator = Validator::make($request->all(), [
        'jobTitle'               => 'required|string|max:255',
        'jobType'                => 'required|string',
        'work_location_type'     => 'nullable|string',
        'educationLevel'         => 'required|string|max:255',
        'course'                 => 'nullable|array',
        'course.*'               => 'string',
        'specialization'         => 'nullable|array',
        'specialization.*'       => 'string',
        'totalExperienceRequired'=> 'nullable|string',
        'englishLevel'           => 'nullable|string',
        'interviewMode'          => 'nullable|string',
        'companyName'            => 'nullable|string',
        'newCompanyName'         => 'nullable|string',
        'contactEmail'           => 'nullable|email',
        // ... add other fields as needed
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $formData = $request->all();

    // Company name
    $company = $formData['newCompanyName'] ?? $formData['companyName'] ?? 'our company';

    // Handle multiple courses
    $courseText = '';
    if (!empty($formData['course']) && is_array($formData['course'])) {
        $courses = array_filter($formData['course']);
        if ($courses) {
            $courseText = " in " . implode(', ', $courses);
        }
    }

    // Handle multiple specializations
    $specializationText = '';
    if (!empty($formData['specialization']) && is_array($formData['specialization'])) {
        $specs = array_filter($formData['specialization']);
        if ($specs) {
            $specializationText = ", specializing in " . implode(', ', $specs);
        }
    }

    // Build a clear, structured prompt
    $prompt = "Write a professional and attractive job description for the following position:\n\n" .
              "Job Title: {$formData['jobTitle']}\n" .
              "Company: {$company}\n" .
              "Job Type: {$formData['jobType']}\n" .
              "Work Location: " . ($formData['work_location_type'] ?? 'Not specified') . "\n" .
              "Education: {$formData['educationLevel']}{$courseText}{$specializationText}\n" .
              "Experience: " . ($formData['totalExperienceRequired'] ?? 'Fresher/Experienced') . "\n" .
              "English Level: " . ($formData['englishLevel'] ?? 'Not specified') . "\n" .
              "Interview Mode: " . ($formData['interviewMode'] ?? 'To be confirmed') . "\n\n" .
              "Structure the job description with these sections:\n" .
              "- About the Role\n" .
              "- Key Responsibilities\n" .
              "- Required Qualifications & Skills\n" .
              "- What We Offer\n\n" .
              "Use bullet points, keep it engaging, concise (under 400 words), and candidate-friendly.";

    try {
        $apiKey = 'd9f42ecf0c9c4e2ab0ae9eb1b8b5f281'; // Your AI ML API key

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://api.aimlapi.com/v1/chat/completions', [
            'model'       => 'mistralai/Mistral-7B-Instruct-v0.2', // Popular model available on AI ML API
            // You can also try: 'gpt-4o-mini', 'claude-3-haiku', etc. if supported
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'You are an expert HR writer who creates clear, professional, and appealing job descriptions.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens'  => 1024,
        ]);

        if (!$response->successful()) {
            \Log::error('AI ML API Error', ['response' => $response->body()]);
            return response()->json([
                'success' => false,
                'errors'  => ['api' => 'Failed to generate job description. Please try again.']
            ], 500);
        }

        $generatedText = $response->json('choices.0.message.content', 'No description generated.');

        // Optional: Limit to ~300 words
        $words = preg_split('/\s+/', strip_tags($generatedText));
        if (count($words) > 300) {
            $generatedText = implode(' ', array_slice($words, 0, 300)) . '...';
        }

        // Convert to HTML (your existing helper method)
        $htmlText = $this->formatJobDescriptionToHTML($generatedText, $formData);

        return response()->json([
            'success'     => true,
            'jobOverview' => $htmlText,
        ]);

    } catch (\Exception $e) {
        \Log::error('AI ML API Exception: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'errors'  => ['api' => 'Service temporarily unavailable. Please try again later.']
        ], 500);
    }
}
    
  

   public function generateSkills(Request $request)
{
    // Step 1: Validate input
    $validator = Validator::make($request->all(), [
        'jobTitle' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $formData = $request->all();
    $jobTitle = $formData['jobTitle'];

    // Improved prompt for better, consistent output
    $prompt = "Generate exactly 25 key technical and soft skills required for a {$jobTitle} position.\n" .
              "Return ONLY a numbered list in this exact format:\n" .
              "1. Skill Name\n" .
              "2. Skill Name\n" .
              "...\n" .
              "25. Skill Name\n\n" .
              "Do NOT include any introduction, explanation, markdown, or extra text. Just the numbered list.";

    try {
        $apiKey = 'd9f42ecf0c9c4e2ab0ae9eb1b8b5f281'; // Your AI ML API key

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://api.aimlapi.com/v1/chat/completions', [
            'model'       => 'mistralai/Mistral-7B-Instruct-v0.2', // Fast & reliable
            // Alternatives: 'gpt-4o-mini', 'claude-3-haiku-20240307' if you want variety
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'You are an expert recruiter who knows the most in-demand skills for any job role.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.6,     // Slightly lower for consistency
            'max_tokens'  => 800,
        ]);

        if (!$response->successful()) {
            \Log::error('AI ML API Error (generateSkills)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'errors'  => ['api' => 'Failed to connect to AI service. Please try again.']
            ], 500);
        }

        $generatedText = $response->json('choices.0.message.content', '');

        if (empty($generatedText)) {
            return response()->json([
                'success' => false,
                'errors'  => ['api' => 'No response from AI model.']
            ], 500);
        }

        // Extract skills using regex: matches "1. Skill Name", "2. Another Skill", etc.
        preg_match_all('/^\d+\.\s*(.+)$/m', $generatedText, $matches);

        $skills = !empty($matches[1]) 
            ? array_map('trim', $matches[1]) 
            : [];

        // Fallback: split by lines and clean
        if (empty($skills)) {
            $lines = array_filter(array_map('trim', explode("\n", $generatedText)));
            foreach ($lines as $line) {
                if (preg_match('/^\d+\.\s*(.+)/', $line, $m)) {
                    $skills[] = trim($m[1]);
                }
            }
        }

        // Limit to 25 skills max
        $skills = array_slice($skills, 0, 25);

        // Final check
        if (empty($skills)) {
            \Log::warning('No skills parsed for job title: ' . $jobTitle);
            return response()->json([
                'success' => false,
                'errors'  => ['api' => 'Could not generate valid skills. Please try again.']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'skills'  => $skills,
        ]);

    } catch (\Exception $e) {
        \Log::error('generateSkills Exception: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'errors'  => ['api' => 'Service temporarily unavailable. Please try again later.']
        ], 500);
    }
}
    public function formatJobDescriptionToHTML($text, $formData)
    {
        // Initialize sections
        $sections = [
            'Job Title' => $formData['jobTitle'] . ' (' . ($formData['department'] ?? 'General') . ') - ' . $formData['experienceLevel'],
            'Overview' => '',
            'Key Responsibilities' => '',
            'Qualifications' => '',
            'Education and Experience' => '',
        ];

        // Flexible regex to capture sections
        $pattern = '/^(?:\*|_|#)*\s*(Overview|Key Responsibilities|Qualifications|Education and Experience):?\s*(.*?)(?=(?:^(?:\*|_|#)*\s*(?:Overview|Key Responsibilities|Qualifications|Education and Experience):|\Z))/ims';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Process matches
        foreach ($matches as $match) {
            $sectionName = trim($match[1]);
            $content = trim($match[2]);
            if (isset($sections[$sectionName])) {
                $sections[$sectionName] = $content;
            }
        }

        // Ensure equal opportunity statement is included in Education and Experience
        $equalOpportunity = "We are an equal opportunity employer and value diversity at our company. We do not discriminate on the basis of race, religion, color, national origin, gender, sexual orientation, age, marital status, veteran status, or disability status. We welcome applications from all qualified individuals and encourage those from diverse backgrounds to apply.";
        if ($sections['Education and Experience']) {
            $sections['Education and Experience'] .= "\n- " . $equalOpportunity;
        } else {
            $sections['Education and Experience'] = "- " . $equalOpportunity;
        }

        // Helper function to convert text to HTML list
        $listify = function($txt) {
            $lines = explode("\n", trim($txt));
            $items = '';
            foreach ($lines as $line) {
                $line = trim($line, "-* \r\t\n");
                if ($line) {
                    $items .= '<li><p>' . e($line) . '</p></li>';
                }
            }
            return $items ? "<ul>$items</ul>" : '';
        };

        // Build HTML output
        $html = "<p>" . e($sections['Job Title']) . "</p>\n<p></p>\n";
        if ($sections['Overview']) {
            $html .= "<p>" . nl2br(e($sections['Overview'])) . "</p>\n";
        }
        if ($sections['Key Responsibilities']) {
            $html .= "<h3>Key Responsibilities:</h3>\n" . $listify($sections['Key Responsibilities']) . "\n";
        }
        if ($sections['Qualifications']) {
            $html .= "<h3>Qualifications:</h3>\n" . $listify($sections['Qualifications']) . "\n";
        }
        if ($sections['Education and Experience']) {
            $html .= "<h3>Education and Experience:</h3>\n" . $listify($sections['Education and Experience']) . "\n";
        }

        // Fallback if no sections were matched
        if ($html === "<p>" . e($sections['Job Title']) . "</p>\n<p></p>\n") {
            $html .= '<p>' . nl2br(e($text)) . '</p>';
        }

        return $html;
    }
}