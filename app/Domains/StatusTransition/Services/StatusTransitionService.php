<?php

namespace App\Domains\StatusTransition\Services;

use App\Domains\StatusTransition\Actions\CreateStatusTransition;
use App\Domains\StatusTransition\Actions\DeleteStatusTransition;
use App\Domains\StatusTransition\Actions\GetStatusTransitionById;
use App\Domains\StatusTransition\Actions\ListStatusTransitions;
use App\Domains\StatusTransition\Actions\UpdateStatusTransition;
use App\Models\StatusTransition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class StatusTransitionService
{
    public function __construct(
        protected ListStatusTransitions $listStatusTransitions,
        protected GetStatusTransitionById $getStatusTransitionById,
        protected CreateStatusTransition $createStatusTransition,
        protected UpdateStatusTransition $updateStatusTransition,
        protected DeleteStatusTransition $deleteStatusTransition
    ) {
    }

    public function list(Request $request): LengthAwarePaginator
    {
        return $this->listStatusTransitions->execute($request);
    }

    public function getById(int $id): StatusTransition
    {
        return $this->getStatusTransitionById->execute($id);
    }

    public function create(array $data): StatusTransition
    {
        return $this->createStatusTransition->execute($data);
    }

    public function update(int $id, array $data): StatusTransition
    {
        return $this->updateStatusTransition->execute($id, $data);
    }

    public function delete(int $id): void
    {
        $this->deleteStatusTransition->execute($id);
    }
}
