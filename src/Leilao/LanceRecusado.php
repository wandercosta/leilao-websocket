<?php

declare(strict_types=1);

namespace App\Leilao;

/** Lance que não passou nas regras do leilão. A mensagem é exibida ao usuário. */
final class LanceRecusado extends \DomainException
{
}
