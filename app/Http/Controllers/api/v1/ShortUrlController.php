<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShortUrlController extends Controller
{
     /**
     * validToken valid if the token given is:
     * {} - true
     * {} [] () - true
     * {) - false
     * [{]} - false
     * {([])} - true
     * (((((() - false
     * Using the stack method so we can validate the order and logic between the character 
     */
    private function validToken($token) {
        $stack = [];
        $pairs = ['(' => ')', '[' => ']', '{' => '}'];
        
        for ($i = 0; $i < strlen($token); $i++) {
            $char = $token[$i];
            if (isset($pairs[$char])) {
                $stack[] = $char;
            } elseif (in_array($char, array_values($pairs))) {
                if (empty($stack) || $pairs[array_pop($stack)] !== $char) {
                    return false;
                }
            }
        }
        return empty($stack);
    }

    /**
     * Short a URL with TinyUrl API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function short(Request $request)
    {
        try{
            $auth = $request->header('Authorization');
            //preg_match('/Bearer (.+)/', $auth, $matches);
            //var_dump($matches);
            if (!$auth || !preg_match('/Bearer (.+)/', $auth, $matches) || !$this->validToken($matches[1])) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            try {
                $validated = $request->validate([
                    'url' => 'required|url'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors()
                ], 422);
            }

            $response = Http::get("https://tinyurl.com/api-create.php", [
                'url' => $validated['url']
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to shorten URL'], 500);
            }

            return response()->json(['url' => $response->body()], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
