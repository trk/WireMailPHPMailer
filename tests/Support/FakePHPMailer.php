<?php

declare(strict_types=1);

namespace ProcessWire\Tests\Support;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * FakePHPMailer test double extending PHPMailer.
 *
 * Overrides send() to record calls and simulate successful or failed delivery
 * without opening network sockets or calling PHP's mail() function.
 */
class FakePHPMailer extends PHPMailer
{
	/**
	 * Configurable return value for send()
	 */
	public bool $mockSendResult = true;

	/**
	 * Total number of times send() was invoked
	 */
	public int $sendCallCount = 0;

	/**
	 * Optional error string to populate in ErrorInfo on send failure
	 */
	public ?string $mockFailureError = null;

	/**
	 * Optional exception to throw when send() is called
	 */
	public ?\Throwable $throwOnSend = null;

	/**
	 * Override send to record calls and simulate sending without network I/O
	 */
	public function send(): bool
	{
		$this->sendCallCount++;

		if ($this->throwOnSend !== null) {
			throw $this->throwOnSend;
		}

		if ($this->mockFailureError !== null) {
			$this->ErrorInfo = $this->mockFailureError;
		}

		return $this->mockSendResult;
	}

	/**
	 * Reset mock call state
	 */
	public function resetMock(): void
	{
		$this->sendCallCount = 0;
		$this->mockSendResult = true;
		$this->mockFailureError = null;
		$this->throwOnSend = null;
	}
}
