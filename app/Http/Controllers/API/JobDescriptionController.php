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
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'jobTitle' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'requiredSkills.*' => 'nullable|string|max:255',
            'experienceLevel' => 'nullable|max:50',
            'totalExperienceRequired' => 'nullable|string|max:255',
            'experienceMax' => 'nullable|max:50',
            'educationLevel' => 'required|max:255',
            'course' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'companyName' => 'nullable|string|max:255',
            'newCompanyName' => 'nullable|string|max:255',
            'contactEmail' => 'nullable|email|max:255',
            'contactPhone' => 'nullable|string|max:20',
            'contactPreference' => 'nullable',
            'englishLevel' => 'nullable|string|max:50',
            'genderPreference' => 'nullable|string|max:50',
            'gstCertificate' => 'nullable',
            'incentive' => 'nullable|string|max:255',
            'interviewDate' => 'nullable|string|max:255',
            'interviewLocation' => 'nullable|string|max:255',
            'interviewMode' => 'nullable|string|in:Online,In-Person,Hybrid',
            'interviewTime' => 'nullable|string|max:255',
            'jobType' => 'required|string|in:Full-Time,Part-Time,Contract,Internship',
            'joiningFee' => 'nullable|boolean',
            'jobExpireTime' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare prompt
        $formData = $request->all();
        // Use newCompanyName if provided, otherwise fall back to companyName, or default to 'our company'
        $company = $formData['newCompanyName'] ?? $formData['companyName'] ?? 'our company';
        $prompt = "
        Write a professional job description for a {$formData['jobTitle']} position at $company 
        The position is {$formData['jobType']}" . 
        ($formData['experienceMax'] ? " with up to {$formData['experienceMax']} years of experience" : "") . 
        " and a {$formData['educationLevel']} level of education" .
        (isset($formData['course']) ? " in {$formData['course']}" . (isset($formData['specialization']) ? ", specializing in {$formData['specialization']}" : "") : "") .
        (isset($formData['englishLevel']) ? ". Candidates should have {$formData['englishLevel']} English proficiency" : "") .
        (isset($formData['interviewMode']) ? ". The interview will be conducted {$formData['interviewMode']}" : "") .
        (isset($formData['contactEmail']) ? ". Contact email: {$formData['contactEmail']}" : "") .
        ". 
";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.together.api_key'),
            ])->post('https://api.together.xyz/v1/chat/completions', [
                'model' => 'mistralai/Mistral-7B-Instruct-v0.2',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that writes job descriptions.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            $generatedText = $response->json('choices')[0]['message']['content'] ?? 'No text generated.';

            // Limit to 300 words for brevity
            $wordsArr = preg_split('/\s+/', strip_tags($generatedText));
            if (count($wordsArr) > 300) {
                $trimmedText = implode(' ', array_slice($wordsArr, 0, 300));
                $generatedText = $trimmedText . '...';
            }

            // Convert plain text/markdown LLM output to HTML
            $htmlText = $this->formatJobDescriptionToHTML($generatedText, $formData);

            return response()->json([
                'success' => true,
                'jobOverview' => $htmlText,
            ]);
        } catch (\Exception $e) {
            \Log::error('API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors' => ['api' => 'Failed to generate job description. Please try again later.'],
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
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare prompt
        $formData = $request->all();
        $prompt = "Provide a numbered list of exactly 25 skills required for a {$formData['jobTitle']} position. Format each skill as 'N. SkillName' (e.g., '1. Python', '2. JavaScript'). Do not include any introductory text, explanations, or additional content.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.huggingface.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://router.huggingface.co/v1/chat/completions', [
                'model' => 'deepseek-ai/DeepSeek-V3.2-Exp:novita',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that generates precise lists of skills for job positions.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            if ($response->failed()) {
                Log::error('Hugging Face Router API Request Failed: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'errors' => ['api' => 'API request failed: ' . $response->body()],
                ], 500);
            }

            Log::info('Hugging Face Router API Response: ' . json_encode($response->json()));

            // Extract generated text
            $generatedText = $response->json('choices')[0]['message']['content'] ?? 'No skills generated.';

            // Parse the generated text into an array of skills
            $skills = [];
            preg_match_all('/\d+\.\s*([^\n]+)/', $generatedText, $matches);
            if (!empty($matches[1])) {
                $skills = array_map('trim', $matches[1]);
            } else {
                // Fallback: split by lines, filter out non-skill lines
                $lines = array_filter(array_map('trim', explode("\n", $generatedText)));
                foreach ($lines as $line) {
                    if (preg_match('/^\d+\.\s*(.+)/', $line, $match)) {
                        $skills[] = trim($match[1]);
                    }
                }
            }
            $skills = array_slice($skills, 0, 50); // Ensure max 50 skills

            if (empty($skills) || $skills[0] === 'No skills generated.') {
                Log::warning('No skills generated for job title: ' . $formData['jobTitle']);
                return response()->json([
                    'success' => false,
                    'errors' => ['api' => 'No skills could be generated for the provided job title.'],
                ], 500);
            }

            return response()->json([
                'success' => true,
                'skills' => $skills,
            ]);
        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors' => ['api' => 'Failed to generate skills. Please try again later.'],
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