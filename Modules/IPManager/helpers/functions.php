<?php

use Illuminate\Support\Facades\Http;
use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\IPManager\Models\UserLog;
use Modules\IPManager\Models\IpRestriction;

if (!function_exists('IPManagerMenuOptions')) {
    function IPManagerMenuOptions($role)
    {
        switch ($role) {
            case 'admin':

                $routes =  [
                    'ipmanager.admin.login-history'            => __('ipmanager::ipmanager.login_history'),
                    'ipmanager.admin.ip-restriction'     => __('ipmanager::ipmanager.ip_restriction'),
                ];

                return [
                    [
                        'title'     => __('ipmanager::ipmanager.ip_management'),
                        'icon'      => 'icon-bar-chart',
                        'routes'    => $routes,
                    ]
                ];
                break;
            default:
                return [];
        }
    }
}

if (!function_exists('ipInRange')) {
    function ipInRange($ip, $start, $end)
    {
        return ip2long($ip) >= ip2long($start) && ip2long($ip) <= ip2long($end);
    }
}

if (!function_exists('getOS')) {
    function getOS($userAgent)
    {
        if (preg_match('/Windows/i', $userAgent)) return 'Windows';
        if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) return 'Mac OS';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux';
        return 'Unknown OS';
    }
}

if (!function_exists('getDevice')) {
    function getDevice($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent)) return 'Mobile';
        if (preg_match('/Tablet|iPad/i', $userAgent)) return 'Tablet';
        return 'Desktop';
    }
}

if (!function_exists('getBrowser')) {
    function getBrowser($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
        if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) return 'Opera';

        return 'Unknown Browser';
    }
}
if (!function_exists('getIpInfo')) {
    function getIpInfo($ip)
    {
        try{            
            $response   = file_get_contents("https://ipinfo.io/{$ip}/json?token=d316557cb3e17a");
            $data       = json_decode($response, true);
            $location   = explode(',', $data['loc'] ?? '0.0,0.0');
            return [
                'ip'        => $data['ip'] ?? '127.0.0.1',
                'country'   => $data['country'] ?? 'Unknown',
                'city'      => $data['city'] ?? 'Unknown',
                'latitude'  => $location[0] ?? '0.0',
                'longitude' => $location[1] ?? '0.0',
            ];
        }catch(\Exception $e){
            return [
                'ip'        => '127.0.0.1',
                'country'   => 'Unknown',
                'city'      => 'Unknown',
                'latitude'  => '0.0',
                'longitude' => '0.0',
            ];
        }
    }
}


if (!function_exists('isBlockedIP')) {
    function isBlockedIP($email)
    {
        $user       = Auth::getProvider()->retrieveByCredentials(['email' => $email]);
        if (empty($user)) {
            return false;
        }

        $ip         = request()->ip();
        $userAgent  = request()->header('User-Agent');
        $browser    = getBrowser($userAgent);
        $os         = getOS($userAgent);
        $device     = getDevice($userAgent);
        $ipInfo     = getIpInfo($ip);

        $country = Country::where('short_code', $ipInfo['country'])->value('name') ?? 'Unknown';

        $isBlockedIP = IpRestriction::where('type', IpRestriction::TYPE_SPECIFIC_IP)->where('ip_start', $ip)->exists();

        $isBlockedCountry = IpRestriction::where('type', IpRestriction::TYPE_COUNTRY)->where('country', $country)->exists();

        $isBlockedRange = IpRestriction::where('type', IpRestriction::TYPE_IP_RANGE)->get()->filter(fn($range) => ipInRange($ip, $range->ip_start, $range->ip_end))->isNotEmpty();

        $isBlocked = ($isBlockedIP || $isBlockedRange || $isBlockedCountry);

        if ($user->hasRole('admin') || !$isBlocked) {
            createUserLog([
                'user_id'     => $user->id,
                'ip'          => $ip,
                'browser'     => $browser,
                'os'          => $os,
                'device'      => $device,
                'country'     => $country,
                'city'        => $ipInfo['city'] ?? null,
                'latitude'    => $ipInfo['latitude'] ?? null,
                'longitude'   => $ipInfo['longitude'] ?? null,
            ]);

            return false;
        }

        return true;
    }
}

if (!function_exists('createUserLog')) {
    function createUserLog($userLogData)
    {
        UserLog::create([
            'user_id'       => $userLogData['user_id'],
            'ip_address'    => $userLogData['ip'],
            'session_id'    => session()->getId(),
            'browser'       => $userLogData['browser'],
            'os'            => $userLogData['os'],
            'device'        => $userLogData['device'],
            'country'       => $userLogData['country'],
            'city'          => $userLogData['city'] ?? null,
            'latitude'      => $userLogData['latitude'] ?? null,
            'longitude'     => $userLogData['longitude'] ?? null,
            'session_start' => now(),
        ]);
    }
}
