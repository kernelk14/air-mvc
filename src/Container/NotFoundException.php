<?php

declare(strict_types=1);

namespace Air\Container;

use RuntimeException;

class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
