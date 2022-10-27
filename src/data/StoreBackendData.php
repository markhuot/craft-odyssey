<?php

namespace markhuot\odyssey\data;

use Symfony\Component\Validator\Constraints as Assert;

class StoreBackendData
{
    public ?int $id;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    public string $handle;

    #[Assert\NotBlank]
    public string $type;

    public array $settings;
}
