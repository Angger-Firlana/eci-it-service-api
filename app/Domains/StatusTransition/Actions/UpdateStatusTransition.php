<?php

namespace App\Domains\StatusTransition\Actions;

use App\Models\StatusTransition;

class UpdateStatusTransition
{
    public function execute(int $id, array $data): StatusTransition
    {
        $statusTransition = StatusTransition::findOrFail($id);

        if (isset($data['code'])) {
            $statusTransition->code = $data['code'];
        }

        if (isset($data['from_status_id'])) {
            $statusTransition->from_status_id = $data['from_status_id'];
        }

        if (isset($data['to_status_id'])) {
            $statusTransition->to_status_id = $data['to_status_id'];
        }

        if (array_key_exists('description', $data)) {
            $statusTransition->description = $data['description'];
        }

        $statusTransition->save();

        return $statusTransition;
    }
}
