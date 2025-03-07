<?php

namespace App\Services\InventoryService;

use App\Helpers\ResponseError;
use App\Models\Inventory;
use App\Services\CoreService;
use App\Traits\SetTranslations;
use DB;
use Throwable;

class InventoryService extends CoreService
{
    use SetTranslations;

    protected function getModelClass(): string
    {
        return Inventory::class;
    }

    /**
     * Create a new Shop model.
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $model = DB::transaction(function () use($data) {

                /** @var Inventory $model */
                $model = $this->model()->create($data);
                $this->setTranslations($model, $data);

                return $model;
            });

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $model
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_501,
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)
            ];
        }
    }

    /**
     * Update specified Inventory model.
     * @param Inventory $model
     * @param array $data
     * @return array
     */
    public function update(Inventory $model, array $data): array
    {
        try {
            $model = DB::transaction(function () use($model, $data) {

                $model->update($data);
                $this->setTranslations($model, $data);

                return $model;
            });

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $model
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_502,
                'message' => __('errors.' . ResponseError::ERROR_502, locale: $this->language)
            ];
        }
    }

    /**
     * Delete model.
     * @param array|null $ids
     * @param int|null $shopId
     * @return array
     */
    public function delete(?array $ids = [], ?int $shopId = null): array
    {
        $models = Inventory::when($shopId, fn($q) => $q->where('shop_id', $shopId))->whereIn('id', $ids)->get();

        $errorIds = [];

        foreach ($models as $model) {
            try {
                $model->delete();
            } catch (Throwable $e) {
                $this->error($e);
                $errorIds[] = $model->id;
            }
        }

        if (count($errorIds) === 0) {
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        return [
            'status'  => false,
            'code'    => ResponseError::ERROR_505,
            'message' => __(
                'errors.' . ResponseError::CANT_DELETE_IDS,
                [
                    'ids' => implode(', ', $errorIds)
                ],
                $this->language
            )
        ];

    }

}
