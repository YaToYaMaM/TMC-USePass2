<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentCredential;

class CustomForgotPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'The email address is not registered in the system.',
        ]);

        $otp = rand(100000, 999999);
        Session::put('otp', $otp);
        Session::put('otp_email', $request->email);
        Session::put('otp_start_time', now());
        Session::put('otp_purpose', 'forgot_password');

        $this->sendOtpEmail($request->email, $otp, 'Password Reset');

        return redirect()->route('otp.form');
    }

    public function sendStudentOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
            'student_id' => 'required|string'
        ]);

        // Verify student exists
        $student = Student::where('students_id', $request->student_id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $otp = rand(100000, 999999);
        Session::put('otp', $otp);
        Session::put('otp_email', $request->email);
        Session::put('otp_start_time', now());
        Session::put('otp_purpose', 'student_auth');
        Session::put('student_id', $request->student_id);
        Session::put('student_phone', $request->phone);
        Session::put('student_email', $request->email);

        $this->sendOtpEmail($request->email, $otp, 'Student Authentication');

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'otp_start_time' => now()
        ]);
    }

    // Add resend OTP method
    public function resendStudentOtp(Request $request)
    {
        // Validate that we have existing session data
        if (!Session::get('student_id') || !Session::get('otp_email')) {
            return response()->json(['error' => 'No active OTP session found'], 400);
        }

        $otp = rand(100000, 999999);
        Session::put('otp', $otp);
        Session::put('otp_start_time', now());

        $email = Session::get('otp_email');

        try {
            $this->sendOtpEmail($email, $otp, 'Student Authentication - Resend');

            return response()->json([
                'success' => true,
                'message' => 'OTP resent successfully',
                'otp_start_time' => now()
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to resend OTP'], 500);
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
            $mail->Subject = 'USePass OTP Code - ' . $purpose;
            $mail->Body = '
                    <div style="font-family: system-ui, sans-serif; font-size: 15px; color: #222; padding: 20px;">
                        <p>Hi there,</p>

                        <p>Here is your One-Time Password (OTP) for ' . $purpose . ':</p>

                        <p style="font-size: 24px; font-weight: bold; letter-spacing: 4px; margin: 20px 0;">' . $otp . '</p>

                        <p>This code will expire in 2 minutes. Please do not share it with anyone for your security.</p>

                        <br>

                        <p>Regards,<br>
                        USePass Support Team</p>
                    </div>';

            $mail->send();
        } catch (Exception $e) {
            throw new Exception('Could not send OTP. Mailer Error: ' . $mail->ErrorInfo);
        }
    }

    public function showOtpForm()
    {
        $purpose = Session::get('otp_purpose', 'forgot_password');

        if ($purpose === 'student_auth') {
            return inertia('Frontend/Eotp', [
                'otp_start_time' => Session::get('otp_start_time'),
                'student_id' => Session::get('student_id'),
                'otp_email' => Session::get('otp_email'),
                'student_phone' => Session::get('student_phone')
            ]);
        }

        return inertia('Auth/VerifyOtp', [
            'otp_start_time' => Session::get('otp_start_time')
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
            'student_id' => 'required_if:purpose,student_auth|string'
        ]);

        if ($request->otp == Session::get('otp')) {
            $purpose = Session::get('otp_purpose', 'forgot_password');

            if ($purpose === 'student_auth') {
                if ($request->student_id !== Session::get('student_id')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Student ID mismatch'
                    ], 422);
                }

                // Store verification in session
                Session::put('student_authenticated', true);
                Session::put('verified_student_id', $request->student_id);
                Session::put('verified_student_email', Session::get('student_email'));
                Session::put('verified_student_phone', Session::get('student_phone'));

                $student = Student::where('students_id', $request->student_id)->first();
                $parent = $student ? $student->parentInfo : null;

                // Clear OTP data but keep student data for next step
                Session::forget(['otp', 'otp_start_time', 'otp_purpose']);

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully',
                    'student_id' => $request->student_id,
                    'redirect_url' => '/Details?step=2',
                    'student_data' => $student,
                    'parent_data' => $parent
                ]);

            } else {
                // Original forgot password logic
                $user = User::where('email', Session::get('otp_email'))->first();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No user found for this email'
                    ], 422);
                }

                $token = Password::getRepository()->create($user);

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('password.reset', [
                        'token' => $token,
                        'email' => $user->email,
                    ])
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid OTP code'
        ], 422);
    }

    // Add method to save student and parent data
    public function saveStudentParentData(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|string',
                'parent_email' => 'required|email',
                'parent_phone' => 'required|string'
            ]);

            // Debug logging
            Log::info('Save data request:', $request->all());
            Log::info('Session data:', [
                'student_authenticated' => Session::get('student_authenticated'),
                'verified_student_id' => Session::get('verified_student_id'),
                'verified_student_email' => Session::get('verified_student_email'),
                'verified_student_phone' => Session::get('verified_student_phone')
            ]);

            // Verify student is authenticated
            if (!Session::get('student_authenticated') ||
                Session::get('verified_student_id') !== $request->student_id) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Find student
            $student = Student::where('students_id', $request->student_id)->first();
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Update student table with email and phone from step 1
            $student->update([
                'students_email' => Session::get('verified_student_email'),
                'students_phone_num' => Session::get('verified_student_phone')
            ]);

            Log::info('Student updated successfully');

            // Handle parent data
            $parentData = [
                'students_id' => $request->student_id,
                'parent_email' => $request->parent_email,
                'parent_phone_num' => $request->parent_phone,
            ];

            $parent = ParentCredential::where('students_id', $request->student_id)->first();

            if ($parent) {
                // Update existing parent record
                $parent->update($parentData);
                Log::info('Parent updated successfully');
            } else {
                // Create new parent record
                ParentCredential::create($parentData);
                Log::info('Parent created successfully');
            }

            // Clear session data
            Session::forget([
                'student_authenticated', 'verified_student_id',
                'verified_student_email', 'verified_student_phone',
                'student_id', 'student_phone', 'student_email', 'otp_email'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
                'student_id' => $request->student_id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Save student parent data error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to save data: ' . $e->getMessage()
            ], 500);
        }
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
