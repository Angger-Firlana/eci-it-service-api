<?php

namespace App\Http\Controllers;

use App\Domains\SetEmailIt\Actions\CreateSetEmailIt;
use App\Domains\SetEmailIt\Actions\DeleteSetEmailIt;
use App\Domains\SetEmailIt\Actions\GetSetEmailItList;
use App\Domains\SetEmailIt\Actions\UpdateSetEmailIt;
use App\Helpers\APIResponse;
use App\Http\Requests\SetEmailIt\StoreSetEmailItRequest;
use App\Http\Requests\SetEmailIt\UpdateSetEmailItRequest;

class SetEmailItController extends Controller
{
    public function __construct(
        protected GetSetEmailItList $getSetEmailItList,
        protected CreateSetEmailIt $createSetEmailIt,
        protected UpdateSetEmailIt $updateSetEmailIt,
        protected DeleteSetEmailIt $deleteSetEmailIt,
    ) {
    }

    public function index()
    {
        $list = $this->getSetEmailItList->execute();

        return APIResponse::success($list);
    }

    public function store(StoreSetEmailItRequest $request)
    {
        $record = $this->createSetEmailIt->execute($request->validated());

        return APIResponse::success($record, 201, 'Penerima email berhasil ditambahkan');
    }

    public function update(UpdateSetEmailItRequest $request, int $id)
    {
        $record = $this->updateSetEmailIt->execute($id, $request->validated());

        return APIResponse::success($record, 200, 'Status penerima email diperbarui');
    }

    public function destroy(int $id)
    {
        $this->deleteSetEmailIt->execute($id);

        return APIResponse::success(null, 200, 'Penerima email berhasil dihapus');
    }
}
