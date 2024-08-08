<?php

namespace App\Helper;

use App\Enum\SuperMarketGlobals;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidator
{
    public function __construct(
        private ValidatorInterface $validator
    )
    {
        //
    }

    /**
     * Validate add request
     *
     * @param array $item
     * @return array
     */
    public function validAddRequest(array $item): array
    {
        $constraints = new Assert\Collection([
            'type' => [
                new Assert\NotBlank(['message' => 'Item type can not be blank']),
                new Assert\Choice([
                    'choices' => [
                        SuperMarketGlobals::ITEM_TYPE_FRUIT->value,
                        SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value,
                    ],
                    'message' => 'Invalid item type'
                ])
            ],
            'name' => [
                new Assert\NotBlank(['message' => 'Item name can not be blank']),
                new Assert\Type('string')
            ],
            'quantity' => [
                new Assert\NotBlank(['message' => 'Item quantity can not be blank']),
                new Assert\Type('numeric')
            ],
            'unit' => [
                new Assert\NotBlank(['message' => 'Item unit can not be blank']),
                new Assert\Choice([
                    'choices' => [
                        SuperMarketGlobals::WEIGHT_GRAMS->value,
                        SuperMarketGlobals::WEIGHT_KILOGRAMS->value,
                    ],
                    'message' => 'Invalid unit'
                ])
            ]
        ]);

        $violations = $this->validator->validate($item, $constraints);

        return $this->getErrorMessages($violations);
    }

    /**
     * Validate list request
     *
     * @param $type
     * @param $unit
     * @return array
     */
    public function validListRequest($type, $unit, $name): array
    {
        $constraints = new Assert\Collection([
            'type' => [
                new Assert\NotBlank(['message' => 'Item type can not be blank']),
                new Assert\Choice([
                    'choices' => [
                        SuperMarketGlobals::ITEM_TYPE_FRUIT->value,
                        SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value,
                    ],
                    'message' => 'Invalid item type'
                ])
            ],
            'unit' => [
                new Assert\Choice([
                    'choices' => [
                        SuperMarketGlobals::WEIGHT_GRAMS->value,
                        SuperMarketGlobals::WEIGHT_KILOGRAMS->value,
                    ],
                    'message' => 'Invalid unit'
                ])
            ],
            'name' => [
                new Assert\Type('string')
            ]
        ]);

        $input = ['type' => $type, 'unit' => $unit, 'name' => $name];
        $violations = $this->validator->validate($input, $constraints);

        return $this->getErrorMessages($violations);
    }

    /**
     * Validate remove request
     *
     * @param $type
     * @param $id
     * @return array
     */
    public function validRemoveRequest($type, $id): array
    {
        $constraints = new Assert\Collection([
            'type' => [new Assert\NotBlank(['message' => 'Item type can not be blank']), new Assert\Choice([
                'choices' => [
                    SuperMarketGlobals::ITEM_TYPE_FRUIT->value,
                    SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value,
                ],
                'message' => 'Invalid item type'
            ])],
            'id' => [new Assert\NotBlank(['message' => 'Item id can not be blank']), new Assert\Type('integer')]
        ]);

        $input = ['type' => $type, 'id' => $id];
        $violations = $this->validator->validate($input, $constraints);

        return $this->getErrorMessages($violations);
    }

    /**
     * Get Error Messages
     *
     * @param $violations
     * @return array
     */
    private function getErrorMessages($violations)
    {
        $errors = [];
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
        }

        return $errors;
    }

    public function validSearchRequest($searchQuery): array
    {
        $constraints = new Assert\Collection([
            'searchQuery' => [
                new Assert\NotBlank(['message' => 'Search query can not be blank']),
                new Assert\Type('string')
            ]
        ]);

        $input = ['searchQuery' => $searchQuery];
        $violations = $this->validator->validate($input, $constraints);

        return $this->getErrorMessages($violations);
    }
}