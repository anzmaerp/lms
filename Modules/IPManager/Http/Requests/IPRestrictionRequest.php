<?php

namespace Modules\IPManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IPRestrictionRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules($type, $id = null)
    {
        if($type == 'specific_ip'){
            return [
                'type'       => 'required|in:specific_ip,ip_range,country',
                'ip_address' => 'required|ip|unique:ipmanager_ip_restrictions,ip_start,' . $id,
            ];
        }
        if($type == 'ip_range'){
            return [
                'type'       => 'required|in:specific_ip,ip_range,country',
                'ip_range'   => 'required|regex:/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})-(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/',
            ];
        }
        if($type == 'country'){
            return [
                'type'       => 'required|in:specific_ip,ip_range,country',
                'country'    => 'required|unique:ipmanager_ip_restrictions,country,' . $id,
            ];
        }
        return [];
    }   
    
    

    public function messages()
    {
        return [
            'type.required'         => 'The restriction type is required.',
            'type.in'               => 'Invalid restriction type selected.',
            'ip_address.required_if'=> 'The IP address is required.',
            'ip_address.ip'         => 'The IP address must be a valid IP.',
            'ip_range.required_if'  => 'The IP range is required.',
            'ip_range.regex'        => 'Invalid IP range format. Example: 192.168.1.10-192.168.1.50',
            'country.required_if'   => 'The country field is required.',
        ];
    }
}
