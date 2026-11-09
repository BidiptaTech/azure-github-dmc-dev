<?php

namespace App\Services\Invoices;

use RuntimeException;

/**
 * A custom invoice template could not be rendered safely.
 * PDF integration catches this and falls back to the system Blade view.
 */
class InvoiceTemplateRenderException extends RuntimeException
{
}
