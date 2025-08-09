<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnauthorizedException extends ApiException
{
    /**
     * Exception'ın HTTP durum kodu.
     *
     * @var int
     */
    protected int $statusCode = SymfonyResponse::HTTP_UNAUTHORIZED;

    /**
     * Constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Yetkisiz Erişim')
    {
        parent::__construct($message);
    }
}
