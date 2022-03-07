<?php
  
namespace App\Http\Controllers;
  
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;
use \Mailjet\Resources;
use Twilio\Rest\Client;

  
class AuthController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('auth.login');
    }  
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registration()
    {
        return view('auth.register');
    }
      
    /**
     * Write code on Method
     * 
     * @return response()
     */
    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
   
        $credentials = $request->only('email', 'password');
        // if (Auth::attempt($credentials)) {
          if (Auth::attempt(['email'=>$request->email,'password'=>$request->password,'status'=>'active'])) {
            return redirect()->intended('dashboard')
                        ->withSuccess('You have Successfully logged in');
        }
  
        return redirect("login")->withSuccess('Sorry! You have entered invalid credentials');
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postRegistration(Request $request)
    {  
        $request->validate([
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $random = \Str::random(40);
        $data['otp'] = rand(100000, 999999);
        $data['remember_token'] = $random;
        // print_r ($data); die;
        $url = url('activtionlink/'.$random);
        $check = $this->create($data);
        // $this->sendMessage($url, $request->mobile);
        // $mj = new \Mailjet\Client(env('MAILJET_APIKEY'),env('MAILJET_APISECRET'),true,['version' => 'v3.1']);
        // $body = [
        //   'Messages' => [
        //     [
        //       'From' => [
        //         'Email' => env('MAIL_FROM_ADDRESS'),
        //         'Name' => env('APP_NAME')
        //       ],
        //       'To' => [
        //         [
        //           'Email' => $request->email,
        //           'Name' => $request->name
        //         ]
        //       ],
        //       'Subject' => "Welcome To Encureit Systems Pvt Ltd",
        //       'TextPart' => "Hello",
        //       'HTMLPart' => "<h3>Your Activtion Link is $url</h3><br />",
        //       'CustomID' => "AppGettingStartedTest"
        //     ]
        //   ]
        // ];
        // $response = $mj->post(Resources::$Email, ['body' => $body]);
        // $response->success() && var_dump($response->getData());
        return redirect("login")->withSuccess('Great! please login.');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function dashboard()
    {
        if(Auth::check()){
            return view('dashboard');
        }
  
        return redirect("login")->withSuccess('Opps! You do not have access');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'mobile' => $data['mobile'],
        'email' => $data['email'],
        'otp' => $data['otp'],
        'remember_token' => $data['remember_token'],
        'password' => Hash::make($data['password'])
      ]);
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function logout() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }

    public function activationlink($id){
      $user = User::where('remember_token', '=', $id)->first();
      if(empty($user)){
        echo"<h1>activation expired</h1>";
      }else{
        $updateuser = User::find($user->id);
        $updateuser->status = 'active';
        $updateuser->remember_token = '';
        $updateuser->save();
        echo"<h1>Your account is activated know</h1>";
      }
    }

    public function sendmessage($message, $recipients){
      // $account_sid = env('TWILIO_SID');
      // $auth_token = env('TWILIO_TOKEN');
      // $twilio_number = env('TWILIO_NUMBER');
      // $client = new Client($account_sid, $auth_token);
      // $client->messages->create($recipients, ['from' => $twilio_number, 'body' => $message]);
    }
}
