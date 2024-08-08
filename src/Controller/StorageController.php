<?php

namespace App\Controller;

use App\Enum\SuperMarketGlobals;
use App\Exception\InvalidItemException;
use App\Exception\InvalidItemTypeException;
use App\Exception\NoItemException;
use App\Helper\RequestValidator;
use App\Response\ApiResponse;
use App\Service\StorageService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class StorageController extends AbstractController
{
    public function __construct(
        private StorageService   $storageService,
        private RequestValidator $requestValidator
    )
    {
        //
    }

    /**
     * Add items
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidItemTypeException
     * @throws InvalidItemException
     * @throws NoItemException
     */
    public function add(Request $request): JsonResponse
    {
        $item = json_decode($request->getContent(), true);

        // Validate the request data
        $errors = $this->requestValidator->validAddRequest($item);
        if (!empty($errors)) {
            return ApiResponse::responseError(implode(', ', $errors));
        }

        try {
            // save item
            $this->storageService->add($item);

            return ApiResponse::responseOk('Item added');
        } catch (Exception $e) {
            return ApiResponse::responseError('Failed to add item. ' . $e->getMessage());
        }
    }

    /**
     * Remove item
     *
     * @param string $type
     * @param int $id
     * @return JsonResponse
     */
    public function remove(string $type, int $id): JsonResponse
    {
        // Validate the request data
        $errors = $this->requestValidator->validRemoveRequest($type, $id);
        if (!empty($errors)) {
            return ApiResponse::responseError(implode(', ', $errors));
        }

        try {
            // delete item
            $this->storageService->remove($id, $type);

            return ApiResponse::responseOk('Item have been successfully removed');
        } catch (Exception $e) {
            return ApiResponse::responseError('Failed to remove item. ' . $e->getMessage());
        }
    }

    /**
     * Get a list of items
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidItemTypeException
     */
    public function list(Request $request): JsonResponse
    {
        $type = $request->query->get('type');
        $unit = $request->query->get('unit', SuperMarketGlobals::WEIGHT_GRAMS->value);
        $name = $request->query->get('name', '');

        // Validate the request data
        $errors = $this->requestValidator->validListRequest($type, $unit, $name);
        if (!empty($errors)) {
            return ApiResponse::responseError(implode(', ', $errors));
        }

        try {
            // fetch items
            $items = $this->storageService->list($type, $unit, $name);

            return ApiResponse::responseCreated($items);
        } catch (Exception $e) {
            return ApiResponse::responseError('Failed to show item. ' . $e->getMessage());
        }
    }

    public function search(Request $request): JsonResponse
    {
        $searchQuery = $request->query->get('q', '');

        // Validate the request data
        $errors = $this->requestValidator->validSearchRequest($searchQuery);
        if (!empty($errors)) {
            return ApiResponse::responseError(implode(', ', $errors));
        }

        try {
            $results = $this->storageService->searchByName($searchQuery);

            return ApiResponse::responseCreated($results);
        } catch (Exception $e) {
            return ApiResponse::responseError('Failed to search items. ' . $e->getMessage());
        }
    }

}