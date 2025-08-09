<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class NotFoundException extends ApiException
{
    /**
     * Exception'ın HTTP durum kodu.
     *
     * @var int
     */
    protected int $statusCode = SymfonyResponse::HTTP_NOT_FOUND;

    /**
     * Constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Kayıt Bulunamadı')
    {
        parent::__construct($message);
    }
}
