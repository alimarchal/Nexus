<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A maker-checker rule stopped a File Management action. The message is written for the user and is shown as-is.
 */
class FileManagementWorkflowException extends RuntimeException {}
