<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A maker-checker rule stopped an Account Opening action. The message is written
 * for the user and is shown as-is.
 */
class AccountOpeningWorkflowException extends RuntimeException {}
