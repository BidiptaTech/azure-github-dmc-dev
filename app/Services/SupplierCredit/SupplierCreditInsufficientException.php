<?php

namespace App\Services\SupplierCredit;

use RuntimeException;

/**
 * Thrown before calling a vendor when TravClicks' credit with that vendor cannot cover the booking.
 */
class SupplierCreditInsufficientException extends RuntimeException {}
