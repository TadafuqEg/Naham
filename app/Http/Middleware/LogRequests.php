<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        if($request->fullUrl()!=url('/api/log_requests')){
            $response = $next($request);
            if ($response instanceof Response) {
                $content = $response->getContent();
              
               if($response->getStatusCode()!=500){
                $resultString=$content;
               }else{
                $lines = explode("\n", $content);
                $lineNumber = 4;

                if (isset($lines[$lineNumber - 1])) {
                    $resultString = $lines[$lineNumber - 1];
                    
                }
                
                
               }
                
        Log::channel('daily')->info('Incoming Request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'request'=>request()->except('_state', '_token'),
            'status_code' => $response->getStatusCode(),
            'response' => $resultString,
            'user_agent' => $request->header('User-Agent'),
            
        ]);
       

        // Store the response in the log entry
       
            
        }

        return $response;
    }
        return $next($request);
    }
}
