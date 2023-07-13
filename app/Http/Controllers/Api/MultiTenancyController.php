<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tenant;
use App\Models\User;
use Exception;

class MultiTenancyController extends Controller
{   
    public function __construct(){
        $this->api_key = "db07f1a7-b45c-4f5c-a8c4-cfb3f6d0b7e5";
        $this->upload_url = "https://api.digisigner.com/v1/documents";
        $this->signature_url = "https://api.digisigner.com/v1/signature_requests";
    }

    public function register_company(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'company_name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required',
                        'subdomain' => 'required|string|regex:/^\S*$/u|max:100|unique:tenants,id'
                    ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        $domain         = strtolower($request->subdomain);
        $company_name   = $request->company_name;
        $email          = $request->email;
        $password       = $request->password;
        $host           = env("HOST", "localhost");
        $port           = env("PORT", "8000");
       
        $db_prefix      = config('tenancy.database.prefix');
        $db_suffix      = config('tenancy.database.suffix');
        $unique         = time();
        $db_name        = $db_prefix.$domain.$unique.$db_suffix;
        $tenant         = Tenant::create(['id'=>$domain,'tenancy_db_name'=>$db_name,'company_name'=>$company_name]);
        $tenant->domains()->create(['domain' => $domain.'.'.$host]);
        $tenant = Tenant::with('domains')->where(['id'=>$domain])->first();
     //   dd($tenant);
        $user = User::create([
            'name' => $company_name,
            'email' => $email,
            'password' => bcrypt($password),
            'role' => 'company',
            'domain_id' => $tenant->domains[0]->id
        ]);

        tenancy()->initialize($tenant);

        // Create the same user in tenant DB
        $user1 = User::create([
            'name' => $company_name,
            'email' => $email,
            'password' => bcrypt($password),
            'role' => 'admin'
        ]);


        
        return response()->json($tenant, 201);
    }
    
    public function send_sign(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        "document_id" => "required",
                        "emails"    => "required|array",
                        "emails.*"  => "required|email|distinct"
                    ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);
        $document_id = $request->document_id;
        $signers     = [];
       
        foreach ($request->emails as $key => $email) {
            $signers[]  = [
                          "email" => $email, 
                          "fields" => [
                             [
                                "page" => 0, 
                                "rectangle" => [
                                   0, 
                                   0, 
                                   200, 
                                   100 
                                ], 
                                "type" => "SIGNATURE" 
                             ] 
                          ] 
                       ];
        }
        

        $post_data = [
           "documents" => [
                 [
                    "document_id" => $document_id, 
                    "subject" => "Signature Request", 
                    "message" => "Please sign following documents", 
                    "signers" => $signers
                 ] 
              ] 
        ]; 

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->signature_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

        curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . '');
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return response()->json(['message' => curl_error($ch)], 404);
        }
        curl_close($ch);     
        $result = json_decode($result, true);
        
        return response()->json($result, 200);
    }
}