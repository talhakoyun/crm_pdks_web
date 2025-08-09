<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ServerException extends ApiException
{
    /**
     * Exception'ın HTTP durum kodu.
     *
     * @var int
     */
    protected int $statusCode = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * Constructor.
     *
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Sunucu Hatası', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
