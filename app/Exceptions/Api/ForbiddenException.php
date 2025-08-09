<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ForbiddenException extends ApiException
{
    /**
     * Exception'ın HTTP durum kodu.
     *
     * @var int
     */
    protected int $statusCode = SymfonyResponse::HTTP_FORBIDDEN;

    /**
     * Constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Bu İşlem İçin Yetkiniz Yok')
    {
        parent::__construct($message);
    }
}
