<?php

namespace App\Services\Quotations;

use RuntimeException;

/**
 * A custom quotation template could not be rendered safely.
 *
 * Future PDF integration should catch this and apply the existing
 * system Blade fallback. This exception is not that fallback.
 */
class QuotationTemplateRenderException extends RuntimeException
{
}
