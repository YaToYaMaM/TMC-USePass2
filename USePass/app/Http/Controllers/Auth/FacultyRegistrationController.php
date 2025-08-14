<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Faculty;
use Illuminate\Validation\ValidationException;
use Exception as GeneralException;

class FacultyRegistrationController extends Controller
{
    public function sendFacultyOtp(Request $request)
    {
        try {
            if (Faculty::where('faculty_id', $request->faculty_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Faculty/Staff ID already exists',
                    'field' => 'faculty_id',
                    'message' => 'This Faculty ID is already registered in the system.'
                ], 422);
            }
            if (Faculty::where('faculty_email', $request->faculty_email)->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Email already exists',
                    'field' => 'faculty_email',
                    'message' => 'This email address is already registered in the system.'
                ], 422);
            }

            // Validate request
            $validated = $request->validate([
                'faculty_id' => 'required|string|unique:facultyStaff,faculty_id',
                'faculty_first_name' => 'required|string|max:100',
                'faculty_last_name' => 'required|string|max:100',
                'faculty_middle_initial' => 'nullable|string|max:1',
                'faculty_gender' => 'required|string|max:50',
                'faculty_college' => [
                    'required',
                    'string',
                    'in:College of Teacher Education and Technology (CTET),School of Medicine (SoM),College of Agriculture and Related Sciences (CARS)'
                ],
                'faculty_department' => 'required|string|max:150',
                'faculty_unit' => 'required|in:Tagum,Mabini',
                'faculty_email' => 'required|email|unique:facultyStaff,faculty_email',
                'faculty_phone_num' => 'required|string|max:20',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
            ], [
                'faculty_id.required' => 'Faculty ID is required.',
                'faculty_id.unique' => 'This Faculty ID is already registered. Please use a different Faculty ID.',
                'faculty_first_name.required' => 'First name is required.',
                'faculty_last_name.required' => 'Last name is required.',
                'faculty_gender.required' => 'Please select a gender.',
                'faculty_college.required' => 'College is required.',
                'faculty_college.in' => 'Please select a valid college.',
                'faculty_department.required' => 'Department is required.',
                'faculty_unit.required' => 'Please select a unit.',
                'faculty_email.required' => 'Email address is required.',
                'faculty_email.email' => 'Please enter a valid email address.',
                'faculty_email.unique' => 'This email address is already registered. Please use a different email.',
                'faculty_phone_num.required' => 'Phone number is required.',
                'profile_image.max' => 'Profile image must be less than 5MB.',
                'profile_image.mimes' => 'Profile image must be a valid image file (jpeg, png, jpg).'
            ]);

            $collegeDepartments = Faculty::getDepartmentsByCollege($validated['faculty_college']);
            if (!in_array($validated['faculty_department'], $collegeDepartments)) {
                return response()->json([
                    'success' => false,
                    'error' => 'The selected department does not belong to the selected college.',
                    'field' => 'faculty_department'
                ], 422);
            }

            // Handle profile image upload
            $imagePath = null;
            if ($request->hasFile('profile_image')) {
                try {
                    $image = $request->file('profile_image');
                    $imageName = time() . '_faculty_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('faculty_pictures');

                    // Create directory if it doesn't exist
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    if ($image->move($destinationPath, $imageName)) {
                        $imagePath = 'faculty_pictures/' . $imageName;
                    } else {
                        throw new GeneralException('Failed to upload profile image');
                    }
                } catch (GeneralException $e) {
                    Log::error('Image upload error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to upload profile image. Please try again.'
                    ], 500);
                }
            }

            // Generate OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store all faculty data in session for later use
            $registrationData = [
                'faculty_id' => $validated['faculty_id'],
                'faculty_first_name' => $validated['faculty_first_name'],
                'faculty_last_name' => $validated['faculty_last_name'],
                'faculty_middle_initial' => $validated['faculty_middle_initial'],
                'faculty_gender' => $validated['faculty_gender'],
                'faculty_college' => $validated['faculty_college'],
                'faculty_department' => $validated['faculty_department'],
                'faculty_unit' => $validated['faculty_unit'],
                'faculty_email' => $validated['faculty_email'],
                'faculty_phone_num' => $validated['faculty_phone_num'],
                'faculty_profile_image' => $imagePath,
            ];

            // Store in session
            Session::put('faculty_registration_data', $registrationData);
            Session::put('faculty_otp', $otp);
            Session::put('faculty_otp_email', $validated['faculty_email']);
            Session::put('faculty_otp_start_time', now());

            // Send OTP email
            $this->sendOtpEmail($validated['faculty_email'], $otp, 'Faculty Registration');

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email',
                'otp_start_time' => now()->toISOString(),
                'faculty_id' => $validated['faculty_id']
            ]);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstError = collect($errors)->flatten()->first();

            return response()->json([
                'success' => false,
                'error' => $firstError,
                'errors' => $errors
            ], 422);
        } catch (GeneralException $e) {
            Log::error('Faculty OTP send failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Failed to send OTP. Please check your email address and try again.'
            ], 500);
        }
    }

    public function resendFacultyOtp(Request $request)
    {
        try {
            if (!Session::has('faculty_registration_data') || !Session::has('faculty_otp_email')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active registration session found. Please start registration again.'
                ], 400);
            }

            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            Session::put('faculty_otp', $otp);
            Session::put('faculty_otp_start_time', now());

            $email = Session::get('faculty_otp_email');

            $this->sendOtpEmail($email, $otp, 'Faculty Registration - Resend');

            return response()->json([
                'success' => true,
                'message' => 'OTP resent successfully',
                'otp_start_time' => now()->toISOString()
            ]);

        } catch (GeneralException $e) {
            Log::error('Faculty OTP resend failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to resend OTP. Please try again.'
            ], 500);
        }
    }

    public function verifyFacultyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|numeric|digits:6',
            ]);

            $sessionOtp = Session::get('faculty_otp');
            $inputOtp = $request->otp;

            if (!$sessionOtp || $inputOtp != $sessionOtp) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid OTP code. Please check and try again.'
                ], 422);
            }

            $registrationData = Session::get('faculty_registration_data');

            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Registration data not found. Please start registration again.'
                ], 422);
            }

            // Create faculty record
            $faculty = Faculty::create($registrationData);

            // Clear session data
            Session::forget([
                'faculty_otp',
                'faculty_otp_start_time',
                'faculty_otp_email',
                'faculty_registration_data'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully',
                'faculty_data' => $faculty
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid OTP format',
                'errors' => $e->errors()
            ], 422);
        } catch (GeneralException $e) {
            Log::error('Faculty registration error: ' . $e->getMessage());
            Log::error('Registration data: ' . json_encode($registrationData ?? 'null'));

            return response()->json([
                'success' => false,
                'error' => 'Registration failed. Please try again or contact support.'
            ], 500);
        }
    }

    private function sendOtpEmail($email, $otp, $purpose)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'usepasstmc.system@gmail.com';
            $mail->Password   = 'rhkwujluyfwnaxpy';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('usepasstmc.system@gmail.com', 'USePass System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'USePass Faculty OTP Code - ' . $purpose;
            $mail->Body = '
                <div style="font-family: system-ui, sans-serif; font-size: 15px; color: #222; padding: 20px;">
                    <p>Dear Faculty Member,</p>

                    <p>Here is your One-Time Password (OTP) for ' . $purpose . ':</p>

                    <p style="font-size: 24px; font-weight: bold; letter-spacing: 4px; margin: 20px 0;">' . $otp . '</p>

                    <p>This code will expire in 2 minutes. Please do not share it with anyone for your security.</p>

                    <br>

                    <p>Best regards,<br>
                    USePass Support Team</p>
                </div>';

            $mail->send();
        } catch (Exception $e) {
            throw new Exception('Could not send OTP. Mailer Error: ' . $mail->ErrorInfo);
        }
    }

    public function showFacultyOtpForm()
    {
        $registrationData = Session::get('faculty_registration_data');

        if (!$registrationData) {
            return redirect()->route('faculty.register')->with('error', 'Registration session expired');
        }

        return inertia('Frontend/FacultyOtp', [
            'otp_start_time' => Session::get('faculty_otp_start_time'),
            'faculty_id' => $registrationData['faculty_id'],
            'faculty_email' => $registrationData['faculty_email'],
        ]);
    }
}
