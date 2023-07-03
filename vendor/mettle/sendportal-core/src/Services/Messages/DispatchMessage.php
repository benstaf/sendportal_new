<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Exception;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Content\MergeContent;

class DispatchMessage
{
    /** @var ResolveEmailService */
    protected $resolveEmailService;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContent */
    protected $mergeContent;

    /** @var MarkAsSent */
    protected $markAsSent;


    /** @var int */
    protected $throttleDelay = 60000 / 100; // 60000 milliseconds = 1 minute


    public function __construct(
        MergeContent $mergeContent,
        ResolveEmailService $resolveEmailService,
        RelayMessage $relayMessage,
        MarkAsSent $markAsSent
    ) {
        $this->resolveEmailService = $resolveEmailService;
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
        $this->markAsSent = $markAsSent;
    }

    /**
     * Returns the message_id from the email service
     *
     * @throws Exception
     */
    public function handle(Message $message): ?string
    {
        if (!$this->isValidMessage($message)) {
            Log::info('Message is not valid, skipping id=' . $message->id);

            return null;
        }

        $this->throttle();

        $mergedContent = $this->getMergedContent($message);

        $emailService = $this->getEmailService($message);

        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        $messageId = $this->dispatch($message, $emailService, $trackingOptions, $mergedContent);

        $this->markSent($message, $messageId);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getMergedContent(Message $message): string
    {
        return $this->mergeContent->handle($message);
    }

    /**
     * @throws Exception
     */
    protected function dispatch(Message $message, EmailService $emailService, MessageTrackingOptions $trackingOptions, string $mergedContent): ?string
    {
        $messageOptions = (new MessageOptions)
            ->setTo($message->recipient_email)
            ->setFromEmail($message->from_email)
            ->setFromName($message->from_name)
            ->setSubject($message->subject)
            ->setTrackingOptions($trackingOptions);

        $messageId = $this->relayMessage->handle($mergedContent, $messageOptions, $emailService);

        Log::info('Message has been dispatched.', ['message_id' => $messageId]);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getEmailService(Message $message): EmailService
    {
        return $this->resolveEmailService->handle($message);
    }

    protected function markSent(Message $message, string $messageId): Message
    {
        return $this->markAsSent->handle($message, $messageId);
    }


    /**
     * Add a delay between message dispatches to throttle the sending.
     *
     * @throws Exception
     */
    protected function throttle(): void
    {
    if ($this->throttleDelay > 0) {
        usleep($this->throttleDelay * 1000); // Convert milliseconds to microseconds
        }
    }   


    /**
     * Check that the message has not already been sent by getting
     * a fresh db record
     */
    protected function isValidMessage(Message $message): bool
    {
        $message->refresh();

        return !(bool)$message->sent_at;
    }
}
