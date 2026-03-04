<?php

namespace App\Domains\ServiceRequest\Support;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;

class ShowRelationsHandler{
    private function splitIncludes(string $includeParam): array
    {
        $includes = [];
        $current = '';
        $depth = 0;
        $length = strlen($includeParam);

        for ($i = 0; $i < $length; $i++) {
            $char = $includeParam[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
                continue;
            }

            if ($char === ')') {
                $depth = max(0, $depth - 1);
                $current .= $char;
                continue;
            }

            if ($char === ',' && $depth === 0) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $includes[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $includes[] = $trimmed;
        }

        return $includes;
    }

    public function parseIncludeTokens(string $includeParam): array
    {
        $tokens = [];
        $rawIncludes = $this->splitIncludes($includeParam);

        foreach ($rawIncludes as $raw) {
            $raw = trim($raw);
            if ($raw === '') {
                continue;
            }

            $name = $raw;
            $args = [];

            $openPos = strpos($raw, '(');
            if ($openPos !== false && str_ends_with($raw, ')')) {
                $name = trim(substr($raw, 0, $openPos));
                $argsString = substr($raw, $openPos + 1, -1);
                $args = array_map('trim', $this->splitIncludes($argsString));
            }

            $tokens[] = [
                'name' => $name,
                'args' => $args
            ];
        }

        return $tokens;
    }

    public function indexWithFromIncludeString(string $includeParam): array
    {
        $relations = [];
        $availableIncludes = ServiceRequest::AVAILABLE_INCLUDES;
        $tokens = $this->parseIncludeTokens($includeParam);

        foreach ($tokens as $token) {
            $includeName = $token['name'];
            $args = $token['args'];

            if ($includeName === '') {
                continue;
            }

            $lookupKey = $includeName;
            if (!empty($args)) {
                $keyWithArgs = $includeName . '(' . implode(',', $args) . ')';
                if (isset($availableIncludes[$keyWithArgs])) {
                    $lookupKey = $keyWithArgs;
                }
            }

            if (isset($availableIncludes[$lookupKey])) {
                $relations = array_merge($relations, $availableIncludes[$lookupKey]);
            }
        }

        return $relations;
    }

     //function to get relations for index
    public function indexWith(Request $request): array
    {
        return $this->indexWithFromIncludeString($request->get('include', ''));
    }

    //function to get relations for show
    public function showWith(): array
    {
        return [
            'user:id,name,email',
            'user.departments:id,name',
            'operator:id,name,email',
            'operator.departments:id,name',
            'status:id,name,code',
            'service_request_details:id,service_request_id,device_id,device_type_id,complaint,solution',
            'service_request_details.device:id,device_model_id,serial_number,bad_asset',
            'service_request_details.device.device_model:id,device_type_id,brand,model',
            'service_request_details.device.device_model.device_type:id,name',
            'service_request_details.device_type:id,name',
            'service_request_details.complaint_images:id,service_request_detail_id,image_path',
            'vendor_approvals:id,service_request_id,approver_id,assigned_by,assigned_at,approved_at,status_id,notes',
            'vendor_approvals.status:id,name,code',
            'vendor_approvals.approver:id,name',
            'vendor_approvals.assigned_by:id,name'
        ];
    }

    //function to get default relations
    public function defaultWith(): array
    {
        return [
            'user:id,name,email',
            'status:id,name,code',
            'service_request_details.device:id,device_model_id,serial_number,bad_asset'
        ];
    }

    public function summaryWith(): array
    {
        return [
            'user:id,name',
            'operator:id,name',
            'status:id,name,code',
        ];
    }
}
